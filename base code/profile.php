<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['username'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user profile information
$sql = "SELECT bank_account, delivery_address FROM profile WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
} else {
    echo "Profile information not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
</head>
<body>
<?php include("header.php"); ?>

<div class="container mt-5">
    <h2>My Profile</h2>
    <table class="table table-bordered">
        <tr>
            <th>Username</th>
            <td><?php echo htmlspecialchars($username); ?></td>
        </tr>
        <tr>
            <th>Bank Account</th>
            <td><?php echo htmlspecialchars($profile['bank_account']); ?></td>
        </tr>
        <tr>
            <th>Delivery Address</th>
            <td><?php echo htmlspecialchars($profile['delivery_address']); ?></td>
        </tr>
    </table>

    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
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
