<?php
// Include header, connection, and utility files for database and HTML functions.
include_once("header_admin.php");
include_once("connection.php");
require("utilities.php");

// Retrieve search parameters, setting defaults if not provided
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$category = isset($_GET['cat']) ? $_GET['cat'] : "all";
$ordering = isset($_GET['order_by']) ? $_GET['order_by'] : "pricelow";
$curr_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$conditions = [];

// Validate inputs to prevent abuse
if (strlen($keyword) > 25) {
    die("Search keyword is too long. Please try again with fewer characters.");
}

// Construct SQL query with conditions and dynamic values
$sql = "SELECT a.auction_id, a.item_name, a.item_description, IFNULL(hb.highest_bid, a.starting_price) AS current_price, a.end_date, a.image_path, 
               a.`condition`, a.material, a.color, a.size, a.views 
        FROM auction a
        LEFT JOIN highest_bids hb ON a.auction_id = hb.auction_id
        WHERE a.auction_status = 'active'";

// Add keyword filter to query if provided
if (!empty($keyword)) {
    // Use LIKE to make the search case-insensitive
    $escaped_keyword = $conn->real_escape_string($keyword);
    $conditions[] = "(item_name LIKE '%$escaped_keyword%' OR item_description LIKE '%$escaped_keyword%')";
}

// Add category filter if a category is specified
if ($category != "all") {
    $escaped_category = $conn->real_escape_string($category);
    $conditions[] = "category_id = '$escaped_category'";
}

// Add condition filter if selected
if (isset($_GET['condition']) && is_array($_GET['condition'])) {
    $conditions_values = array_map(function ($value) use ($conn) {
        return "'" . $conn->real_escape_string($value) . "'";
    }, $_GET['condition']);
    $conditions[] = "`condition` IN (" . implode(",", $conditions_values) . ")";
}

// Add material filter if selected
if (isset($_GET['material']) && is_array($_GET['material'])) {
    $material_values = array_map(function ($value) use ($conn) {
        return "'" . $conn->real_escape_string($value) . "'";
    }, $_GET['material']);
    $conditions[] = "material IN (" . implode(",", $material_values) . ")";
}

// Add color filter if selected
if (isset($_GET['color']) && is_array($_GET['color'])) {
    $color_values = array_map(function ($value) use ($conn) {
        return "'" . $conn->real_escape_string($value) . "'";
    }, $_GET['color']);
    $conditions[] = "color IN (" . implode(",", $color_values) . ")";
}

// Add size filter if selected
if (isset($_GET['size']) && is_array($_GET['size'])) {
    $size_values = array_map(function ($value) use ($conn) {
        return "'" . $conn->real_escape_string($value) . "'";
    }, $_GET['size']);
    $conditions[] = "size IN (" . implode(",", $size_values) . ")";
}

// Combine conditions into the base query
if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// Add sorting condition to the query
switch ($ordering) {
    case "pricelow":
        $sql .= " GROUP BY a.auction_id ORDER BY current_price ASC";
        break;
    case "pricehigh":
        $sql .= " GROUP BY a.auction_id ORDER BY current_price DESC";
        break;
    case "date":
        $sql .= " GROUP BY a.auction_id ORDER BY a.end_date ASC";
        break;
    case "views":
        $sql .= " GROUP BY a.auction_id ORDER BY a.views DESC";
        break;
}

// Pagination setup
$results_per_page = 9;
$offset = ($curr_page - 1) * $results_per_page;
$sql .= " LIMIT $results_per_page OFFSET $offset";

// Execute the SQL query directly
$result = $conn->query($sql);

// Check if the query execution resulted in an error
if (!$result) {
    die("Error executing query: " . $conn->error);
}

// Count the total number of results for pagination purposes
$count_sql = "SELECT COUNT(*) as total FROM auction WHERE auction_status = 'active'";
if (!empty($conditions)) {
    $count_sql .= " AND " . implode(" AND ", $conditions);
}
$count_result = $conn->query($count_sql);
if ($count_result) {
    $num_results = ($count_result->num_rows > 0) ? $count_result->fetch_assoc()['total'] : 0;
} else {
    $num_results = 0;
}
$max_page = ceil($num_results / $results_per_page);

?>

