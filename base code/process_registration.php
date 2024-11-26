<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';

function redirect_with_error($error, $data = []) {
    $query = http_build_query(array_merge(['error' => $error], $data));
    header("Location: register.php?$query");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data and trim whitespace
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'passwordConfirmation' => $_POST['passwordConfirmation'] ?? '',
        'sortcode' => trim($_POST['sortcode'] ?? ''),
        'bankaccount' => trim($_POST['bankaccount'] ?? ''),
        'phonenumber' => trim($_POST['phonenumber'] ?? ''),
        'address-line1' => trim($_POST['address-line1'] ?? ''),
        'address-line2' => trim($_POST['address-line2'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'postcode' => trim($_POST['postcode'] ?? '')
    ];

    // Check for required fields
    foreach ($data as $field => $value) {
        if (empty($value) && $field !== 'address-line2') {
            redirect_with_error("The field '$field' is required.", $data);
        }
    }

    // Validate passwords match
    if ($data['password'] !== $data['passwordConfirmation']) {
        redirect_with_error("Passwords do not match.", $data);
    }

    // Validate password length
    if (strlen($data['password']) < 8 || strlen($data['password']) > 25) {
        redirect_with_error("Password must be between 8 and 25 characters.", $data);
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        redirect_with_error("Invalid email format.", $data);
    }

    // Validate sort code and bank account
    if (!preg_match('/^\d{6}$/', $data['sortcode'])) {
        redirect_with_error("Sort code must be 6 digits.", $data);
    }
    if (!preg_match('/^\d{8}$/', $data['bankaccount'])) {
        redirect_with_error("Bank account must be 8 digits.", $data);
    }

    // Validate phone number format
    if (!preg_match('/^07\d{9}$/', $data['phonenumber'])) {
        redirect_with_error("Phone number must start with 07 and be 11 digits.", $data);
    }

    // Check if the email or username is already registered
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $data['username'], $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        redirect_with_error("Account already exists with this username or email.", $data);
    }

    // Insert into users table
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT); // Use hashing for security
    $stmt->bind_param("sss", $data['username'], $data['email'], $hashedPassword);

    if ($stmt->execute()) {
        // Insert into profile table
        $sql = "INSERT INTO profile (username, sort_code, bank_account, phone_number, delivery_address, postcode) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $delivery_address = $data['address-line1'] . ' ' . $data['address-line2'] . ', ' . $data['city'];
        $stmt->bind_param(
            "ssssss",
            $data['username'],
            $data['sortcode'],
            $data['bankaccount'],
            $data['phonenumber'],
            $delivery_address,
            $data['postcode']
        );

        if ($stmt->execute()) {
            // Redirect to success page
            header("Location: register.php?success=1");
            exit();
        } else {
            redirect_with_error("Error inserting profile data: " . $stmt->error, $data);
        }
    } else {
        redirect_with_error("Error inserting user data: " . $stmt->error, $data);
    }
} else {
    redirect_with_error("Invalid request method.");
}

$conn->close();
?>
