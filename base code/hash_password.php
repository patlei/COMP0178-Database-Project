<?php
include_once("connection.php");

// Fetch all users with plaintext passwords (assuming you know which users don’t have hashed passwords)
$sql = "SELECT username, password FROM users";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $username = $row['username'];
    $password = $row['password'];

    // Check if the password is already hashed (assume hashed passwords start with '$')
    if (substr($password, 0, 1) !== '$') {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the user with the hashed password
        $update_sql = "UPDATE users SET password='$hashed_password' WHERE username='$username'";
        $conn->query($update_sql);
    }
}

$conn->close();
echo "All passwords have been hashed.";
?>
