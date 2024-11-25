<?php
include_once("header.php");
include_once("connection.php");
require_once("utilities.php");
include_once("config.php");
// Set PHP's default timezone to UTC
date_default_timezone_set('UTC');

// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Alert Notification 
// Check if there is a success message for placing a bid
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
             ' . htmlspecialchars($_SESSION['success_message']) . '
             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
             </button>
          </div>';
    unset($_SESSION['success_message']); // Clear the message
}

// Check if there is an outbid notification to display
if (isset($_SESSION['outbid_message'])) {
    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
             ' . htmlspecialchars($_SESSION['outbid_message']) . '
             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
             </button>
          </div>';
    unset($_SESSION['outbid_message']); // Clear the message
}

// Check if there is an auction won message
if (isset($_SESSION['auction_won_message'])) {
    echo '<div class="alert alert-info alert-dismissible fade show" role="alert">
             ' . htmlspecialchars($_SESSION['auction_won_message']) . '
             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
             </button>
          </div>';
    unset($_SESSION['auction_won_message']); // Clear the message
}
// End of Alerts Code Block

// Get the auction ID from the URL parameter
if (!isset($_GET['auction_id']) || empty($_GET['auction_id']) || !is_numeric($_GET['auction_id'])) {
  echo "No auction selected!";
  exit;
}

$auction_id = intval($_GET['auction_id']);

// Update the views count for the given auction item
$update_views_sql = "UPDATE auction SET views = views + 1 WHERE auction_id = ?";
$stmt = $conn->prepare($update_views_sql);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$stmt->close();

// Ensure that a user is logged in before logging the user view
if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];

  // Check if this auction has already been viewed by this user
  $check_view_sql = "SELECT view_count FROM user_views WHERE username = ? AND auction_id = ?";
  $stmt = $conn->prepare($check_view_sql);
  $stmt->bind_param("si", $username, $auction_id);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
      // User has already viewed this listing, update the view count
      $update_view_sql = "UPDATE user_views SET view_count = view_count + 1 WHERE username = ? AND auction_id = ?";
      $update_stmt = $conn->prepare($update_view_sql);
      $update_stmt->bind_param("si", $username, $auction_id);
      $update_stmt->execute();
      $update_stmt->close();
  } else {
      // First time user views this listing, insert into user_views
      $insert_view_sql = "INSERT INTO user_views (username, auction_id, view_count) VALUES (?, ?, 1)";
      $insert_stmt = $conn->prepare($insert_view_sql);
      $insert_stmt->bind_param("si", $username, $auction_id);
      $insert_stmt->execute();
      $insert_stmt->close();
  }
  $stmt->close(); // Close the original view check statement
}

// Query to fetch the auction details along with category, size, material, color, condition, and views
$sql = "SELECT a.item_name, a.item_description, a.username, a.starting_price, a.reserve_price, a.end_date, a.auction_status,
               a.image_path, a.item_condition, a.views,
               c.category_name, s.size, m.material, co.color
        FROM auction a
        LEFT JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN sizes s ON a.size_id = s.size_id
        LEFT JOIN materials m ON a.material_id = m.material_id
        LEFT JOIN colors co ON a.color_id = co.color_id
        WHERE a.auction_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Auction not found!";
    exit;
}

$auction = $result->fetch_assoc();  // Fetch the auction data here

// Extract auction details and associated values with default handling if missing
$title = $auction['item_name'];
$description = $auction['item_description'];
$category = !empty($auction['category_name']) ? $auction['category_name'] : 'Not specified';
$size = !empty($auction['size']) ? $auction['size'] : 'Not specified';
$material = !empty($auction['material']) ? $auction['material'] : 'Not specified';
$color = !empty($auction['color']) ? $auction['color'] : 'Not specified';
$condition = $auction['item_condition'];
$views = $auction['views'];
$starting_price = $auction['starting_price'];
$reserve_price = $auction['reserve_price'];
$end_time = $auction['end_date'];
$auction_status = $auction['auction_status'];
$image_path = IMAGE_BASE_PATH . $auction['image_path'];
$seller_username = $auction['username'];

