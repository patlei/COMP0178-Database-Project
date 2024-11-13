<?php
include_once("header.php"); // Assume header.php includes session_start()
require("utilities.php");
require("connection.php"); // Database connection

echo "<div class='container'>";
echo "<h2 class='my-3'>My listings</h2>";

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "<p>Please log in to view your listings.</p>";
} else {
    // Get the username of the logged-in user
    $username = $_SESSION['username'];

    // Query to retrieve auctions created by the user, including auction_status
    $sql = "SELECT auction_id, item_name, item_description, starting_price, end_date, auction_status FROM auction WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if there are any auctions
    if ($result->num_rows > 0) {
        // Loop through results and display them
        while ($row = $result->fetch_assoc()) {
            $auction_id = $row['auction_id'];
            $item_name = htmlspecialchars($row['item_name']);
            $description = htmlspecialchars($row['item_description']);
            $starting_price = $row['starting_price'];
            $end_date = $row['end_date'];
            $auction_status = htmlspecialchars($row['auction_status']); // Retrieve auction status

            echo "<div class='listing-item'>";
            echo "<h5><a href='auction.php?id=$auction_id'>$item_name</a></h5>";
            echo "<p>Item Description: $description</p>";
            echo "<p>Starting Price: £$starting_price</p>";
            echo "<p>End Date: $end_date</p>";
            echo "<p>Auction Status: $auction_status</p>"; // Display auction status

            // Add a link to edit the listing
            echo "<p><a href='edit_mylisting.php?auction_id=$auction_id'>Edit Listing</a></p>";

            echo "</div><hr>";
        }
    } else {
        echo "<p>You have no active listings.</p>";
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
}
echo "</div>";

include_once("footer.php");
