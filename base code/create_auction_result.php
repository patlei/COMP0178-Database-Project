<?php include_once("header.php");
include 'connection.php'; 
############### ADDING IMAGES OPTION not great yet#####################
session_start();
// Set PHP's default timezone to UTC
date_default_timezone_set('UTC');
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
    $auctionSize = isset($_POST['auctionSize']) ? $_POST['auctionSize'] : '';
    $auctionMaterial = isset($_POST['auctionMaterial']) ? $_POST['auctionMaterial'] : '';
    $auctionColor = isset($_POST['auctionColor']) ? $_POST['auctionColor'] : '';
    $auctionCondition = isset($_POST['auctionCondition']) ? $_POST['auctionCondition'] : '';
    $auctionImage = isset($_POST['auctionImage']) ? $_POST['auctionImage'] : '';
#image_path	
    // Get today's date for start_date
    $auctionStartDate = date('Y-m-d H:i:s');  // Current date and time
    $auctionEndDate = date('Y-m-d H:i:s', strtotime($auctionEndDate));  // Convert to datetime format

    // Determine auction status
    if ($auctionStartDate > $auctionEndDate) {
        // Auction has ended
        $auctionStatus = 'closed';
    } else {
        // Auction is ongoing
        $auctionStatus = 'active';
    }

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
        // Query to retrieve size_id based on size
        $sizeQuery = "SELECT size_id FROM sizes WHERE size = ?";
        $stmtSize = $conn->prepare($sizeQuery);
        $stmtSize->bind_param("s", $auctionSize);
        $stmtSize->execute();
        $stmtSize->bind_result($size_id);
        $stmtSize->fetch();
        $stmtSize->close();

    
        // Query to retrieve material_id based on material
        $materialQuery = "SELECT material_id FROM materials WHERE material = ?";
        $stmtMaterial = $conn->prepare($materialQuery);
        $stmtMaterial->bind_param("s", $auctionMaterial);
        $stmtMaterial->execute();
        $stmtMaterial->bind_result($material_id);
        $stmtMaterial->fetch();
        $stmtMaterial->close();


        // Query to retrieve color_id based on color
        $colorQuery = "SELECT color_id FROM colors WHERE color = ?";
        $stmtColor = $conn->prepare($colorQuery);
        $stmtColor->bind_param("s", $auctionColor);
        $stmtColor->execute();
        $stmtColor->bind_result($color_id);
        $stmtColor->fetch();
        $stmtColor->close();

        if (!$auctionCondition) {
            echo "Error: Condition not found.";
            exit;
        }

        // Prepare SQL query to insert auction
        $query = "INSERT INTO auction (username, item_name, item_description, category_id, starting_price, reserve_price, start_date, end_date, auction_status, image_path, material_id, item_condition, color_id, size_id)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);  // Use $query here
        if ($stmt) {
            // Bind parameters
            $stmt->bind_param("ssssddssssssss", $username, $auctionTitle, $auctionDetails, $category_id, $auctionStartPrice, $auctionReservePrice, $auctionStartDate, $auctionEndDate, $auctionStatus, $auctionImage, $material_id, $auctionCondition, $color_id, $size_id);

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