$stmt->close();

// Query to get the highest bid and the username of the highest bidder
$query = "SELECT bid_amount, username FROM bids WHERE auction_id = ? ORDER BY bid_amount DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $auction_id);
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
$stmt_bids->bind_param("i", $auction_id);
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
    $stmt->bind_param("si", $username, $auction_id); 
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


<?php 
//'Auctions by the same user'
// Construct SQL query with conditions and dynamic values
$sql = "SELECT  a.auction_id, a.item_name, a.item_description, 
               IFNULL(hb.highest_bid, a.starting_price) AS current_price, 
               a.category_id, a.end_date, a.image_path,
               a.item_condition, m.material, c.color, s.size, a.views 
        FROM auction a
        LEFT JOIN highest_bids hb ON a.auction_id = hb.auction_id
        LEFT JOIN materials m ON a.material_id = m.material_id
        LEFT JOIN colors c ON a.color_id = c.color_id
        LEFT JOIN sizes s ON a.size_id = s.size_id
        WHERE a.auction_status = 'active'
        AND a.auction_id IN (
          SELECT a2.auction_id
          FROM auction a2
          WHERE username = '$seller_username'
        )
        AND a.auction_id != $auction_id 
        LIMIT 10";
      

        
// Execute the SQL query
$result2 = $conn->query($sql);

// Check if the query execution resulted in an error
if (!$result2) {
    echo "<div class='alert alert-danger'>Oops! Something went wrong while fetching the results. Please try again later.</div>";
}
?>


<?php

