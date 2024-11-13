<?php
include 'connection.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $auction_id = $_POST['auction_id'];
    $item_name = isset($_POST['auctionTitle']) ? $_POST['auctionTitle'] : '';
    $item_description = isset($_POST['auctionDetails']) ? $_POST['auctionDetails'] : '';
    $category_id = isset($_POST['auctionCategory']) ? $_POST['auctionCategory'] : '';
    $starting_price = isset($_POST['auctionStartPrice']) ? $_POST['auctionStartPrice'] : '';
    $reserve_price = isset($_POST['auctionReservePrice']) ? $_POST['auctionReservePrice'] : null;
    $end_date = isset($_POST['auctionEndDate']) ? $_POST['auctionEndDate'] : '';

    // Validate required fields
    if (empty($item_name) || empty($item_description) || empty($category_id) || empty($starting_price) || empty($end_date)) {
        echo "Please fill out all required fields.";
        exit;
    }

    // Retrieve existing start_date from the database
    $query = "SELECT start_date FROM auction WHERE auction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $stmt->bind_result($start_date);
    if (!$stmt->fetch()) {
        echo "Auction not found.";
        exit;
    }
    $stmt->close();

    // Determine auction status based on end_date
    $current_date = date('Y-m-d H:i:s'); // Current date and time
    $auction_status = (strtotime($end_date) > strtotime($current_date)) ? 'active' : 'closed';

    // Update query to modify the auction details and status
    $update_query = "UPDATE auction SET item_name = ?, item_description = ?, category_id = ?, starting_price = ?, reserve_price = ?, end_date = ?, auction_status = ? WHERE auction_id = ?";
    $stmt_update = $conn->prepare($update_query);

    if ($stmt_update) {
        // Bind parameters
        $stmt_update->bind_param("ssiddssi", $item_name, $item_description, $category_id, $starting_price, $reserve_price, $end_date, $auction_status, $auction_id);

        // Execute the statement
        if ($stmt_update->execute()) {
            // Redirect to My Listings page after success
            header("Location: mylistings.php");
            exit;
        } else {
            echo "Error updating data: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
