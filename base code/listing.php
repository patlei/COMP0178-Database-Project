<?php
include_once("header.php");
include_once("connection.php");
require_once("utilities.php");
// Set PHP's default timezone to UTC
date_default_timezone_set('UTC');

// // Start the session
session_start(); 

// // Check if the user is logged in
// if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
//   header('Location: login.php'); // Redirect to login if not logged in
//   exit;
// }

// Check if there is a success message and display it
if (isset($_SESSION['success_message'])) {
  echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
  // Unset the success message so it doesn't show again on page refresh
  unset($_SESSION['success_message']);
}

// Optionally, display any error messages
if (isset($_SESSION['error_message'])) {
  echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
  unset($_SESSION['error_message']);
}


// Get the auction ID from the URL parameter
if (!isset($_GET['auction_id'])) {
  echo "No auction selected!";
  exit;
}

$item_id = $_GET['auction_id'];

// Query to fetch the auction details along with category, size, material, color, condition, and views
$sql = "SELECT a.item_name, a.item_description, a.category_id, a.starting_price, a.reserve_price, a.end_date, a.auction_status,
               a.image_path, a.material_id, a.item_condition, a.color_id, a.size_id, a.views,
               c.category_name, s.size, m.material, co.color
        FROM auction a
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN sizes s ON a.size_id = s.size_id
        LEFT JOIN materials m ON a.material_id = m.material_id
        LEFT JOIN colors co ON a.color_id = co.color_id
        WHERE a.auction_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Auction not found!";
    exit;
}

$auction = $result->fetch_assoc();

// Extract auction details and associated values
$title = $auction['item_name'];
$description = $auction['item_description'];
$category = $auction['category_name'];
$size = $auction['size'];
$material = $auction['material'];
$color = $auction['color'];
$condition = $auction['item_condition'];
$views = $auction['views'];
$starting_price = $auction['starting_price'];
$reserve_price = $auction['reserve_price'];
$end_time = $auction['end_date'];
$auction_status = $auction['auction_status'];
$image_path = $auction['image_path'];

$stmt->close();

// Query to get the highest bid and the username of the highest bidder
$query = "SELECT bid_amount, username FROM bids WHERE auction_id = ? ORDER BY bid_amount DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$stmt->bind_result($current_price, $highest_bidder);
$stmt->fetch();
$stmt->close();



// If there are no bids, set the current price as the starting price
if ($current_price === null) {
    $current_price = $starting_price;
}
// Query to count the number of bids for this auction
$query_bids = "SELECT COUNT(*) FROM bids WHERE auction_id = ?";
$stmt_bids = $conn->prepare($query_bids);
$stmt_bids->bind_param("i", $item_id);
$stmt_bids->execute();
$stmt_bids->bind_result($num_bids);
$stmt_bids->fetch();
$stmt_bids->close();

 
// Calculate time to auction end:
$now = new DateTime();
$end_time = new DateTime($end_time); // Convert $end_time to DateTime object
if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
} 



// Check if the user is logged in 
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username']; // Get the username from the session
    $has_session = true;

   // Query to check if the user is watching this auction
    $query = "SELECT COUNT(*) FROM watchlist WHERE username = ? AND auction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $username, $item_id); 
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    
    // If the count is greater than 0, the user is watching the item
    if ($count > 0) {
        $watching = true;
    } else {
        $watching = false;
    }

    $stmt->close(); // Close the prepared statement
} else {
    $has_session = false; // If there is no session, the user is not logged in
    $watching = false; // Cannot be watching anything if not logged in
}
?>


<div class="container">

<div class="row"> <!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!-- Left col -->
    <h2 class="my-3"><?php echo($title); ?></h2>
  </div>
  <div class="col-sm-4 align-self-center"> <!-- Right col -->
<?php
  /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>
    <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
    </div>
    <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
    </div>
<?php endif /* Print nothing otherwise */ ?>
  </div>
</div>
<div class="row"> <!-- Row for item image and description -->
    <div class="col-sm-4"> <!-- Left column for image -->
      <?php if (!empty($image_path)): ?>
        <img src="<?php echo($image_path); ?>" alt="Auction Item Image" class="img-fluid">
      <?php else: ?>
        <p>No image available for this auction.</p>
      <?php endif; ?>
    </div>
