<?php
include("header.php");
require_once("connection.php");

if (!isset($_SESSION['username'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$current_username = $_SESSION['username'];

// Fetch user and profile details function
function fetchUserData($conn, $current_username)
{
    // Fetch user details
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

    // Fetch profile details
    $profile_sql = "SELECT sort_code, bank_account, phone_number, delivery_address, postcode FROM profile WHERE username = ?";
    $profile_stmt = $conn->prepare($profile_sql);
    $profile_stmt->bind_param("s", $current_username);
    $profile_stmt->execute();
    $profile_result = $profile_stmt->get_result();

    if ($profile_result->num_rows === 0) {
        echo "Profile information not found.";
        exit();
    }
    $profile = $profile_result->fetch_assoc();

    return [$user, $profile];
}

// Fetch user data
list($user, $profile) = fetchUserData($conn, $current_username);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_email = trim($_POST['email']);
    $new_sort_code = trim($_POST['sort_code']);
    $new_bank_account = trim($_POST['bank_account']);
    $new_phone_number = trim($_POST['phone_number']);
    $new_delivery_address = trim($_POST['delivery_address']);
    $new_postcode = trim($_POST['postcode']);

    // Validate form data
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^\d{6}$/', $new_sort_code)) {
        $error = "Sort code must be 6 digits.";
    } elseif (!preg_match('/^\d{8}$/', $new_bank_account)) {
        $error = "Bank account must be 8 digits.";
    } elseif (!preg_match('/^07\d{9}$/', $new_phone_number)) {
        $error = "Phone number must start with 07 and be 11 digits.";
    } elseif (empty($new_delivery_address) || empty($new_postcode)) {
        $error = "Delivery address and postcode cannot be empty.";
    } else {
        // Begin transaction to ensure consistency
        $conn->begin_transaction();
        try {
            // Update email in users table
            $update_user_sql = "UPDATE users SET email = ? WHERE username = ?";
            $user_stmt = $conn->prepare($update_user_sql);
            $user_stmt->bind_param("ss", $new_email, $current_username);

            if (!$user_stmt->execute()) {
                throw new Exception("Error updating email: " . $user_stmt->error);
            }

            // Update profile table
            $update_profile_sql = "UPDATE profile SET sort_code = ?, bank_account = ?, phone_number = ?, delivery_address = ?, postcode = ? WHERE username = ?";
            $profile_stmt = $conn->prepare($update_profile_sql);
            $profile_stmt->bind_param("ssssss", $new_sort_code, $new_bank_account, $new_phone_number, $new_delivery_address, $new_postcode, $current_username);

            if (!$profile_stmt->execute()) {
                throw new Exception("Error updating profile information: " . $profile_stmt->error);
            }

            // Commit transaction
            $conn->commit();
            $success = "Your settings have been updated successfully.";

            // Re-fetch updated data
            list($user, $profile) = fetchUserData($conn, $current_username);
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


<div class="container mt-5">
    <h2>Settings</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="settings.php">
        <!-- Username (Read-only) -->
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <!-- Sort Code -->
        <div class="form-group">
            <label for="sort_code">Sort Code</label>
            <input type="text" class="form-control" id="sort_code" name="sort_code" value="<?php echo htmlspecialchars($profile['sort_code']); ?>" required>
        </div>

        <!-- Bank Account -->
        <div class="form-group">
            <label for="bank_account">Bank Account</label>
            <input type="text" class="form-control" id="bank_account" name="bank_account" value="<?php echo htmlspecialchars($profile['bank_account']); ?>" required>
        </div>

        <!-- Phone Number -->
        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($profile['phone_number']); ?>" required>
        </div>

        <!-- Delivery Address -->
        <div class="form-group">
            <label for="delivery_address">Delivery Address</label>
            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required><?php echo htmlspecialchars($profile['delivery_address']); ?></textarea>
        </div>

        <!-- Postcode -->
        <div class="form-group">
            <label for="postcode">Postcode</label>
            <input type="text" class="form-control" id="postcode" name="postcode" value="<?php echo htmlspecialchars($profile['postcode']); ?>" required>
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
