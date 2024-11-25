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
                <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required, must be a valid email address. </span></small>
            </div>
        </div>

        <!-- Password Field -->
        <div class="form-group row">
            <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required, must be 8-25 characters.</span></small>
            </div>
        </div>

        <!-- Repeat Password Field -->
        <div class="form-group row">
            <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Confirmed password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation" placeholder="Enter password again" required>
                <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Sort Code -->
        <div class="form-group row">
            <label for="sort code" class="col-sm-2 col-form-label text-right">Sort Code</label>
            <div class="col-sm-10">
                <input type="sortcode" class="form-control" id="sortcode" name="sortcode" placeholder="Sort Code" required>
                <small id="sortcodeHelp" class="form-text text-muted"><span class="text-danger">* Required, must be 6 digits.</span></small>
            </div>
        </div>

        <!-- Bank Account -->
        <div class="form-group row">
            <label for="bank account" class="col-sm-2 col-form-label text-right">Bank Account</label>
            <div class="col-sm-10">
                <input type="bankaccount" class="form-control" id="bankaccount" name="bankaccount" placeholder="Bank Account" required>
                <small id="bankaccountHelp" class="form-text text-muted"><span class="text-danger">* Required, must be 8 digits.</span></small>
            </div>
        </div>

        <!-- Phone Number -->
        <div class="form-group row">
            <label for="phone number" class="col-sm-2 col-form-label text-right">Phone Number</label>
            <div class="col-sm-10">
                <input type="phonenumber" class="form-control" id="phonenumber" name="phonenumber" placeholder="Phone Number" required>
                <small id="phonenumberHelp" class="form-text text-muted"><span class="text-danger">* Required, must start with 07.</span></small>
            </div>
        </div>


        <!-- Delivery Address -->
        <div class="form-group row">
            <label for="address-line1" class="col-sm-2 col-form-label text-right">Delivery Address</label>
             <div class="col-sm-10">
                <input type="text" class="form-control" id="address-line1" name="address-line1" placeholder="First Line" required>
                <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>
        <div class="form-group row">
            <label for="address-line2" class="col-sm-2 col-form-label text-right"></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="address-line2" name="address-line2" placeholder="Second Line">
                <small class="form-text text-muted">Optional.</small>
            </div>
        </div>

        <div class="form-group row">
            <label for="city" class="col-sm-2 col-form-label text-right"></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="city" name="city" placeholder="City" required>
                <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Postcode -->
        <div class="form-group row">
            <label for="postcode" class="col-sm-2 col-form-label text-right">Postcode</label>
            <div class="col-sm-10">
                <input type="postcode" class="form-control" id="postcode" name="postcode" placeholder="Postcode" required>
                <small id="postcodeHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary form-control">Register</button>
            </div>
        </div>
    </form>

    <div class="text-center">Already have an account? <a href="login.php">Login</a></div>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert("Register successfully, you will be guided to login.");

        setTimeout(() => {
            window.location.href = "login.php";
        }, 1000);
    }
</script>

<?php include_once("footer.php"); ?>
