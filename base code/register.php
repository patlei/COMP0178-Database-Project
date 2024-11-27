<?php include_once("header.php"); ?>

<div class="container">
    <h2 class="my-3">Register new account</h2>

    <?php
    // Display error message if present
    if (isset($_GET['error'])) {
        echo "<div class='alert alert-danger'>" . htmlspecialchars($_GET['error']) . "</div>";
    }

    // Retain previously entered form values
    $username = $_GET['username'] ?? '';
    $email = $_GET['email'] ?? '';
    $sortcode = $_GET['sortcode'] ?? '';
    $bankaccount = $_GET['bankaccount'] ?? '';
    $phonenumber = $_GET['phonenumber'] ?? '';
    $address_line1 = $_GET['address-line1'] ?? '';
    $address_line2 = $_GET['address-line2'] ?? '';
    $city = $_GET['city'] ?? '';
    $postcode = $_GET['postcode'] ?? '';
    ?>

    <form action="process_registration.php" method="post">
        <!-- Username Field -->
        <div class="form-group row">
            <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
                <small id="usernameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Email Field -->
        <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required, must be a valid email address.</span></small>
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
            <label for="sortcode" class="col-sm-2 col-form-label text-right">Sort Code</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="sortcode" name="sortcode" placeholder="Sort Code" value="<?php echo htmlspecialchars($sortcode); ?>" required>
                <small id="sortcodeHelp" class="form-text text-muted"><span class="text-danger">* Required, must be 6 digits.</span></small>
            </div>
        </div>

        <!-- Bank Account -->
        <div class="form-group row">
            <label for="bankaccount" class="col-sm-2 col-form-label text-right">Bank Account</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="bankaccount" name="bankaccount" placeholder="Bank Account" value="<?php echo htmlspecialchars($bankaccount); ?>" required>
                <small id="bankaccountHelp" class="form-text text-muted"><span class="text-danger">* Required, must be 8 digits.</span></small>
            </div>
        </div>

        <!-- Phone Number -->
        <div class="form-group row">
            <label for="phonenumber" class="col-sm-2 col-form-label text-right">Phone Number</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="phonenumber" name="phonenumber" placeholder="Phone Number" value="<?php echo htmlspecialchars($phonenumber); ?>" required>
                <small id="phonenumberHelp" class="form-text text-muted"><span class="text-danger">* Required, must start with 07.</span></small>
            </div>
        </div>

        <!-- Delivery Address -->
        <div class="form-group row">
            <label for="address-line1" class="col-sm-2 col-form-label text-right">Delivery Address</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="address-line1" name="address-line1" placeholder="First Line" value="<?php echo htmlspecialchars($address_line1); ?>" required>
                <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>
        <div class="form-group row">
            <label for="address-line2" class="col-sm-2 col-form-label text-right"></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="address-line2" name="address-line2" placeholder="Second Line" value="<?php echo htmlspecialchars($address_line2); ?>">
                <small class="form-text text-muted">Optional.</small>
            </div>
        </div>
        <div class="form-group row">
            <label for="city" class="col-sm-2 col-form-label text-right">City</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="city" name="city" placeholder="City" value="<?php echo htmlspecialchars($city); ?>" required>
                <small class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Postcode -->
        <div class="form-group row">
            <label for="postcode" class="col-sm-2 col-form-label text-right">Postcode</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="postcode" name="postcode" placeholder="Postcode" value="<?php echo htmlspecialchars($postcode); ?>" required>
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

<?php include_once("footer.php"); ?>