<style>
    .card-fixed {
        height: 100%;  /* Makes all cards the same height */
    }
    .card-img-top {
        object-fit: cover;
        width: 100%;
        height: 200px;  /* Fix the image height for consistency */
    }
    .scrolling-wrapper {
        overflow-x: auto;
        white-space: nowrap;
    }
    .scrolling-wrapper .card {
        display: inline-block;
        margin-right: 1rem;
        width: 18rem;
    }
</style>

<div class="container">
    <h2 class="my-3">Browse Listings</h2>

    <!-- Search Form (separate from the floating sidebar) -->
    <div id="searchSpecs" class="p-4 rounded shadow-sm mb-5" style="background-color: #f8f9fa; border: 1px solid #e3e6f0;">
        <form method="get" action="browse.php">
            <div class="row">
                <div class="col-md-5 pr-0">
                    <div class="form-group">
                        <label for="keyword" class="sr-only">Search keyword:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent pr-0 text-muted">
                                    <i class="fa fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control border-left-0" id="keyword" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Search...">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 pr-0">
                    <div class="form-group">
                        <label for="cat" class="sr-only">Search within:</label>
                        <select class="form-control rounded" id="cat" name="cat" style="border-color: #ced4da;">
                            <option value="all" <?php echo ($category == 'all') ? 'selected' : ''; ?>>All categories</option>
                            <?php
                            $cat_sql = "SELECT category_id, category_name FROM categories";
                            $cat_result = $conn->query($cat_sql);
                            if ($cat_result->num_rows > 0) {
                                while ($row = $cat_result->fetch_assoc()) {
                                    echo "<option value=\"" . htmlspecialchars($row['category_id']) . "\"";
                                    if ($category == $row['category_id']) {
                                        echo " selected";
                                    }
                                    echo ">" . htmlspecialchars($row['category_name']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 pr-0">
                    <div class="form-inline">
                        <label class="mx-2" for="order_by">Sort by:</label>
                        <select class="form-control rounded" id="order_by" name="order_by" style="border-color: #ced4da;">
                            <option value="pricelow" <?php echo ($ordering == 'pricelow') ? 'selected' : ''; ?>>Price (low to high)</option>
                            <option value="pricehigh" <?php echo ($ordering == 'pricehigh') ? 'selected' : ''; ?>>Price (high to low)</option>
                            <option value="date" <?php echo ($ordering == 'date') ? 'selected' : ''; ?>>Soonest expiry</option>
                            <option value="views" <?php echo ($ordering == 'views') ? 'selected' : ''; ?>>Most viewed</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-1 px-0">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>

            <!-- Collapsible Filter Section -->
            <div class="mt-4">
                <button class="btn btn-secondary" type="button" data-toggle="collapse" data-target="#filterOptions" aria-expanded="false" aria-controls="filterOptions">
                    Filter Options
                </button>
                <button type="button" class="btn btn-light ml-2" onclick="window.location.href='browse.php'">
                    Clear Filters
                </button>
            </div>
            <div class="collapse mt-3" id="filterOptions">
                <div class="card card-body">
                    <!-- Condition Filter -->
                    <div class="form-group">
                        <label>Condition:</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="condition[]" value="new" id="conditionNew" <?php echo (isset($_GET['condition']) && in_array('new', $_GET['condition'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="conditionNew">New</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="condition[]" value="used" id="conditionUsed" <?php echo (isset($_GET['condition']) && in_array('used', $_GET['condition'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="conditionUsed">Used</label>
                        </div>
                    </div>

                    <!-- Material Filter -->
                    <div class="form-group">
                        <label>Material:</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="material[]" value="cotton" id="materialCotton" <?php echo (isset($_GET['material']) && in_array('cotton', $_GET['material'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="materialCotton">Cotton</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="material[]" value="wool" id="materialWool" <?php echo (isset($_GET['material']) && in_array('wool', $_GET['material'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="materialWool">Wool</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="material[]" value="polyester" id="materialPolyester" <?php echo (isset($_GET['material']) && in_array('polyester', $_GET['material'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="materialPolyester">Polyester</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="material[]" value="acrylic" id="materialAcrylic" <?php echo (isset($_GET['material']) && in_array('acrylic', $_GET['material'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="materialAcrylic">Acrylic</label>
                        </div>
                        <!-- Add more material options here -->
                    </div>

                    <!-- Colour Filter -->
                    <div class="form-group">
                        <label>Colour:</label><br>
                        <!-- White Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="white" id="colorWhite" <?php echo (isset($_GET['color']) && in_array('white', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorWhite">White</label>
                        </div>
                        
                        <!-- Black Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="black" id="colorBlack" <?php echo (isset($_GET['color']) && in_array('black', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorBlack">Black</label>
                        </div>

                        <!-- Grey Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="grey" id="colorGrey" <?php echo (isset($_GET['color']) && in_array('grey', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorGrey">Grey</label>
                        </div>
                        
                        <!-- Brown Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="brown" id="colorBrown" <?php echo (isset($_GET['color']) && in_array('brown', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorBrown">Brown</label>
                        </div>
                        
                        <!-- Red Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="red" id="colorRed" <?php echo (isset($_GET['color']) && in_array('red', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorRed">Red</label>
                        </div>
                        
                        <!-- Green Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="green" id="colorGreen" <?php echo (isset($_GET['color']) && in_array('green', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorGreen">Green</label>
                        </div>
                        
                        <!-- Blue Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="blue" id="colorBlue" <?php echo (isset($_GET['color']) && in_array('blue', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorBlue">Blue</label>
                        </div>
                        
                        <!-- Yellow Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="yellow" id="colorYellow" <?php echo (isset($_GET['color']) && in_array('yellow', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorYellow">Yellow</label>
                        </div>

                        <!-- Orange Colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="orange" id="colorOrange" <?php echo (isset($_GET['color']) && in_array('orange', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorOrange">Orange</label>
                        </div>

                        <!-- Multi-colour -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="color[]" value="multi-colour" id="colorMultiColour" <?php echo (isset($_GET['color']) && in_array('multi-colour', $_GET['color'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="colorMultiColour">Multi-colour</label>
                        </div>
                        <!-- Add more color options here -->
                    </div>

                    <!-- Size Filter -->
                    <div class="form-group">
                        <label>Size:</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="size[]" value="S" id="sizeXS" <?php echo (isset($_GET['size']) && in_array('S', $_GET['size'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sizeXS">XS</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="size[]" value="S" id="sizeS" <?php echo (isset($_GET['size']) && in_array('S', $_GET['size'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sizeS">S</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="size[]" value="M" id="sizeM" <?php echo (isset($_GET['size']) && in_array('M', $_GET['size'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sizeM">M</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="size[]" value="L" id="sizeL" <?php echo (isset($_GET['size']) && in_array('L', $_GET['size'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sizeL">L</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="size[]" value="XL" id="sizeXL" <?php echo (isset($_GET['size']) && in_array('XL', $_GET['size'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sizeXL">XL</label>
                        </div>
                        <!-- Add more size options here -->
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container mt-5">
    <?php if ($result->num_rows == 0): ?>
        <p>No results found... Try again with different keywords or filters.</p>
    <?php else: ?>
        <div class="row">
            <?php
            // Loop through each result and display it using the utility function
            while ($row = $result->fetch_assoc()) {
                $item_id = $row['auction_id'];
                $title = $row['item_name'];
                $description = $row['item_description'];
                $current_price = $row['current_price']; // Highest bid price
                $end_date = new DateTime($row['end_date']);
                $image_path = $row['image_path'];

                // Display item in a grid layout with an image
                echo "<div class='col-md-4 mb-4'>
                        <div class='card h-100'>
                            <img src='$image_path' class='card-img-top' alt='Item Image' style='object-fit: cover; height: 200px;'>
                            <div class='card-body'>
                                <h5 class='card-title'><a href='listing.php?item_id=$item_id'>$title</a></h5>
                                <p class='card-text'>" . htmlspecialchars($description) . "</p>
                                <p class='text-muted'>Current Bid Price: £" . number_format($current_price, 2) . "</p>
                                <p class='text-muted'>End Date: " . $end_date->format('d M Y H:i') . "</p>
                            </div>
                        </div>
                    </div>";
            }
            ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <nav aria-label="Search results pages" class="mt-5">
        <ul class="pagination justify-content-center">
            <?php
            // Build query string for pagination links
            $querystring = "";
            foreach ($_GET as $key => $value) {
                if ($key != "page") {
                    $querystring .= "$key=" . urlencode($value) . "&amp;";
                }
            }

            // Previous page link
            if ($curr_page != 1) {
                echo '<li class="page-item">
                        <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
                            <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>';
            }

            // Page number links
            for ($i = 1; $i <= $max_page; $i++) {
                $active_class = ($i == $curr_page) ? 'active' : '';
                echo '<li class="page-item ' . $active_class . '">
                        <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
                    </li>';
            }

            // Next page link
            if ($curr_page != $max_page) {
                echo '<li class="page-item">
                        <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
                            <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </li>';
            }
            ?>
        </ul>
    </nav>
</div>

<!-- Recommendation Section Based on User's Most Viewed Items -->
<div class="container mt-5">
    <h3>Recommended for You</h3>
    <div class="scrolling-wrapper row flex-row flex-nowrap mt-4 pb-4 pt-2">
        <?php
        // Retrieve user's most viewed items
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

        if ($username) {
            // Fetch the user's most viewed items with the highest bid information
            $user_rec_sql = "SELECT a.auction_id, a.item_name, a.image_path, 
                            COALESCE(MAX(b.bid_amount), a.starting_price) AS highest_bid 
                            FROM auction a
                            LEFT JOIN bids b ON a.auction_id = b.auction_id
                            JOIN user_views uv ON a.auction_id = uv.auction_id
                            WHERE a.auction_status = 'active' AND uv.username = ?
                            GROUP BY a.auction_id
                            ORDER BY uv.view_count DESC
                            LIMIT 10";

            $user_rec_stmt = $conn->prepare($user_rec_sql);
            $user_rec_stmt->bind_param('s', $username);
            $user_rec_stmt->execute();
            $user_rec_result = $user_rec_stmt->get_result();

            if ($user_rec_result && $user_rec_result->num_rows > 0) {
                while ($rec_row = $user_rec_result->fetch_assoc()) {
                    echo '<div class="col-3">
                            <div class="card h-100">
                                <img src="' . htmlspecialchars($rec_row['image_path']) . '" class="card-img-top" alt="' . htmlspecialchars($rec_row['item_name']) . '" style="object-fit: cover; height: 150px;">
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($rec_row['item_name']) . '</h5>
                                    <p class="card-text"><strong>Highest Bid: £' . number_format($rec_row['highest_bid'], 2) . '</strong></p>
                                    <a href="listing.php?item_id=' . $rec_row['auction_id'] . '" class="btn btn-primary">View Listing</a>
                                </div>
                            </div>
                        </div>';
                }
            } else {
                // Updated HTML to align the message with other card containers
                echo '<div class="col-12 d-flex align-items-center justify-content-center" style="height: 150px;">
                        <p class="text-muted">No personalised recommendations available at this time.</p>
                      </div>';
            }

            $user_rec_stmt->close();
        } else {
            // When the user is not logged in
            echo '<div class="col-12 d-flex align-items-center justify-content-center" style="height: 150px;">
                    <p class="text-muted">Please log in to see personalised recommendations.</p>
                  </div>';
        }
        ?>
    </div>
</div>

<!-- Popular Listings Section Based on Overall Views -->
<div class="container mt-5">
    <h3>Popular Listings</h3>
    <div class="scrolling-wrapper row flex-row flex-nowrap mt-4 pb-4 pt-2">
        <?php
        // Retrieve most viewed items overall with the highest bid information
        $popular_sql = "SELECT a.auction_id, a.item_name, a.image_path, 
                        COALESCE(MAX(b.bid_amount), a.starting_price) AS highest_bid 
                        FROM auction a
                        LEFT JOIN bids b ON a.auction_id = b.auction_id
                        WHERE a.auction_status = 'active' 
                        GROUP BY a.auction_id
                        ORDER BY a.views DESC 
                        LIMIT 10";

        $popular_result = $conn->query($popular_sql);

        if ($popular_result && $popular_result->num_rows > 0) {
            while ($pop_row = $popular_result->fetch_assoc()) {
                echo '<div class="col-3">
                        <div class="card h-100">
                            <img src="' . htmlspecialchars($pop_row['image_path']) . '" class="card-img-top" alt="' . htmlspecialchars($pop_row['item_name']) . '" style="object-fit: cover; height: 150px;">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($pop_row['item_name']) . '</h5>
                                <p class="card-text"><strong>Highest Bid: £' . number_format($pop_row['highest_bid'], 2) . '</strong></p>
                                <a href="listing.php?item_id=' . $pop_row['auction_id'] . '" class="btn btn-primary">View Listing</a>
                            </div>
                        </div>
                    </div>';
            }
        } else {
            echo '<p>No popular listings available at this time.</p>';
        }
        ?>
    </div>
</div>

<?php include_once("footer.php"); ?>
