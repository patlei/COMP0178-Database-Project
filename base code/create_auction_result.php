<?php include_once("header.php");
include 'connection.php'; 

session_start();
?>

<div class="container my-5">

<?php

// Retrieve username from session
$username = $_SESSION['username'];

// Handle auction creation form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract data from the form
    $auctionTitle = isset($_POST['auctionTitle']) ? $_POST['auctionTitle'] : '';
    $auctionDetails = isset($_POST['auctionDetails']) ? $_POST['auctionDetails'] : '';
    $auctionCategory = isset($_POST['auctionCategory']) ? $_POST['auctionCategory'] : '';
    $auctionStartPrice = isset($_POST['auctionStartPrice']) ? $_POST['auctionStartPrice'] : '';
    $auctionReservePrice = isset($_POST['auctionReservePrice']) ? $_POST['auctionReservePrice'] : ''; // Optional 
    $auctionEndDate = isset($_POST['auctionEndDate']) ? $_POST['auctionEndDate'] : '';

    // Get today's date for start_date
    $auctionStartDate = date('Y-m-d');  // Current date in 'YYYY-MM-DD' format

    // Determine auction status
    $auctionStatus = (strtotime($auctionEndDate) > time()) ? 'active' : 'closed';

    // Validate the form fields
    if (empty($auctionTitle) || empty($auctionDetails) || empty($auctionCategory) || empty($auctionStartPrice) || empty($auctionEndDate)) {
        $error_message = "Please fill out all required fields.";
    } else {
        // Query to retrieve category_id based on category_name
        $categoryQuery = "SELECT category_id FROM categories WHERE category_name = ?";
        $stmtCategory = $conn->prepare($categoryQuery);
        $stmtCategory->bind_param("s", $auctionCategory);
        $stmtCategory->execute();
        $stmtCategory->bind_result($category_id);
        $stmtCategory->fetch();
        $stmtCategory->close();

        if (!$category_id) {
            echo "Error: Category not found.";
            exit;
        }

        // Prepare SQL query to insert auction
        $query = "INSERT INTO auction (username, item_name, item_description, category_id, starting_price, reserve_price, start_date, end_date, auction_status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);  // Use $query here
        if ($stmt) {
            // Bind parameters
            $stmt->bind_param("ssssddsss", $username, $auctionTitle, $auctionDetails, $category_id, $auctionStartPrice, $auctionReservePrice, $auctionStartDate, $auctionEndDate, $auctionStatus);

            // Execute the statement
            if ($stmt->execute()) {
                echo "Auction successfully created!";
            } else {
                echo "Error inserting data: " . $stmt->error;
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}

$conn->close();
?>

</div>

<?php include_once("footer.php")?>

<?php
if (!$stmt->execute()) {
    echo "Error executing query: " . $stmt->error;
}


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>