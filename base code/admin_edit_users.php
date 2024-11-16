<?php

include_once("connection.php");    // Database connection
include_once("header_admin.php");  // Admin header

// // Ensure the user is an admin
// if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'admin') {
//     header("Location: login.php");
//     exit();
// }

// Check if username is provided in the URL
if (!isset($_GET['username'])) {
    echo "No user specified.";
    exit();
}

$username = $_GET['username'];

// Fetch user data from the database using the username
$sql = "SELECT username, email, average_rating, accountType FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "User not found.";
    exit();
}

$user = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $average_rating = $_POST['average_rating'];
    $account_type = isset($_POST['block_user']) ? 'blocked' : 'user';

    // Update user information
    $update_sql = "UPDATE users SET email = ?, average_rating = ?, accountType = ? WHERE username = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("siss", $email, $average_rating, $account_type, $username);

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>User updated successfully.</div>";
        // Refresh user data after update
        $user['email'] = $email;
        $user['average_rating'] = $average_rating;
        $user['accountType'] = $account_type;
    } else {
        echo "<div class='alert alert-danger'>Error updating user.</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Edit User - <?php echo htmlspecialchars($user['username']); ?></h2>
    <form method="POST" action="">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="average_rating">Average Rating</label>
            <input type="number" class="form-control" id="average_rating" name="average_rating" value="<?php echo htmlspecialchars($user['average_rating']); ?>" min="0" max="10" required>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="block_user" name="block_user" <?php echo ($user['accountType'] == 'blocked') ? 'checked' : ''; ?>>
            <label class="form-check-label" for="block_user">Block User</label>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="admin_users.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conn->close();
?>
