<?php
include 'connection.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'], $_POST['password'], $_POST['passwordConfirmation'], $_POST['accountType'])) {
        $accountType = $_POST['accountType'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $passwordConfirmation = $_POST['passwordConfirmation'];

        // Check if passwords match
        if ($password !== $passwordConfirmation) {
            die("Passwords do not match.");
        }

        // Check if the email is already registered with the same account type
        $sql = "SELECT * FROM users WHERE email = ? AND accountType = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $accountType);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "You have already registered with this email for the specified account type.";
        } else {
            // Proceed with registration since the email is unique for the specified account type
            $sql = "INSERT INTO users (accountType, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $accountType, $email, $password);

            if ($stmt->execute()) {
                echo "Registration successful!";
            } else {
                echo "Error: " . $conn->error;
            }
        }

        $stmt->close();
    } else {
        echo "Required form data is missing.";
    }
} else {
    echo "Invalid request method.";
}

mysqli_close($conn);
