<?php
session_start();
require_once("connection.php");

// Check if the user is an admin
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'admin') {
    die("Access denied.");
}

// Check if the required parameters are provided
if (!isset($_GET['username']) || !isset($_GET['action'])) {
    die("Invalid request.");
}

$username = $_GET['username'];
$action = $_GET['action']; // Expected values: 'block' or 'unblock'

// Validate the action
if ($action !== 'block' && $action !== 'unblock') {
    die("Invalid action.");
}

// Update the blocked status in the database
$blocked = ($action === 'block') ? 1 : 0;
$sql = "UPDATE users SET blocked = ? WHERE username = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("is", $blocked, $username);
    if ($stmt->execute()) {
        echo $action === 'block' ? "User $username has been blocked." : "User $username has been unblocked.";
    } else {
        echo "Failed to update user status.";
    }
    $stmt->close();
} else {
    echo "Failed to prepare statement.";
}

$conn->close();
header("Location: admin_users.php"); // Redirect to the admin users page
exit();
?>
