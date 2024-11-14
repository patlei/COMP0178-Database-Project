<?php
include_once("connection.php");
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(array("status" => "error", "message" => "User not logged in."));
    exit;
}

$username = $_SESSION['username']; // Get the logged-in username

if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
    echo json_encode(array("status" => "error", "message" => "Missing parameters."));
    exit;
}

$item_id = $_POST['arguments'];

$res = array("status" => "error", "message" => "Something went wrong.");

if ($_POST['functionname'] == "add_to_watchlist") {
    // Check if the item is already in the watchlist
    $query = "SELECT COUNT(*) FROM watchlist WHERE username = ? AND auction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $username, $item_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // If the item is not already in the watchlist, add it
    if ($count == 0) {
        $query = "INSERT INTO watchlist (username, auction_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $username, $item_id);
        
        if ($stmt->execute()) {
            $res = array("status" => "success", "message" => "Item added to watchlist.");
        } else {
            $res = array("status" => "error", "message" => "Failed to add item to watchlist.");
        }
        $stmt->close();
    } else {
        $res = array("status" => "info", "message" => "Item is already in your watchlist.");
    }
} 
else if ($_POST['functionname'] == "remove_from_watchlist") {
    // Remove the item from the watchlist
    $query = "DELETE FROM watchlist WHERE username = ? AND auction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $username, $item_id);

    if ($stmt->execute()) {
        $res = array("status" => "success", "message" => "Item removed from watchlist.");
    } else {
        $res = array("status" => "error", "message" => "Failed to remove item from watchlist.");
    }
    $stmt->close();
}

echo json_encode($res);
?>
