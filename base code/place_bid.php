<?php
// include_once("header.php");
include_once("connection.php");

session_start();

// Retrieve username from session
$username = $_SESSION['username'];

// Get POST data - auction_id and bid_amount
$auction_id = isset($_POST['auction_id']) ? $_POST['auction_id'] : '';
$bid_amount = isset($_POST['bid']) ? $_POST['bid'] : '';

// Get current date and time
$bid_time = date('Y-m-d H:i:s'); 

// Handle bid form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate the bid amount is a valid number
    if (empty($bid_amount)) {
        $error_message = "Please enter a bid amount.";
    } elseif (!is_numeric($bid_amount) || $bid_amount <= 0) {
        $error_message = "Bid amount must be a positive number.";
    }


    if (!isset($error_message)) {
        // Prepare SQL query to insert the bid
        $query = "INSERT INTO bids (auction_id, username, bid_amount, bid_time)
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);  // Prepare the query
        
        if ($stmt) {
            // Bind parameters (auction_id, username, bid_amount, bid_time)
            $stmt->bind_param("dsds", $auction_id, $username, $bid_amount, $bid_time);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Your bid has been placed successfully!";
            } else {
                $error_message = "Error inserting bid: " . $stmt->error;
            }
            // Close the prepared statement 
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
        
    }
}

if (isset($error_message)) {
    echo $error_message; // Display any error message
}

// Close the database connection
$conn->close();

// Notify user of success/failure and redirect.
if (!isset($error_message)) {
    header("Location: listing.php?auction_id=$auction_id");
    exit();  // Ensure the script stops here
} else {
    // Handle any errors (optional)
    $_SESSION['error_message'] = $error_message;
    header("Location: listing.php?auction_id=$auction_id");
    exit();
}
?>