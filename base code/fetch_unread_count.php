<?php
include_once("connection.php");

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$response = ['unread_count' => 0]; // Default response

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query to get the unread count
    $unread_count_sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE username = ? AND is_read = FALSE";
    $stmt = $conn->prepare($unread_count_sql);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($unread_count);
        $stmt->fetch();
        $stmt->close();

        $response['unread_count'] = $unread_count;
    }
}

// Return the unread count as a JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
