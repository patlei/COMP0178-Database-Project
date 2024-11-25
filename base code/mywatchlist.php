<?php
include_once("header.php");  
include_once("connection.php"); 
require_once("utilities.php");  


// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p>You must be logged in to view your bids. <a href='login.php'>Login</a></p>";
    exit;
}

// Get the logged-in username from the session
$username = $_SESSION['username'];

// Query to get the auctions the user added to watchlist
$query = "SELECT a.auction_id, a.item_name, a.item_description, a.image_path,
            IFNULL(hb.highest_bid, a.starting_price) AS current_price, 
           COUNT(b.bid_id) AS num_bids, a.end_date
    FROM watchlist w
    LEFT JOIN auction a ON w.auction_id = a.auction_id
    LEFT JOIN highest_bids hb ON a.auction_id = hb.auction_id
    LEFT JOIN bids b ON w.auction_id = b.auction_id
    WHERE w.username = ?
    GROUP BY a.auction_id, a.item_name, a.item_description, a.image_path, a.end_date
    ORDER BY a.end_date ASC";  

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);  // Bind the username to the query
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="container mt-5">
    <h2 class="mb-4">Your Watchlist</h2> 
    <?php
    // Check if there are any results
    if ($result->num_rows > 0) {
        echo '<ul class="list-group">';  // Start a list for the auction items

        // Loop through the results and use the print_listing_li function to display them
        while ($row = $result->fetch_assoc()) {
            $item_id = $row['auction_id'];
            $title = $row['item_name'];
            $desc = $row['item_description'];
            $image_path = $row['image_path'];
            $price = $row['current_price'];
            $num_bids = $row['num_bids'];
            $end_time = new DateTime($row['end_date']);  // Convert end_time to DateTime object

            // Use the print_listing_li function to display each auction
            print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time, $image_path);
        }

        echo '</ul>';  // End the list
    } else {
        echo "<p>You have not added any auctions to watchlist yet.</p>";
    }

    $stmt->close();  
    $conn->close();  
    ?>
</div>

<?php include_once("footer.php"); ?>
