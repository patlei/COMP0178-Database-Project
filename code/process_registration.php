<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['passwordConfirmation'])) {
        
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $passwordConfirmation = $_POST['passwordConfirmation'];

        // Check if passwords match
        if ($password !== $passwordConfirmation) {
            $error = "Passwords do not match.";
            header("Location: register.php?error=" . urlencode($error));
            exit();
        }

        // Check if password length is between 8 and 25 characters
        if (strlen($password) < 8 || strlen($password) > 25) {
            $error = "Password must be between 8 and 25 characters.";
            header("Location: register.php?error=" . urlencode($error));
            exit();
        }

        // Check if email contains "@" symbol
        if (strpos($email, '@') === false) {
            $error = "Email must contain '@' symbol.";
            header("Location: register.php?error=" . urlencode($error));
            exit();
        }

        // Check if the email or the username is already registered
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Account already exists with this username or email.";
            header("Location: register.php?error=" . urlencode($error));
            exit();
        } else {
            // Insert new user into the database
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hashing the password for security
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                header("Location: register.php?success=1"); // Redirect to register.php with success parameter
                exit();
            } else {
                $error = "Error inserting data: " . $stmt->error;
                header("Location: register.php?error=" . urlencode($error));
                exit();
            }
        }

        $stmt->close();
    } else {
        $error = "Required form data is missing.";
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }
} else {
    $error = "Invalid request method.";
    header("Location: register.php?error=" . urlencode($error));
    exit();
}

$conn->close();
?>
