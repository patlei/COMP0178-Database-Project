<?php

//session_start(); // Start the session to access user data

// Check if the user is logged in and if they are an admin
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['accountType']) && $_SESSION['accountType'] === 'admin') {
        header("Location: admin_page.php");
        exit;
    }
}

// Include the header and database connection files
include_once("header.php");
include_once("connection.php");
require_once("utilities.php");

// Fetch "Popular Listings" based on the highest number of views.
$popular_sql = "SELECT a.auction_id, a.item_name, a.image_path, 
                       COALESCE(h.highest_bid, a.starting_price) AS display_price, 
                       a.views 
                FROM auction a
                LEFT JOIN highest_bids h ON a.auction_id = h.auction_id
                WHERE a.auction_status = 'active'
                ORDER BY a.views DESC 
                LIMIT 5";
$popular_result = $conn->query($popular_sql);

// Fetch "Ending Soon Auctions" from auction table
$ending_soon_sql = "SELECT a.auction_id, a.item_name, a.image_path, a.end_date, 
                           COALESCE(h.highest_bid, a.starting_price) AS display_price 
                    FROM auction a
                    LEFT JOIN highest_bids h ON a.auction_id = h.auction_id
                    WHERE a.auction_status = 'active'
                    ORDER BY a.end_date ASC 
                    LIMIT 5";
$ending_soon_result = $conn->query($ending_soon_sql);
?>

<!-- Hero Section -->
<div class="container mt-5">
    <div class="jumbotron text-center" style="background-color: #c69097; color: #ffffff;">
        <h1>Welcome to Knitty Gritty!</h1>
        <p>Your one-stop platform to buy and sell amazing knitwear & crochet items</p>
        <a href="browse.php" class="btn btn-light btn-lg">Start Browsing</a>
    </div>
</div>

<!-- Categories Section -->
<div class="container mt-5">
    <h3>Shop by Categories</h3>
    <div class="row mt-4">
        <?php
        $cat_sql = "SELECT category_id, category_name, category_section FROM categories LIMIT 6";
        $cat_result = $conn->query($cat_sql);
        if ($cat_result && $cat_result->num_rows > 0) {
            while ($cat_row = $cat_result->fetch_assoc()) {
                echo '<div class="col-md-4 mb-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($cat_row['category_name']) . '</h5>
                                <p class="card-text">Explore our range of ' . htmlspecialchars($cat_row['category_name']) . ' in ' . htmlspecialchars($cat_row['category_section']) . '.</p>
                                <a href="browse.php?cat=' . htmlspecialchars($cat_row['category_id']) . '" class="btn btn-primary">View Category</a>
                            </div>
                        </div>
                      </div>';
            }
        }
        ?>
    </div>
</div>

<!-- Popular Listings Section -->
<div class="container mt-5 popular-listings">
    <h3>Popular Listings</h3>
    <div class="scrolling-wrapper row flex-row flex-nowrap mt-4 pb-4 pt-2">
        <?php
        if ($popular_result && $popular_result->num_rows > 0) {
            while ($row = $popular_result->fetch_assoc()) {
                $image_path = $row['image_path'];
                // Construct full image path
                $image_path = isset($row['image_path']) ? htmlspecialchars($row['image_path']) : null;
            
                // Check if image path is valid, else use placeholder image
                $image_src = (!empty($image_path) && file_exists($image_path)) ? $image_path : './images/default-placeholder.png';
            
                echo '<div class="col-3">
                        <div class="card h-100">
                            <img src="' . $image_src . '" class="card-img-top" alt="' . htmlspecialchars($row['item_name']) . '">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($row['item_name']) . '</h5>
                                <p class="card-text"><strong>Current Price: £' . number_format($row['display_price'], 2) . '</strong></p>
                                <p class="text-muted">Views: ' . number_format($row['views']) . '</p>
                            </div>
                            <div class="card-footer text-center">
                                <a href="listing.php?auction_id=' . $row['auction_id'] . '" class="btn btn-primary">View Listing</a>
                            </div>
                        </div>
                    </div>';
            }
        } else {
            echo '<p class="col-12 text-center text-muted">No popular listings available at this time.</p>';
        }
        ?>
    </div>
</div>

<!-- Ending Soon Auctions Section -->
<div class="container mt-5">
    <h3>Ending Soon - Grab Your Favourite Items Before They Are Gone!</h3>
    <div class="scrolling-wrapper row flex-row flex-nowrap mt-4 pb-4 pt-2">
        <?php
        if ($ending_soon_result && $ending_soon_result->num_rows > 0) {
            while ($row = $ending_soon_result->fetch_assoc()) {
                $end_date = new DateTime($row['end_date']);
                $image_path = $row['image_path'];
                $image_path = isset($row['image_path']) ? htmlspecialchars($row['image_path']) : null;
            
                // Check if image path is valid, else use placeholder image
                $image_src = (!empty($image_path) && file_exists($image_path)) ? $image_path : './images/default-placeholder.png';
            
                echo '<div class="col-3">
                        <div class="card h-100">
                            <img src="' . $image_src . '" class="card-img-top" alt="' . htmlspecialchars($row['item_name']) . '">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($row['item_name']) . '</h5>
                                <p class="card-text"><strong>Current Price: £' . number_format($row['display_price'], 2) . '</strong></p>
                                <p class="text-muted">Ends on: ' . $end_date->format('d M Y H:i') . '</p>
                            </div>
                            <div class="card-footer text-center">
                                <a href="listing.php?auction_id=' . $row['auction_id'] . '" class="btn btn-warning">Bid Now</a>
                            </div>
                        </div>
                    </div>';
            }
        } else {
            echo '<p class="col-12 text-center text-muted">No auctions are ending soon at this time.</p>';
        }
        ?>
    </div>
</div>

<!-- Include Footer -->
<?php include_once("footer.php"); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