<div class="row"> <!-- Row #2 with auction information + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->

    <div class="itemDescription">
    <p><strong>Description:</strong> <?php echo($description); ?></p>

    <!-- Item Information -->
    <p><strong>Category:</strong> <?php echo($category); ?></p>
      <p><strong>Size:</strong> <?php echo($size); ?></p>
      <p><strong>Material:</strong> <?php echo($material); ?></p>
      <p><strong>Color:</strong> <?php echo($color); ?></p>
      <p><strong>Condition:</strong> <?php echo($condition); ?></p>
      <p><strong>Views:</strong> <?php echo($views); ?></p>
    </div>

  </div>

  <div class="col-sm-4"> <!-- Right col with bidding info -->

    <p>
<?php if ($now > $end_time): ?>
  <?php 
    // If no bids or reserve price not met, display "ended without a sale"
    if ($num_bids == 0 || ($reserve_price > 0 && $current_price < $reserve_price)): ?>
        <!-- Auction ended without a sale -->
        This auction ended without a sale on <?php echo date_format($end_time, 'j M H:i'); ?>
        <?php if ($num_bids == 0): ?>
            No bids were placed.
        <?php endif; ?>
        <?php if ($reserve_price > 0 && $current_price < $reserve_price): ?>
            The reserve price of £<?php echo number_format($reserve_price, 2); ?> was not met.
        <?php endif; ?>
  <?php else: ?>
        <!-- Auction ended with a sale -->
        This auction ended <?php echo date_format($end_time, 'j M H:i'); ?><br>
        Sold for £<?php echo number_format($current_price, 2); ?>
    <?php endif; ?>
<?php else: ?>
     Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>  
    <p class="lead">Current bid: £<?php echo(number_format($current_price, 2)) ?></p>
    <p><strong>Number of Bids:</strong> <?php echo($num_bids); ?></p>
    <!-- Displaying message for the user if their bid is the highest -->
    <?php if (isset($_SESSION['username']) && $_SESSION['username'] === $highest_bidder): ?>
      <p class="text-success">Highest bid is yours!</p>
    <?php endif; ?>


    <!-- Bidding form -->
    <form method="POST" action="place_bid.php">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">£</span>
        </div>
        <input type="number" class="form-control" id="bid" name="bid" required>
      </div>
      <!-- Hidden auction_id  -->
      <input type="hidden" name="auction_id" value="<?php echo $_GET['auction_id']; ?>">
      <button type="submit" class="btn btn-primary form-control">Place bid</button>
    </form>

<?php endif ?>

  
  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->



<?php include_once("footer.php")?>

<!-- validate the bid if higher than current price before submitting the form -->
<script>
  // JavaScript function to validate the bid
  $("form").submit(function(e) {
      var bidAmount = parseFloat($("#bid").val()); // Get the bid amount entered by the user
      var currentBid = <?php echo($current_price); ?>; // The current bid value fetched from the server

      if (bidAmount <= currentBid) {
          e.preventDefault();  // Prevent the form from submitting
          alert("Your bid must be higher than the current bid.");
      }
  });
</script>

</div> <!-- End of container -->


<script> 
// JavaScript functions: addToWatchlist and removeFromWatchlist.
function addToWatchlist(button) {
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: { functionname: 'add_to_watchlist', arguments: <?php echo json_encode($item_id); ?> },
    success: function(response) {
      var obj = JSON.parse(response);  // Parse the JSON response
      console.log(obj);  // Debugging

      if (obj.status == "success") {
        // Hide the "Add to Watchlist" button and show the "Watching" button
        $("#watch_nowatch").hide(); // Hide the "Add to Watchlist"
        $("#watch_watching").show(); // Show the "Watching" button
      } else {
        alert(obj.message); 
      }
    },
    error: function() {
      alert("An error occurred while adding to the watchlist.");
    }
  });
}


function removeFromWatchlist(button) {
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: { functionname: 'remove_from_watchlist', arguments: <?php echo json_encode($item_id); ?> },
    success: function(response) {
      var obj = JSON.parse(response);  // Parse the JSON response
      console.log(obj);  // Debugging

      if (obj.status == "success") {
        // Hide the "Watching" button and show the "Add to Watchlist" button
        $("#watch_watching").hide(); // Hide the "Watching"
        $("#watch_nowatch").show(); // Show the "Add to Watchlist"
      } else {
        alert(obj.message); 
      }
    },
    error: function() {
      alert("An error occurred while removing from the watchlist.");
    }
  });
}

</script>