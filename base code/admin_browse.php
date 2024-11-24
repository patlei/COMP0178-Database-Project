<?php
// Include header, connection, and utility files for database and HTML functions.
include_once("header_admin.php");
include_once("connection.php");
include_once("config.php");
require_once("utilities.php");

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
$sql = "SELECT a.auction_id, a.item_name, a.item_description, 
               IFNULL(hb.highest_bid, a.starting_price) AS current_price, 
               a.end_date, a.image_path,
               a.item_condition, m.material, c.color, s.size, a.views 
        FROM auction a
        LEFT JOIN highest_bids hb ON a.auction_id = hb.auction_id
        LEFT JOIN materials m ON a.material_id = m.material_id
        LEFT JOIN colors c ON a.color_id = c.color_id
        LEFT JOIN sizes s ON a.size_id = s.size_id
        WHERE a.auction_status = 'active'";
        
// Add keyword filter to query if provided
if (!empty($keyword)) {
    // Ensure the keyword is trimmed of leading and trailing spaces
    $keyword = trim($keyword);

    // Use LIKE to make the search case-insensitive
    $escaped_keyword = $conn->real_escape_string($keyword);
    $conditions[] = "(a.item_name LIKE CONCAT('%', '$escaped_keyword', '%') OR a.item_description LIKE CONCAT('%', '$escaped_keyword', '%'))";
}


// Add category filter if a category is specified
if ($category != "all") {
    $escaped_category = $conn->real_escape_string($category);
    $conditions[] = "a.category_id = '$escaped_category'";
}

// Add condition filter if selected
if (isset($_GET['condition']) && is_array($_GET['condition'])) {
    $conditions_values = array_map(function ($value) use ($conn) {
        return "'" . $conn->real_escape_string($value) . "'";
    }, $_GET['condition']);
    $conditions[] = "a.item_condition IN (" . implode(",", $conditions_values) . ")";
}

// Add material filter if selected
if (isset($_GET['material']) && is_array($_GET['material'])) {
    $material_values = array_map(function ($value) use ($conn) {
        return "'" . $conn->real_escape_string($value) . "'";
    }, $_GET['material']);
    $conditions[] = "m.material IN (" . implode(",", $material_values) . ")";
}

// Add color filter if selected
if (isset($_GET['color']) && is_array($_GET['color'])) {
    $color_values = array_map(function ($value) use ($conn) {
        return "'" . $conn->real_escape_string($value) . "'";
    }, $_GET['color']);
    $conditions[] = "c.color IN (" . implode(",", $color_values) . ")";
}

// Add size filter if selected
if (isset($_GET['size']) && is_array($_GET['size'])) {
    $size_values = array_map(function ($value) use ($conn) {
        return "'" . $conn->real_escape_string($value) . "'";
    }, $_GET['size']);
    $conditions[] = "s.size IN (" . implode(",", $size_values) . ")";
}

// Combine conditions into the base query
if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// Add sorting condition to the query
switch ($ordering) {
    case "pricelow":
        $sql .= " ORDER BY current_price ASC";
        break;
    case "pricehigh":
        $sql .= " ORDER BY current_price DESC";
        break;
    case "date":
        $sql .= " ORDER BY a.end_date ASC";
        break;
    case "views":
        $sql .= " ORDER BY a.views DESC";
        break;
    default:
        $sql .= " ORDER BY a.auction_id DESC"; // Default sorting in case none provided
}

// Pagination setup
$results_per_page = 9;
$offset = ($curr_page - 1) * $results_per_page;
$sql .= " LIMIT $results_per_page OFFSET $offset";

// Execute the SQL query
$result = $conn->query($sql);

// Check if the query execution resulted in an error
if (!$result) {
    echo "<div class='alert alert-danger'>Oops! Something went wrong while fetching the results. Please try again later.</div>";
}

// Count the total number of results for pagination purposes
$count_sql = "SELECT COUNT(*) as total 
              FROM auction a 
              LEFT JOIN materials m ON a.material_id = m.material_id
              LEFT JOIN colors c ON a.color_id = c.color_id
              LEFT JOIN sizes s ON a.size_id = s.size_id 
              WHERE a.auction_status = 'active'";

// Apply the filters to count query if they exist
if (!empty($conditions)) {
    $count_sql .= " AND " . implode(" AND ", $conditions);
}

