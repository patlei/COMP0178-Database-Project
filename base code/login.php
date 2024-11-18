<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error_message = "";
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/custom.css">

  <title>Login - Knitty Gritty</title>

  <?php if ($error_message): ?>
  <script>
      // Automatically redirect to login.php after 3 seconds if there's an error
      setTimeout(function() {
          window.location.href = "login.php";
      }, 3000);
  </script>
  <?php endif; ?>
</head>
<body>

<!-- Top Navbar with Logo Only -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="index.php" style="display: flex; align-items: center;">
      <img src="../icon.jpg" alt="Knitty Gritty Logo" style="height: 60px; width: auto; margin-right: 10px;">
      <span class="store-title">Knitty Gritty</span>
  </a>
</nav>

<!-- Second Navbar with Only "Browse" Link -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="collapse navbar-collapse">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <a class="nav-link" href="browse.php">Browse</a>
      </li>
    </ul>
  </div>
</nav>

<div class="container mt-5">
    <h2 class="text-center mb-4">Login to your account</h2>

    <!-- Display error message if login fails -->
    <?php if ($error_message): ?>
        <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Login form -->
    <form method="POST" action="process_login.php" class="mx-auto" style="max-width: 400px;">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
    </form>

    <div class="text-center mt-3">
        Don't have an account? <a href="register.php">Create one</a>
    </div>
</div>

<!-- Optional JavaScript and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
