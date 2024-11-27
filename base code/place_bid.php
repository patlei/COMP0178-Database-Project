<?php
include_once("header.php");
include_once("connection.php");
// Set PHP's default timezone to UTC
date_default_timezone_set('UTC');

session_start();

// Retrieve username from session
if (!isset($_SESSION['username'])) {
    // Redirect to login if not logged in
    header('Location: login.php');
    exit();
}
$username = $_SESSION['username'];

// Check if the user is blocked
$blockedQuery = "SELECT blocked FROM users WHERE username = ?";
$blockedStmt = $conn->prepare($blockedQuery);
$blockedStmt->bind_param("s", $username);
$blockedStmt->execute();
$blockedStmt->bind_result($blocked);
$blockedStmt->fetch();
$blockedStmt->close();

if ($blocked) {
    // If the user is blocked, show a message and exit
    echo "<div class='container my-5'>
            <div class='alert alert-danger' role='alert'>
                You are blocked by an admin, you cannot bid on auctions.
            </div>
          </div>";
    include_once("footer.php");
    exit();
}

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
            $stmt->bind_param("isds", $auction_id, $username, $bid_amount, $bid_time);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Your bid has been placed successfully!";
                
                // Close the prepared statement
                $stmt->close();

                // Adding Notification for the Bidder
                $notification_message = "You have placed a bid of £" . number_format($bid_amount, 2) . " on auction ID: " . $auction_id;
                $notification_type = 'bidding';

                $notification_query = "INSERT INTO notifications (username, auction_id, message, type, is_read) VALUES (?, ?, ?, ?, FALSE)";
                $notification_stmt = $conn->prepare($notification_query);
                
                if ($notification_stmt) {
                    $notification_stmt->bind_param("siss", $username, $auction_id, $notification_message, $notification_type);
                    $notification_stmt->execute();
                    $notification_stmt->close();
                }

                // Notify other users who have bid on this auction that they have been outbid
                $outbid_message = "You have been outbid on auction ID: " . $auction_id;
                $outbid_type = 'bidding';

                $outbid_query = "INSERT INTO notifications (username, auction_id, message, type, is_read) 
                                 SELECT DISTINCT username, ?, ?, ?, FALSE FROM bids 
                                 WHERE auction_id = ? AND username != ?";
                $outbid_stmt = $conn->prepare($outbid_query);

                if ($outbid_stmt) {
                    $outbid_stmt->bind_param("issis", $auction_id, $outbid_message, $outbid_type, $auction_id, $username);
                    $outbid_stmt->execute();
                    $outbid_stmt->close();
                }

                // Notify users who are watching this auction that a new bid has been placed
                $watchlist_query = "SELECT username FROM watchlist WHERE auction_id = ? AND username != ?";
                $watchlist_stmt = $conn->prepare($watchlist_query);
                $watchlist_stmt->bind_param("is", $auction_id, $username); // Get all users watching this auction, except the current bidder
                $watchlist_stmt->execute();
                $watchlist_result = $watchlist_stmt->get_result();

                while ($watchlist_row = $watchlist_result->fetch_assoc()) {
                    $watcher_username = $watchlist_row['username'];
                    $watchlist_message = "A new bid of £" . number_format($bid_amount, 2) . " has been placed on an auction you are watching (ID: " . $auction_id . ")";
                    $watchlist_type = 'watchlist';

                    $watchlist_notification_query = "INSERT INTO notifications (username, auction_id, message, type, is_read) VALUES (?, ?, ?, ?, FALSE)";
                    $watchlist_notification_stmt = $conn->prepare($watchlist_notification_query);
                    $watchlist_notification_stmt->bind_param("siss", $watcher_username, $auction_id, $watchlist_message, $watchlist_type);
                    $watchlist_notification_stmt->execute();
                    $watchlist_notification_stmt->close();
                }

                $watchlist_stmt->close();

                // Notify the auction owner when a new bid is placed
                $owner_query = "SELECT username FROM auction WHERE auction_id = ?";
                $owner_stmt = $conn->prepare($owner_query);
                if ($owner_stmt) {
                    $owner_stmt->bind_param("i", $auction_id);
                    $owner_stmt->execute();
                    $owner_stmt->bind_result($owner_username);
                    if ($owner_stmt->fetch() && $owner_username != $username) {
                        $owner_message = "A new bid of £" . number_format($bid_amount, 2) . " has been placed on your auction (ID: " . $auction_id . ")";
                        $owner_type = 'auction';

                        $owner_stmt->close(); // Close before issuing another query

                        $owner_notification_query = "INSERT INTO notifications (username, auction_id, message, type, is_read) VALUES (?, ?, ?, ?, FALSE)";
                        $owner_notification_stmt = $conn->prepare($owner_notification_query);
                        if ($owner_notification_stmt) {
                            $owner_notification_stmt->bind_param("siss", $owner_username, $auction_id, $owner_message, $owner_type);
                            $owner_notification_stmt->execute();
                            $owner_notification_stmt->close();
                        }
                    } else {
                        // Close the statement if no rows are fetched
                        $owner_stmt->close();
                    }
                }
            } else {
                $error_message = "Error inserting bid: " . $stmt->error;
                $stmt->close(); // Close the statement here as well
            }
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
