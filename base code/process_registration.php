<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required fields are set
    $requiredFields = [
        'username', 'email', 'password', 'passwordConfirmation', 
        'sortcode', 'bankaccount', 'phonenumber', 
        'address-line1', 'city', 'postcode'
    ];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $error = "Required form data is missing: $field.";
            header("Location: register.php?error=" . urlencode($error));
            exit();
        }
    }

    // Retrieve form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordConfirmation = $_POST['passwordConfirmation'];
    $sort_code = trim($_POST['sortcode']);
    $bank_account = trim($_POST['bankaccount']);
    $phone_number = trim($_POST['phonenumber']);
    $delivery_address = trim($_POST['address-line1']) . ' ' . trim($_POST['address-line2'] ?? '') . ', ' . trim($_POST['city']);
    $postcode = trim($_POST['postcode']);

    // Validate passwords match
    if ($password !== $passwordConfirmation) {
        $error = "Passwords do not match.";
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }

    // Validate password length
    if (strlen($password) < 8 || strlen($password) > 25) {
        $error = "Password must be between 8 and 25 characters.";
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }

    // Validate sort code and bank account
    if (!preg_match('/^\d{6}$/', $sort_code)) {
        $error = "Sort code must be 6 digits.";
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }
    if (!preg_match('/^\d{8}$/', $bank_account)) {
        $error = "Bank account must be 8 digits.";
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }

    // Validate phone number format
    if (!preg_match('/^07\d{9}$/', $phone_number)) {
        $error = "Phone number must start with 07 and be 11 digits.";
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }

    // Check if the email or username is already registered
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Account already exists with this username or email.";
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }

    // Insert into users table
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Use hashing for security
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Insert into profile table
        $sql = "INSERT INTO profile (username, sort_code, bank_account, phone_number, delivery_address, postcode) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $sort_code, $bank_account, $phone_number, $delivery_address, $postcode);

        if ($stmt->execute()) {
            // Redirect to success page
            header("Location: register.php?success=1");
            exit();
        } else {
            $error = "Error inserting profile data: " . $stmt->error;
            header("Location: register.php?error=" . urlencode($error));
            exit();
        }
    } else {
        $error = "Error inserting user data: " . $stmt->error;
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