// Execute the count query
$count_result = $conn->query($count_sql);
if ($count_result && $count_result->num_rows > 0) {
    $num_results = $count_result->fetch_assoc()['total'];
} else {
    $num_results = 0; // Default to 0 if there's an error
}
$max_page = ($results_per_page > 0) ? ceil($num_results / $results_per_page) : 1; // Ensure no division by zero
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
                <button type="button" class="btn btn-light ml-2" onclick="window.location.href='admin_browse.php'">
                    Clear Filters
                </button>
            </div>
            <div class="collapse mt-3" id="filterOptions">
                <div class="card card-body" style="text-align: left;"> 
                 <!-- Condition Filter -->
                    <div class="form-group">
                        <label>Condition:</label><br>
                        <?php
                        // Fetch unique conditions dynamically from the auction table
                        $condition_sql = "SELECT DISTINCT item_condition FROM auction";
                        $condition_result = $conn->query($condition_sql);
                        
                        if ($condition_result->num_rows > 0) {
                            while ($row = $condition_result->fetch_assoc()) {
                                $condition_value = $row['item_condition'];
                                echo '<div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="condition[]" value="' . $condition_value . '" id="condition' . ucfirst($condition_value) . '"' . (isset($_GET['condition']) && in_array($condition_value, $_GET['condition']) ? 'checked' : '') . '>
                                        <label class="form-check-label" for="condition' . ucfirst($condition_value) . '">' . ucfirst($condition_value) . '</label>
                                    </div>';
                            }
                        }
                        ?>
                    </div>
                    
                    <!-- Material Filter -->
                    <div class="form-group">
                        <label>Material:</label><br>
                        <?php
                        // Fetch materials in the correct order as per SQL instructions
                        $material_sql = "SELECT material FROM materials ORDER BY 
                                            CASE
                                                WHEN material = 'Cotton' THEN 1
                                                WHEN material = 'Wool' THEN 2
                                                WHEN material = 'Polyester' THEN 3
                                                WHEN material = 'Acrylic' THEN 4
                                                WHEN material = 'Other' THEN 5
                                                ELSE 6
                                            END";
                        $material_result = $conn->query($material_sql);
                        
                        if ($material_result->num_rows > 0) {
                            while ($row = $material_result->fetch_assoc()) {
                                $material_value = $row['material'];
                                echo '<div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="material[]" value="' . $material_value . '" id="material' . ucfirst($material_value) . '"' . (isset($_GET['material']) && in_array($material_value, $_GET['material']) ? 'checked' : '') . '>
                                        <label class="form-check-label" for="material' . ucfirst($material_value) . '">' . ucfirst($material_value) . '</label>
                                    </div>';
                            }
                        }
                        ?>
                    </div>
                                
                    <!-- Colour Filter -->
                    <div class="form-group">
                        <label>Colour:</label><br>
                        <?php
                        // Fetch colours in the correct order as per SQL instructions
                        $color_sql = "SELECT color FROM colors ORDER BY 
                                        CASE
                                            WHEN color = 'White' THEN 1
                                            WHEN color = 'Black' THEN 2
                                            WHEN color = 'Grey' THEN 3
                                            WHEN color = 'Brown' THEN 4
                                            WHEN color = 'Red' THEN 5
                                            WHEN color = 'Green' THEN 6
                                            WHEN color = 'Blue' THEN 7
                                            WHEN color = 'Yellow' THEN 8
                                            WHEN color = 'Orange' THEN 9
                                            WHEN color = 'Purple' THEN 10
                                            WHEN color = 'Multicolor' THEN 11
                                            ELSE 12
                                        END";
                        $color_result = $conn->query($color_sql);
                        
                        if ($color_result->num_rows > 0) {
                            while ($row = $color_result->fetch_assoc()) {
                                $color_value = $row['color'];
                                echo '<div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="color[]" value="' . $color_value . '" id="color' . ucfirst($color_value) . '"' . (isset($_GET['color']) && in_array($color_value, $_GET['color']) ? 'checked' : '') . '>
                                        <label class="form-check-label" for="color' . ucfirst($color_value) . '">' . ucfirst($color_value) . '</label>
                                    </div>';
                            }
                        }
                        ?>
                    </div>

                    <!-- Size Filter -->
                    <div class="form-group">
                        <label>Size:</label><br>
                        <?php
                        // Fetch sizes in the correct order as per SQL instructions
                        $size_sql = "SELECT size FROM sizes ORDER BY 
                                        CASE
                                            WHEN size = 'XS' THEN 1
                                            WHEN size = 'S' THEN 2
                                            WHEN size = 'M' THEN 3
                                            WHEN size = 'L' THEN 4
                                            WHEN size = 'XL' THEN 5
                                            WHEN size = 'One-size' THEN 6
                                            ELSE 7
                                        END";
                        $size_result = $conn->query($size_sql);
                        
                        if ($size_result->num_rows > 0) {
                            while ($row = $size_result->fetch_assoc()) {
                                $size_value = $row['size'];
                                echo '<div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="size[]" value="' . $size_value . '" id="size' . ucfirst($size_value) . '"' . (isset($_GET['size']) && in_array($size_value, $_GET['size']) ? 'checked' : '') . '>
                                        <label class="form-check-label" for="size' . ucfirst($size_value) . '">' . ucfirst($size_value) . '</label>
                                    </div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container mt-5">
    <!-- Main Search Results -->
    <?php if ($result->num_rows == 0): ?>
        <p>No results found... Try again with different keywords or filters.</p>
    <?php else: ?>
        <div class="row">
            <?php
            // Loop through each result and display it using the utility function
            while ($row = $result->fetch_assoc()) {
                $auction_id = $row['auction_id'];
                $title = $row['item_name'];
                $description = $row['item_description'];
                $current_price = $row['current_price']; // Highest bid price
                $end_date = new DateTime($row['end_date']);
                $image_path = $row['image_path'];

                // Construct full image path
                $full_image_path = IMAGE_BASE_PATH . $image_path;

                // Display item in a grid layout with an image
                echo "<div class='col-md-4 mb-4'>
                        <div class='card h-100'>
                            <img src='" . $full_image_path . "' class='card-img-top' alt='Item Image' style='object-fit: cover; height: 200px;'>
                            <div class='card-body'>
                                <h5 class='card-title'><a href='listing.php?auction_id=$auction_id'>$title</a></h5>
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

    <!-- Pagination Setup -->
    <nav aria-label="Search results pages" class="mt-5">
        <ul class="pagination justify-content-center">
            <?php
            // Build query string for pagination links
            $querystring = "";
            foreach ($_GET as $key => $value) {
                if ($key != "page") {
                    // Include all active filter parameters in the query string
                    if (is_array($value)) {
                        foreach ($value as $subvalue) {
                            $querystring .= htmlspecialchars($key) . "[]=" . urlencode($subvalue) . "&";
                        }
                    } else {
                        $querystring .= htmlspecialchars($key) . "=" . urlencode($value) . "&";
                    }
                }
            }

            // Previous page link
            if ($curr_page > 1) {
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
            if ($curr_page < $max_page) {
                echo '<li class="page-item">
                        <a class="page-link" href="admin_browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
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
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

        if ($username) {
            // Fetch recommended items for the logged-in user
            // These recommendations should be independent of the filters applied in the search
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
                    // Construct the full image path
                    $full_image_path = IMAGE_BASE_PATH . $rec_row['image_path'];

                    echo '<div class="col-3">
                            <div class="card h-100">
                                <img src="' . htmlspecialchars($full_image_path) . '" class="card-img-top" alt="' . htmlspecialchars($rec_row['item_name']) . '" style="object-fit: cover; height: 150px;">
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($rec_row['item_name']) . '</h5>
                                    <p class="card-text"><strong>Highest Bid: £' . number_format($rec_row['highest_bid'], 2) . '</strong></p>
                                    <a href="listing.php?auction_id=' . $rec_row['auction_id'] . '" class="btn btn-primary">View Listing</a>
                                </div>
                            </div>
                        </div>';
                }
            } else {
                echo '<div class="col-12 d-flex align-items-center justify-content-center" style="height: 150px;">
                        <p class="text-muted">No personalised recommendations available at this time.</p>
                      </div>';
            }

            $user_rec_stmt->close();
        } else {
            echo '<div class="col-12 d-flex align-items-center justify-content-center flex-column" style="height: 150px;">
                    <p class="text-muted">Please log in to see personalised recommendations.</p>
                    <a href="login.php" class="btn btn-primary mt-2">Log In</a>
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
                // Construct the full image path
                $full_image_path = IMAGE_BASE_PATH . $pop_row['image_path'];

                echo '<div class="col-3">
                        <div class="card h-100">
                            <img src="' . htmlspecialchars($full_image_path) . '" class="card-img-top" alt="' . htmlspecialchars($pop_row['item_name']) . '" style="object-fit: cover; height: 150px;">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($pop_row['item_name']) . '</h5>
                                <p class="card-text"><strong>Highest Bid: £' . number_format($pop_row['highest_bid'], 2) . '</strong></p>
                                <a href="listing.php?auction_id=' . $pop_row['auction_id'] . '" class="btn btn-primary">View Listing</a>
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

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Debug Dropdown Functionality -->
<script>
    $(document).ready(function () {
        console.log("Dropdowns are initialized.");
        $('.dropdown-toggle').dropdown(); // Ensure dropdowns are activated
    });
</script>

</body>
</html>

<?php
$conn->close(); // Close database connection
?>


