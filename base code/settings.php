<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['username'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$current_username = $_SESSION['username'];

// Fetch user and profile details
$user_sql = "SELECT username, email FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $current_username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    echo "User not found.";
    exit();
}
$user = $user_result->fetch_assoc();

$profile_sql = "SELECT bank_account, delivery_address FROM profile WHERE username = ?";
$profile_stmt = $conn->prepare($profile_sql);
$profile_stmt->bind_param("s", $current_username);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();

if ($profile_result->num_rows === 0) {
    echo "Profile information not found.";
    exit();
}
$profile = $profile_result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $bank_account = trim($_POST['bank_account']);
    $delivery_address = trim($_POST['delivery_address']);

    // Validate form data
    if (empty($new_username) || empty($new_email)) {
        $error = "Username and email cannot be empty.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Begin transaction to ensure consistency
        $conn->begin_transaction();
        try {
            // Update users table
            $update_user_sql = "UPDATE users SET username = ?, email = ?" . (!empty($new_password) ? ", password = ?" : "") . " WHERE username = ?";
            $user_stmt = $conn->prepare($update_user_sql);

            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $user_stmt->bind_param("ssss", $new_username, $new_email, $hashed_password, $current_username);
            } else {
                $user_stmt->bind_param("sss", $new_username, $new_email, $current_username);
            }

            if (!$user_stmt->execute()) {
                throw new Exception("Error updating personal information: " . $user_stmt->error);
            }

            // Update profile table
            $update_profile_sql = "UPDATE profile SET bank_account = ?, delivery_address = ? WHERE username = ?";
            $profile_stmt = $conn->prepare($update_profile_sql);
            $profile_stmt->bind_param("sss", $bank_account, $delivery_address, $new_username);

            if (!$profile_stmt->execute()) {
                throw new Exception("Error updating profile information: " . $profile_stmt->error);
            }

            // Update session username if changed
            if ($new_username !== $current_username) {
                $_SESSION['username'] = $new_username;
            }

            // Commit transaction
            $conn->commit();
            $success = "Your settings have been updated successfully.";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
</head>
<body>
<?php include("header.php"); ?>

<div class="container mt-5">
    <h2>Settings</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="settings.php">
        <!-- Username -->
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">New Password (Leave blank to keep current password)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
        </div>

        <!-- Bank Account -->
        <div class="form-group">
            <label for="bank_account">Bank Account</label>
            <input type="text" class="form-control" id="bank_account" name="bank_account" value="<?php echo htmlspecialchars($profile['bank_account']); ?>" required>
        </div>

        <!-- Delivery Address -->
        <div class="form-group">
            <label for="delivery_address">Delivery Address</label>
            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required><?php echo htmlspecialchars($profile['delivery_address']); ?></textarea>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Debug Dropdown Functionality -->
<script>
    $(document).ready(function () {
        console.log("Dropdowns are initialized.");
        $('.dropdown-toggle').dropdown(); // Ensure dropdowns are activated
    });
</script>

</body>
</html>

<?php
$conn->close(); // Close database connection
?>
