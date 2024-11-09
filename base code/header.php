<?php
session_start();

// Default session variables if not logged in
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false; // Ensure this is false by default if the user isn't logged in
    $_SESSION['account_type'] = 'guest'; // Default to a guest account type, or some default state
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

  <!-- Custom CSS file -->
  <link rel="stylesheet" href="css/custom.css">

  <title>[My Auction Site] <!--CHANGEME!--></title>
</head>


<body>

<!-- Navbars -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="#">Site Name <!--CHANGEME!--></a>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
    
<?php
  // Displays either login or logout on the right, depending on user's
  // current status (session).
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    echo '<a class="nav-link" href="logout.php">Logout</a>';
  } else {
    echo '<button type="button" class="btn nav-link" data-toggle="modal" data-target="#loginModal">Login</button>';
  }
?>

    </li>
  </ul>
</nav>

<!-- Second Navbar  -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <ul class="navbar-nav align-middle">
    <li class="nav-item mx-1">
      <a class="nav-link" href="browse.php">Browse</a>
    </li>
    
<?php
  // If user is logged in, show all 4 buttons
  if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo('
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
        <a class="nav-link btn border-light" href="create_auction.php">+ Create Auction</a>
      </li>
    ');
  }
?>
  </ul>
</nav>


<!-- Login modal -->
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

