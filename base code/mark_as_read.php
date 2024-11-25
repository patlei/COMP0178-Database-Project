<?php
include_once("connection.php");

// Start the session if it hasn't already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    if (!headers_sent()) {
        header('Location: login.php'); // Redirect to login if not logged in
        exit();
    } else {
        echo "<script>window.location.href='login.php';</script>";
        exit();
    }
}

// Get the username of the logged-in user
$username = $_SESSION['username'];

// Check if database connection is successful
if ($conn->connect_error) {
    $_SESSION['error_message'] = "Database connection failed: " . $conn->connect_error;
    if (!headers_sent()) {
        header('Location: notifications.php');
    } else {
        echo "<script>window.location.href='notifications.php';</script>";
    }
    exit();
}

// If a notification ID is provided, mark only that notification as read
if (isset($_POST['notification_id'])) {
    $notification_id = intval($_POST['notification_id']); // Ensure notification_id is an integer

    $update_sql = "UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND username = ?";
    $stmt = $conn->prepare($update_sql);

    if ($stmt) {
        $stmt->bind_param("is", $notification_id, $username);

        // Execute the query and check for success
        if ($stmt->execute()) {
            echo "success"; // Send success response for AJAX
        } else {
            echo "failure"; // Send failure response for AJAX
        }

        $stmt->close();
    } else {
        echo "failure"; // Send failure response for AJAX if statement preparation fails
    }

    exit(); // End script to prevent further code execution
}

// If no notification ID is provided, mark all notifications as read
$update_all_sql = "UPDATE notifications SET is_read = 1 WHERE username = ?";
$stmt_all = $conn->prepare($update_all_sql);

if ($stmt_all) {
    $stmt_all->bind_param("s", $username);

    // Execute the query and check for success
    if ($stmt_all->execute()) {
        $_SESSION['success_message'] = "All notifications have been marked as read.";
    } else {
        $_SESSION['error_message'] = "Failed to mark all notifications as read. Please try again.";
    }

    $stmt_all->close();
} else {
    // Prepare statement failed
    $_SESSION['error_message'] = "Failed to prepare statement to mark notifications as read. Please try again.";
}

// Redirect back to notifications page
if (!headers_sent()) {
    header('Location: notifications.php');
} else {
    echo "<script>window.location.href='notifications.php';</script>";
}
exit();
?>
