<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['passwordConfirmation'], $_POST['accountType'])) {
        
        $username = $_POST['username']; // New field
        $accountType = $_POST['accountType'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $passwordConfirmation = $_POST['passwordConfirmation'];

        // Check if passwords match
        if ($password !== $passwordConfirmation) {
            echo "Passwords do not match.";
            exit();
        }

        // Check if the email is already registered with the same account type
        $sql = "SELECT * FROM users WHERE email = ? AND accountType = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $accountType);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "Account already exists";
            exit();
        } else {
            // Hash the password before storing it in the database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, accountType, email, password) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $accountType, $email, $hashedPassword);

            if ($stmt->execute()) {
                echo "Registration successful!";
            } else {
                echo "Error inserting data: " . $stmt->error;
            }
        }

        $stmt->close();
    } else {
        echo "Required form data is missing.";
    }
} else {
    echo "Invalid request method.";
}

$conn->close();