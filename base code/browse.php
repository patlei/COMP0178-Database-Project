<?php
// Include header, connection, and utility files for database and HTML functions.
include_once("header.php");
include_once("connection.php");
require("utilities.php");

// Retrieve search parameters, setting defaults if not provided
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$category = isset($_GET['cat']) ? $_GET['cat'] : "all";
$ordering = isset($_GET['order_by']) ? $_GET['order_by'] : "pricelow";
$curr_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Validate inputs to prevent abuse
if (strlen($keyword) > 25) {
    die("Search keyword is too long. Please try again with fewer characters.");
}

// Construct SQL query with conditions and dynamic values
$sql = "SELECT auction_id, item_name, item_description, starting_price, end_date FROM auction WHERE auction_status = 'active'";

// Prepare dynamic conditions
$conditions = [];

// Add keyword filter to query if provided
if (!empty($keyword)) {
    // Use LOWER() to make the search case-insensitive
    $escaped_keyword = $conn->real_escape_string($keyword);
    $conditions[] = "(LOWER(item_name) LIKE LOWER('%$escaped_keyword%') OR LOWER(item_description) LIKE LOWER('%$escaped_keyword%'))";
}

// Add category filter if a category is specified
if ($category != "all") {
    $escaped_category = $conn->real_escape_string($category);
    $conditions[] = "category_id = '$escaped_category'";
}

// Combine conditions into the base query
if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// Add sorting condition to the query
switch ($ordering) {
    case "pricelow":
        $sql .= " ORDER BY starting_price ASC";
        break;
    case "pricehigh":
        $sql .= " ORDER BY starting_price DESC";
        break;
    case "date":
        $sql .= " ORDER BY end_date ASC";
        break;
}

// Pagination setup
$results_per_page = 10;
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

<div class="container">
    <h2 class="my-3">Browse listings</h2>

    <!-- Search Form -->
    <div id="searchSpecs">
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
                        <select class="form-control" id="cat" name="cat">
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
                        <select class="form-control" id="order_by" name="order_by">
                            <option value="pricelow" <?php echo ($ordering == 'pricelow') ? 'selected' : ''; ?>>Price (low to high)</option>
                            <option value="pricehigh" <?php echo ($ordering == 'pricehigh') ? 'selected' : ''; ?>>Price (high to low)</option>
                            <option value="date" <?php echo ($ordering == 'date') ? 'selected' : ''; ?>>Soonest expiry</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-1 px-0">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container mt-5">
    <?php if ($result->num_rows == 0): ?>
        <p>No results found... Try again with different keywords or filters.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php
            // Loop through each result and display it using the utility function
            while ($row = $result->fetch_assoc()) {
                $item_id = $row['auction_id'];
                $title = $row['item_name'];
                $description = $row['item_description'];
                $current_price = $row['starting_price'];
                $end_date = new DateTime($row['end_date']);

                // Use the utility function to print each listing
                print_listing_li($item_id, $title, $description, $current_price, null, $end_date);
            }
            ?>
        </ul>
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

<?php include_once("footer.php"); ?>
