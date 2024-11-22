<?php
include 'connection.php'; // Database connection file
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve username and password from POST request
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Check if input fields are empty
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
        header("Location: login.php?error=" . urlencode($error_message));
        exit;
    } else {
        // Prepare SQL query to find the user by username
        $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username); // 's' denotes a string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password using password_verify(hash) for all users
            if (password_verify($password, $user['password'])) {
                // Login successful, set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['accountType'] = $user['accountType'];

                // Redirect based on account type
                if ($user['accountType'] === 'admin') {
                    header("Location: admin_browse.php");
                } else {
                    header("Location: browse.php");
                }
                exit;
            } else {
                $error_message = "Invalid username or password.";
                header("Location: login.php?error=" . urlencode($error_message));
                exit;
            }
        } else {
            // User not found
            $error_message = "Invalid username or password.";
            header("Location: login.php?error=" . urlencode($error_message));
            exit;
        }

        $stmt->close();
    }
}

$conn->close();
?>
