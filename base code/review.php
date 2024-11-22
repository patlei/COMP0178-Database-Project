<?php 
include_once("header.php");
include 'connection.php'; 

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: login.php'); // Redirect to login if not logged in
  exit;
}
$username = $_SESSION['username']; // Get the username from the session

// Get the auction ID from the URL parameter
if (!isset($_GET['auction_id']) || empty($_GET['auction_id']) || !is_numeric($_GET['auction_id'])) {
    echo "No auction selected!";
    exit;
  }
$auction_id = intval($_GET['auction_id']);

// Get the seller_username from the URL parameter
if (!isset($_GET['seller_username']) || empty($_GET['seller_username'])) {
    echo "No seller information!";
    exit;
  }
$seller_username = $_GET['seller_username'];

// Get the highest_bidder username from the URL parameter
if (!isset($_GET['highest_bidder']) || empty($_GET['highest_bidder'])) {
    echo "No buyer information!";
    exit;
  }
$highest_bidder = $_GET['highest_bidder'];

// Establish who is being reviewed
// review_author will always be $username 
if ($highest_bidder === $username) {
    // If the current user is the buyer, they review the seller
    $reviewed_user = $seller_username;
} elseif ($seller_username === $username) {
    // If the current user is the seller, they review the buyer
    $reviewed_user = $highest_bidder;
} else {
    // If neither condition is met, raise an error
    die("Error: Unable to determine who is being reviewed.");
}

?>

<div class="container">

<!-- Create review form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Submit the review after the sale is completed </h2>
  <div class="card">
    <div class="card-body">
      <form method="post" action="review_result.php">
        <!-- Hidden inputs for auction_id and reviewed_user -->
        <input type="hidden" name="auction_id" value="<?php echo $auction_id; ?>">
        <input type="hidden" name="reviewed_user" value="<?php echo $reviewed_user; ?>">

        <div class="form-group row">
          <label for="review" class="col-sm-2 col-form-label text-right">Review</label>
          <div class="col-sm-10">
            <textarea class="form-control" id="review" name="review" rows="4"></textarea>
            <small id="reviewHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Write a comment about the reviewed user.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="rating" class="col-sm-2 col-form-label text-right">Rating</label>
          <div class="col-sm-10">
            <select class="form-control" id="rating" name="rating" required>
              <option value="" selected>How many starts would you like to give this user...</option>
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="5">5</option>
            </select>
            <small id="ratingHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select rating of the user (1 being the worst; 5 being the best).</small>
          </div>
        </div>
        <button type="submit" class="btn btn-primary form-control">Submit Review</button>
      </form>
    </div>
  </div>
</div>

</div>

<?php include_once("footer.php")?>