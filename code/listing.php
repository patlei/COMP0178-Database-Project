<?php
include_once("header.php");
include_once("connection.php");
require("utilities.php");


// Start the session
session_start(); #Can this be deleted???? warning says ignoring as session already exists

// Get auction_id
if (isset($_GET['auction_id']) && is_numeric($_GET['auction_id'])) {
    $item_id = $_GET['auction_id'];
} else {
    // Handle invalid or missing item_id
    die("Invalid item ID.");
}

// Prepare and execute the query to get auction details
$query = "SELECT item_name, item_description, starting_price, end_date FROM auction WHERE auction_id = ?";
$stmt = $conn->prepare($query);

// Check if the query preparation is successful
if (!$stmt) {
    die('Query preparation failed: ' . $conn->error);
}

$stmt->bind_param("i", $item_id);
$stmt->execute();

// Check if the query execution is successful
if ($stmt->errno) {
    die('Query execution failed: ' . $stmt->error);
}

// Bind results to variables
$stmt->bind_result($title, $description, $starting_price, $end_time);

// Fetch the result
$stmt->fetch();

$stmt->close(); // Always close the prepared statement


// Query to get the highest bid for the item
$query = "SELECT MAX(bid_amount) FROM bids WHERE auction_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$stmt->bind_result($current_price);
$stmt->fetch();
$stmt->close();

// If there are no bids, set the starting price as the current bid
if ($current_price === null) {
    $current_price = $starting_price;
}
#add number of bids

  // TODO: Note: Auctions that have ended may pull a different set of data,
  //       like whether the auction ended in a sale or was cancelled due
  //       to lack of high-enough bids. Or maybe not. --> TO BE DONE STILL
  
  // Calculate time to auction end:
  $now = new DateTime();
  
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

<div class="row"> <!-- Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->

    <div class="itemDescription">
    <?php echo($description); ?>
    </div>

  </div>

  <div class="col-sm-4"> <!-- Right col with bidding info -->

    <p>
<?php if ($now > $end_time): ?>
     This auction ended <?php echo(date_format($end_time, 'j M H:i')) ?>
     <!-- TODO: Print the result of the auction here? -->
<?php else: ?>
     Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>  
    <p class="lead">Current bid: £<?php echo(number_format($current_price, 2)) ?></p>

    <!-- Bidding form -->
    <form method="POST" action="place_bid.php">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">£</span>
        </div>
	    <input type="number" class="form-control" id="bid">
      </div>
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
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func
</script>