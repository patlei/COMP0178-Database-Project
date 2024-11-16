<?php
include_once("header.php");
include 'connection.php'; 

session_start();

// If the user is already logged in, redirect to the appropriate page
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['accountType'] === 'admin') {
        header("Location: admin_page.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password from the form submission
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Check if input fields are empty
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // Query to get user information
        $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Validate password based on account type
            if ($user['accountType'] === 'user') {
                // For regular users, use password_verify for hashed passwords
                if (password_verify($password, $user['password'])) {
                    // Set session variables for successful login
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['accountType'] = $user['accountType'];
                    header("Location: index.php");
                    exit;
                } else {
                    $error_message = "Invalid username or password.";
                }
            } elseif ($user['accountType'] === 'admin') {
                // For admin users, use direct password comparison (if stored in plain text)
                if ($password === $user['password']) {
                    // Set session variables for successful login
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['accountType'] = $user['accountType'];
                    header("Location: admin_page.php");
                    exit;
                } else {
                    $error_message = "Invalid username or password.";
                }
            } else {
                $error_message = "Invalid account type.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!-- HTML for the Login Form -->
<div class="container">
    <h2 class="my-3">Login to your account</h2>

    <!-- Display error message if login fails -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Login form -->
    <form method="POST" action="">
        <div class="form-group row">
            <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
            </div>
        </div>

        <div class="form-group row">
            <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
        </div>

        <div class="form-group row">
            <button type="submit" class="btn btn-primary form-control">Login</button>
        </div>
    </form>

    <div class="text-center">Don't have an account? <a href="register.php">Register here</a></div>
</div>

<?php include_once("footer.php"); ?>
