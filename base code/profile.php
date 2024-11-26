<?php
session_start();
require_once("connection.php");

if (!isset($_SESSION['username'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

// Determine the username to display
$logged_in_username = $_SESSION['username'];
$view_username = isset($_GET['seller_username']) ? $_GET['seller_username'] : $logged_in_username;


// Fetch user profile information
$user_sql = "SELECT email FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $view_username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user = $user_result->fetch_assoc();
} else {
    echo "User information not found.";
    exit();
}

$profile_sql = "SELECT bank_account, delivery_address FROM profile WHERE username = ?";
$profile_stmt = $conn->prepare($profile_sql);
$profile_stmt->bind_param("s", $view_username);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();

if ($profile_result->num_rows > 0) {
    $profile = $profile_result->fetch_assoc();
} else {
    echo "Profile information not found.";
    exit();
}

// Fetch My Purchases
$purchases_sql = "SELECT a.auction_id, a.item_name, s.seller_username, s.sale_price 
                  FROM sales s 
                  JOIN auction a ON s.auction_id = a.auction_id 
                  WHERE s.buyer_username = ?";
$purchases_stmt = $conn->prepare($purchases_sql);
$purchases_stmt->bind_param("s", $view_username);
$purchases_stmt->execute();
$purchases_result = $purchases_stmt->get_result();
$purchases = $purchases_result->fetch_all(MYSQLI_ASSOC);

// Fetch My Sold Items
$sold_sql = "SELECT a.auction_id, a.item_name, s.buyer_username, s.sale_price 
             FROM sales s 
             JOIN auction a ON s.auction_id = a.auction_id 
             WHERE s.seller_username = ?";
$sold_stmt = $conn->prepare($sold_sql);
$sold_stmt->bind_param("s", $view_username);
$sold_stmt->execute();
$sold_result = $sold_stmt->get_result();
$sold_items = $sold_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Personal Information</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css">
</head>
<body>
<?php include("header.php"); ?>

<div class="container mt-5">
    <h2>My Personal Information</h2>
    <table class="table table-bordered table-striped">
        <tr>
            <th style="width: 30%;">Username</th>
            <td><?php echo htmlspecialchars($view_username); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
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
</div>


<div class="container mt-5">
    <h2>My Purchases</h2>
    <?php if (!empty($purchases)): ?>
        <?php foreach ($purchases as $purchase): ?>
            <table class="table table-bordered table-striped">
                <tr>
                    <th style="width: 30%;">Auction ID</th>
                    <td><?php echo htmlspecialchars($purchase['auction_id']); ?></td>
                </tr>
                <tr>
                    <th>Item Name</th>
                    <td><?php echo htmlspecialchars($purchase['item_name']); ?></td>
                </tr>
                <tr>
                    <th>Seller Username</th>
                    <td><?php echo htmlspecialchars($purchase['seller_username']); ?></td>
                </tr>
                <tr>
                    <th>Sale Price</th>
                    <td>£<?php echo htmlspecialchars($purchase['sale_price']); ?></td>
                </tr>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No purchases found.</p>
    <?php endif; ?>
</div>

<div class="container mt-5">
    <h2>My Sold Items</h2>
    <?php if (!empty($sold_items)): ?>
        <?php foreach ($sold_items as $sold): ?>
            <table class="table table-bordered table-striped">
                <tr>
                    <th style="width: 30%;">Auction ID</th>
                    <td><?php echo htmlspecialchars($sold['auction_id']); ?></td>
                </tr>
                <tr>
                    <th>Item Name</th>
                    <td><?php echo htmlspecialchars($sold['item_name']); ?></td>
                </tr>
                <tr>
                    <th>Buyer Username</th>
                    <td><?php echo htmlspecialchars($sold['buyer_username']); ?></td>
                </tr>
                <tr>
                    <th>Sale Price</th>
                    <td>£<?php echo htmlspecialchars($sold['sale_price']); ?></td>
                </tr>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No sold items found.</p>
    <?php endif; ?>
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