// Register the sale in the database
if ($auction_status === 'closed' && $num_bids > 0) {
  // Check if auction_id is in table sales
  $check_sale = "SELECT COUNT(*) FROM sales WHERE auction_id = ?";
  $stmt = $conn->prepare($check_sale);
  if ($stmt) {
      // Bind parameter
      $stmt->bind_param("i", $auction_id);
      
      // Execute the query
      $stmt->execute();
      $stmt->bind_result($count);
      $stmt->fetch();
      $stmt->close();

      if ($count == 0) {
          // if auction_id not in sales yet, proceed to insert
          $add_sale = "INSERT INTO sales (auction_id, seller_username, buyer_username, sale_price)
                      VALUES (?, ?, ?, ?)";
          $stmt = $conn->prepare($add_sale);
          if ($stmt) {
              // Bind parameters
              $stmt->bind_param("iiss", $auction_id, $seller_username, $highest_bidder, $current_price);

              // Execute the statement
              if ($stmt->execute()) {
                  echo "Sale successfully registered!";
              } else {
                  echo "Error inserting data: " . $stmt->error;
              }

              // Close the prepared statement
              $stmt->close();
          } else {
              echo "Error preparing statement: " . $conn->error;
          }
      } // Don't need to inform the user that the sale has been registered previously
  } else {
      echo "Error preparing check statement: " . $conn->error;
  }
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
        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Auction Item Image" class="img-fluid">

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
        Sold for £<?php echo number_format($current_price, 2); ?> to User: <?php echo($highest_bidder)?>
        <!--Display review form button for seller and buyer -->
        <?php // Check if the user has already submitted a review for this auction
        $query_review_check = "SELECT COUNT(*) FROM review WHERE auction_id = ? AND review_author = ?";
        $stmt_review_check = $conn->prepare($query_review_check);
        $stmt_review_check->bind_param("is", $auction_id, $username); // 'i' for integer, 's' for string
        $stmt_review_check->execute();
        $stmt_review_check->bind_result($review_count);
        $stmt_review_check->fetch();
        $stmt_review_check->close();
        // If review_count > 0, user has already submitted a review for this auction
        $has_reviewed = ($review_count > 0);
        ?>
        <?php if ($username === $seller_username || $username === $highest_bidder): ?>
          <?php if (!$has_reviewed): // Only show the review button if the user hasn't reviewed ?>
            <div style="margin-top: 10px;">
              <a href="review.php?auction_id=<?php echo $auction_id; ?>&seller_username=<?php echo urlencode($seller_username); ?>&highest_bidder=<?php echo urlencode($highest_bidder); ?>" class="btn btn-primary">Add Review</a>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php
        // Register the sale in the database
        if ($auction_status === 'closed' && $num_bids > 0) {
          // Check if auction_id is in table sales
          $check_sale = "SELECT COUNT(*) FROM sales WHERE auction_id = ?";
          $stmt = $conn->prepare($check_sale);
          if ($stmt) {
              // Bind parameter
              $stmt->bind_param("d", $auction_id);
      
              // Execute the query
              $stmt->execute();
              $stmt->bind_result($count);
              $stmt->fetch();
              $stmt->close();

              if ($count == 0) {
                // if auction_id not in sales yet, proceed to insert
                  $add_sale = "INSERT INTO sales (auction_id, seller_username, buyer_username, sale_price)
                                VALUES (?, ?, ?, ?)";
                  $stmt = $conn->prepare($add_sale);
                  if ($stmt) {
                      // Bind parameters
                      $stmt->bind_param("dssd", $auction_id, $seller_username, $highest_bidder, $current_price);

                      // Execute the statement
                      if ($stmt->execute()) {
                          echo "Sale successfully registered!";
                      } else {
                          echo "Error inserting data: " . $stmt->error;
                      }

                      // Close the prepared statement
                      $stmt->close();
                    } else {
                      echo "Error preparing statement: " . $conn->error;
                  }
                  // Execute the SQL query to insert the sale
                  $sale = $conn->query($addsale);
                  // Check if the query execution resulted in an error
                  if (!$sale) {
                    echo "<div class='alert alert-danger'>Oops! Something went wrong while fetching the results. Please try again later.</div>";
                  };
              } // Don't need to inform the user that the sale has been registered previously
          } else {
              echo "Error preparing check statement: " . $conn->error;
          }
        } endif
        ?>
<?php else: ?>
     Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></>  
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

    <!-- Auctions by the same user -->
<div class="container mt-5 sameuser-listings">
<h3>Other auctions by this user:</h3>
<div class="scrolling-wrapper row flex-row flex-nowrap mt-4 pb-4 pt-2">
    <?php if ($result2->num_rows == 0): ?>
      <p>No results found... Try again with different keywords or filters.</p>
  <?php else: ?>
      <div class="row">
          <?php
          // Loop through each result and display it using the utility function
          while ($row = $result2->fetch_assoc()) {
            $image_path = $row['image_path'];
            // Construct full image path
            $full_image_path = IMAGE_BASE_PATH . $image_path;
            echo '<div class="col-3">
                    <div class="card h-100">
                        <img src="' . $full_image_path . '" class="card-img-top" alt="' . htmlspecialchars($row['item_name']) . '">
                        <div class="card-body">
                            <h5 class="card-title">' . htmlspecialchars($row['item_name']) . '</h5>
                            <p class="card-text"><strong>Current Price: £' . number_format($row['current_price'], 2) . '</strong></p>
                            <p class="text-muted">Views: ' . number_format($row['views']) . '</p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="listing.php?auction_id=' . $row['auction_id'] . '" class="btn btn-primary">View Listing</a>
                        </div>
                    </div>
                </div>';}
            ?>
        </div>
  <?php endif; ?>
</div>

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
    data: { functionname: 'add_to_watchlist', arguments: <?php echo json_encode($auction_id); ?> },
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
    data: { functionname: 'remove_from_watchlist', arguments: <?php echo json_encode($auction_id); ?> },
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
