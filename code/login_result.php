<?php
include_once("header.php");
include 'connection.php'; 

session_start();

// If the user is already logged in, redirect to the home page
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit; // Make sure no further code is executed after the redirect
}

// Handle the login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract username and password from POST data
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate input (ensure both username and password are provided)
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        // Prepare SQL query to find the user by username
        $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username); // 's' denotes a string
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check if the provided password matches the stored hash using password_verify
            if (password_verify($password, $user['password'])) {
                // Successful login, set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $user['username'];
                
                // Redirect to home page after successful login
                header("Location: index.php");
                exit; // Make sure no further code is executed after the redirect
            } else {
                // Password does not match
                $error_message = "Invalid username or password.";
            }
        } else {
            // User not found
            $error_message = "Invalid username or password.";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<div class="container">
    <h2 class="my-3">Login to your account</h2>

    <!-- Show error message if login fails -->
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Login form -->
    <form method="POST" action="login_result.php">
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
