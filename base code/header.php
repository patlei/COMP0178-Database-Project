<?php
// Start output buffering to prevent "headers already sent" errors
ob_start(); 

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include other necessary files
include_once('connection.php');
include_once('utilities.php');

// Update auction status (ensures any completed auctions are marked as closed)
update_auction_status($conn);

// Update watchlist notifications for ending auctions
update_watchlist_notifications($conn);

// Default session variables if not logged in
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false;
    $_SESSION['account_type'] = 'guest';
}

// Secure session configuration 
session_regenerate_id(true); // Regenerate session ID to prevent session fixation attacks

// Display welcome message for logged-in users
$greeting_message = "";
if ($_SESSION['logged_in'] && isset($_SESSION['username'])) {
    $greeting_message = "Welcome back, " . htmlspecialchars($_SESSION['username']) . "!";
}

// Check if the logged-in user is blocked
$blocked_message = "";
if ($_SESSION['logged_in']) {
    $blockedQuery = "SELECT blocked FROM users WHERE username = ?";
    $blockedStmt = $conn->prepare($blockedQuery);
    $blockedStmt->bind_param("s", $_SESSION['username']);
    $blockedStmt->execute();
    $blockedStmt->bind_result($blocked);
    $blockedStmt->fetch();
    $blockedStmt->close();

    if ($blocked) {
        $blocked_message = "<div class='alert alert-danger text-center mb-0' role='alert'>
                                Your account is blocked. Some features may be restricted.
                            </div>";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- Bootstrap and FontAwesome CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">

  <!-- Custom CSS file -->
  <link rel="stylesheet" href="css/custom.css">

  <title>Knitty Gritty Online Store</title>
</head>

<body>

<!-- Blocked Message -->
<?php echo $blocked_message; ?>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="index.php" style="display: flex; align-items: center;">
      <img src="../icon.jpg" alt="Knitty Gritty Logo" style="height: 60px; width: auto; margin-right: 10px;">
      <span class="store-title">Knitty Gritty</span>
  </a>

  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarContent">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item mx-2">
        <form class="form-inline" method="GET" action="browse.php">
          <input class="form-control mr-sm-2" type="text" name="keyword" placeholder="Search listings" aria-label="Search">
          <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
      </li>
      
      <?php if ($_SESSION['logged_in']): ?>
        <li class="nav-item mx-2">
          <a class="nav-link" href="notifications.php"><i class="fa fa-bell"></i></a>
        </li>
        <li class="nav-item dropdown mx-2">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-user"></i> <?php echo $greeting_message; ?>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
            <a class="dropdown-item" href="profile.php">My Profile</a>
            <a class="dropdown-item" href="settings.php">Settings</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="logout.php">Logout</a>
          </div>
        </li>
      <?php else: ?>
        <li class="nav-item mx-2">
          <a href="login.php" class="nav-link">Login</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<!-- Second Navbar for Site Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="siteNavbar">
    <ul class="navbar-nav">
      <!-- Browse Tab (Available to Everyone) -->
      <li class="nav-item mx-1">
        <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/browse.php') ? 'active' : ''; ?>" href="browse.php">Browse</a>
      </li>
      
      <?php if ($_SESSION['logged_in']): ?>
        <!-- Tabs for Logged-in Users -->
        <li class="nav-item mx-1">
          <a class="nav-link" href="mybids.php">My Bids</a>
        </li>
        <li class="nav-item mx-1">
          <a class="nav-link" href="mywatchlist.php">My Watchlist</a>
        </li>
        <li class="nav-item mx-1">
          <a class="nav-link" href="recommendations.php">Recommended</a>
        </li>
        <li class="nav-item mx-1">
          <a class="nav-link" href="mylistings.php">My Listings</a>
        </li>
        <li class="nav-item ml-3">
          <a class="nav-link btn btn-outline-light" href="create_auction.php">+ Create Auction</a>
        </li>

        <?php if ($_SESSION['account_type'] == 'admin'): ?>
          <!-- Admin-Specific Tabs -->
          <li class="nav-item mx-1">
            <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/admin_users.php') ? 'active' : ''; ?>" href="admin_users.php">Users</a>
          </li>
          <li class="nav-item mx-1">
            <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/admin_auctions.php') ? 'active' : ''; ?>" href="admin_auctions.php">Auctions</a>
          </li>
        <?php endif; ?>
      <?php endif; ?>
    </ul>
  </div>
</nav>


<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(function () {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
