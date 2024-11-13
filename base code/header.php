<?php
session_start();

// Default session variables if not logged in
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false;
    $_SESSION['account_type'] = 'guest';
}

// Secure session configuration (optional but recommended)
session_regenerate_id(true); // Regenerate session ID to prevent session fixation attacks

// Display welcome message for logged-in users
$greeting_message = "";
if ($_SESSION['logged_in'] && isset($_SESSION['username'])) {
    $greeting_message = "Welcome back, " . htmlspecialchars($_SESSION['username']) . "!";
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
          <button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">Login</button>
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
      <li class="nav-item mx-1">
        <a class="nav-link <?php echo ($_SERVER['PHP_SELF'] == '/browse.php') ? 'active' : ''; ?>" href="browse.php">Browse</a>
      </li>
      <?php if ($_SESSION['logged_in']): ?>
        <li class="nav-item mx-1">
          <a class="nav-link" href="mybids.php">My Bids</a>
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
      <?php endif; ?>
    </ul>
  </div>
</nav>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Modal Body: Form to login -->
      <div class="modal-body">
        <form method="POST" action="login_result.php">
          <!-- Username field -->
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
          </div>

          <!-- Password field -->
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
          </div>

          <!-- Remember Me -->
          <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember Me</label>
          </div>

          <!-- Submit button -->
          <button type="submit" class="btn btn-primary form-control">Sign in</button>
        </form>

        <!-- Register link -->
        <div class="text-center mt-3">
          Don't have an account? <a href="register.php">Create one</a>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  $(function () {
    $('[data-toggle="tooltip"]').tooltip();
  });
</script>
