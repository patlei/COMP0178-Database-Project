<?php include_once("header.php"); ?>

<div class="container">
    <h2 class="my-3">Register new account</h2>

    <?php
    if (isset($_GET['error'])) {
        echo "<div class='alert alert-danger'>" . htmlspecialchars($_GET['error']) . "</div>";
    }
    ?>

    <form action="process_registration.php" method="post">
        <!-- Username Field -->
        <div class="form-group row">
            <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <small id="usernameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Email Field -->
        <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Password Field -->
        <div class="form-group row">
            <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required. Must be 8-25 characters.</span></small>
            </div>
        </div>

        <!-- Repeat Password Field -->
        <div class="form-group row">
            <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation" placeholder="Enter password again" required>
                <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary form-control">Register</button>
            </div>
        </div>
    </form>

    <div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a></div>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert("Register successfully, you will be guided to browse.");

        setTimeout(() => {
            window.location.href = "browse.php";
        }, 1000);
    }
</script>

<?php include_once("footer.php"); ?>
