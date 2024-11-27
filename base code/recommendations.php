<?php include_once("header.php") // Header includes session_start()?>
<?php require_once("utilities.php")?>
<?php require("connection.php");  // Database connection ?>

<div class="container">

<h2 class="my-3">Recommendations for you</h2>

<?php
  // Recommendations page shows items in the same category as items the user has previously bid on
  $curr_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
?>

<?php
// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p>Please log in to view recommendation.</p>";
} else {
    // Get the username of the logged-in user
    $username = $_SESSION['username'];
}

// Construct SQL query with conditions and dynamic values
$sql = "SELECT DISTINCT a.auction_id, a.item_name, a.item_description, 
               IFNULL(hb.highest_bid, a.starting_price) AS current_price, 
               a.category_id, a.end_date, a.image_path,
               a.item_condition, m.material, c.color, s.size, a.views 
        FROM auction a
        LEFT JOIN highest_bids hb ON a.auction_id = hb.auction_id
        LEFT JOIN materials m ON a.material_id = m.material_id
        LEFT JOIN colors c ON a.color_id = c.color_id
        LEFT JOIN sizes s ON a.size_id = s.size_id
        WHERE a.auction_status = 'active'
        AND a.category_id IN (
          SELECT DISTINCT auc.category_id
          FROM bids b
          JOIN auction auc ON b.auction_id = auc.auction_id
          WHERE b.username = '$username'
        )
        AND a.auction_id NOT IN (
          SELECT b.auction_id
          FROM bids b
          WHERE b.username = '$username'
        )
        LIMIT 18";
      

        
// Execute the SQL query
$result = $conn->query($sql);

// Check if the query execution resulted in an error
if (!$result) {
    echo "<div class='alert alert-danger'>Oops! Something went wrong while fetching the results. Please try again later.</div>";
}
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

<div class="container mt-5"></div>
<!-- Main Search Results -->
<?php if ($result->num_rows == 0): ?>
    <p>No recommendations found for now. Try again later.</p>
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
            $image_path = isset($row['image_path']) ? htmlspecialchars($row['image_path']) : null;
            
            // Check if image path is valid, else use placeholder image
            $image_src = (!empty($image_path) && file_exists($image_path)) ? $image_path : './images/default-placeholder.png';
            
            // Display item in a grid layout with an image
            echo "<div class='col-md-4 mb-4'>
                    <div class='card h-100'>
                        <img src='" . $image_src . "' class='card-img-top' alt='" . $title . "' style='object-fit: cover; height: 200px;'>
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


</div>
<?php include_once("footer.php"); ?>
