<?php
session_start();

// Set default session variables if not logged in
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false;
    $_SESSION['account_type'] = 'user';
}

// Secure session configuration
session_regenerate_id(true); 

// Greeting message for logged-in users
$greeting_message = $_SESSION['logged_in'] && isset($_SESSION['username']) 
    ? "Welcome back, " . htmlspecialchars($_SESSION['username']) . "!" 
    : "";

// Include database connection
include_once("connection.php");

// Retrieve the logged-in username and set unread notifications count
$unread_count = 0;

if ($_SESSION['logged_in'] && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Query to count the unread notifications for the logged-in user
    $unread_count_sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE username = ? AND is_read = FALSE";
    $stmt = $conn->prepare($unread_count_sql);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($unread_count);
        $stmt->fetch();
        $stmt->close();
    } else {
        // You could log this error or handle it appropriately
        error_log("Failed to prepare statement for unread notifications count: " . $conn->error);
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- Bootstrap CSS and FontAwesome -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/custom.css">
  
  <title>Knitty Gritty</title>
</head>

<body>

<div id="loading-spinner" style="display: none;">
    <i class="fa fa-spinner fa-spin"></i> Loading...
</div>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="admin_browse.php"> 
      <img src="../icon.jpg" alt="Knitty Gritty Logo" style="height: 60px; width: auto; margin-right: 10px;">
      <span class="store-title">Knitty Gritty</span>
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ml-auto">
        <!-- Search Form -->
        <li class="nav-item">
          <form class="form-inline my-2 my-lg-0" method="GET" action="admin_browse.php">
            <input class="form-control mr-sm-2" type="text" name="keyword" placeholder="Search listings" aria-label="Search">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
          </form>
        </li>

        <?php if ($_SESSION['logged_in']): ?>
          <!-- User Dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
              <a class="dropdown-item" href="logout.php">Logout</a>
            </div>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Admin Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNavbar">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] === '/admin_browse.php') ? 'active' : ''; ?>" href="admin_browse.php">Browse</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] === '/admin_users.php') ? 'active' : ''; ?>" href="admin_users.php">Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] === '/admin_auctions.php') ? 'active' : ''; ?>" href="admin_auctions.php">Auctions</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Include Bootstrap and JavaScript dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
