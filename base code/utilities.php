<?php

// display_time_remaining:
// Helper function to help figure out what time to display
function display_time_remaining($interval) {
    // Set PHP's default timezone to UTC
    date_default_timezone_set('UTC');
  
    if ($interval->days == 0 && $interval->h == 0) {
      // Less than one hour remaining: print mins + seconds:
      $time_remaining = $interval->format('%im %Ss');
    }
    else if ($interval->days == 0) {
      // Less than one day remaining: print hrs + mins:
      $time_remaining = $interval->format('%hh %im');
    }
    else {
      // At least one day remaining: print days + hrs:
      $time_remaining = $interval->format('%ad %hh');
    }

  return $time_remaining;

}

// print_listing_li:
// This function prints an HTML <li> element containing an auction listing
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  }
  else {
    $desc_shortened = $desc;
  }
  
  // Fix language of bid vs. bids
  if ($num_bids == 1) {
    $bid = ' bid';
  }
  else {
    $bid = ' bids';
  }
  
  // Calculate time to auction end
  $now = new DateTime();
  if ($now > $end_time) {
    $time_remaining = 'This auction has ended';
  }
  else {
    // Get interval:
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($time_to_end) . ' remaining';
  }
  
  // Print HTML
  echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5"><h5><a href="listing.php?auction_id=' . $item_id . '">' . $title . '</a></h5>' . $desc_shortened . '</div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' . $num_bids . $bid . '<br/>' . $time_remaining . '</div>
  </li>'
  );
}


// Function to update auction status and notify sellers and buyers
function update_auction_status($conn) {
  // Set PHP's default timezone to UTC
  date_default_timezone_set('UTC');

  // Get current date and time
  $current_time = date('Y-m-d H:i:s');

  // Query to select auctions that are still active but have expired
  $query = "SELECT auction.auction_id, auction.end_date, auction.username, auction.reserve_price, highest_bids.highest_bid, highest_bids.last_bidder 
            FROM auction 
            LEFT JOIN highest_bids ON auction.auction_id = highest_bids.auction_id 
            WHERE auction.end_date < ? AND auction.auction_status = 'active'";

  // Prepare the query
  $stmt = $conn->prepare($query);
  if ($stmt) {
      // Bind current time as parameter
      $stmt->bind_param("s", $current_time);
      // Execute the query
      $stmt->execute();
      $result = $stmt->get_result();

      // Iterate through the result set
      while ($row = $result->fetch_assoc()) {
          // Retrieve auction details
          $auction_id = $row['auction_id'];
          $username = $row['username'];
          $reserve_price = $row['reserve_price'];
          $highest_bid = $row['highest_bid'];
          $last_bidder = $row['last_bidder'];

          // Determine auction outcome and prepare notification message for seller
          if ($highest_bid === null) {
              // No bids were placed
              $message = "Your auction for Auction ID $auction_id ended without any bids being placed.";
          } else if ($highest_bid < $reserve_price) {
              // Reserve price not met
              $message = "Your auction for Auction ID $auction_id ended with bids, but the reserve price of £" . number_format($reserve_price, 2) . " was not met.";
          } else {
              // Successful sale
              $message = "Congratulations! Your auction for Auction ID $auction_id ended successfully with the highest bid of £" . number_format($highest_bid, 2) . " to User: $last_bidder.";

              // Notify the buyer that they have won the auction
              $buyer_message = "Congratulations! You won the auction for Auction ID $auction_id with a bid of £" . number_format($highest_bid, 2) . ".";
              $buyer_notification_query = "INSERT INTO notifications (username, auction_id, message, type) VALUES (?, ?, ?, 'bidding')";
              $buyer_notification_stmt = $conn->prepare($buyer_notification_query);
              if ($buyer_notification_stmt) {
                  $buyer_notification_stmt->bind_param("sis", $last_bidder, $auction_id, $buyer_message);
                  $buyer_notification_stmt->execute();
                  $buyer_notification_stmt->close();
              }
          }

          // Insert the notification for the seller
          $notification_query = "INSERT INTO notifications (username, auction_id, message, type) VALUES (?, ?, ?, 'auction')";
          $notification_stmt = $conn->prepare($notification_query);
          if ($notification_stmt) {
              $notification_stmt->bind_param("sis", $username, $auction_id, $message);
              $notification_stmt->execute();
              $notification_stmt->close();
          }

          // Update the auction status to 'closed'
          $update_query = "UPDATE auction SET auction_status = 'closed' WHERE auction_id = ?";
          $update_stmt = $conn->prepare($update_query);
          if ($update_stmt) {
              $update_stmt->bind_param("i", $auction_id);
              $update_stmt->execute();
              $update_stmt->close();
          }
      }

      // Close the original statement
      $stmt->close();
  } else {
      // Log an error if the query preparation failed
      echo "Error updating auction status: " . $conn->error;
  }
}

// Function to update watchlist notifications
function update_watchlist_notifications($conn) {
  // Set the timezone
  date_default_timezone_set('UTC');
  // Get the current time
  $current_time = date('Y-m-d H:i:s');

  // Notify users of auctions in their watchlist that are ending soon
  // Example: Notify if an auction is ending in the next hour
  $end_soon_query = "SELECT a.auction_id, a.end_date, w.username
                     FROM auction a
                     JOIN watchlist w ON a.auction_id = w.auction_id
                     WHERE a.end_date > ? AND a.end_date <= DATE_ADD(?, INTERVAL 1 HOUR)
                     AND a.auction_status = 'active'";

  $stmt = $conn->prepare($end_soon_query);
  if ($stmt) {
      $stmt->bind_param("ss", $current_time, $current_time);
      $stmt->execute();
      $result = $stmt->get_result();

      while ($row = $result->fetch_assoc()) {
          $auction_id = $row['auction_id'];
          $username = $row['username'];

          // Create a notification for the user
          $message = "Auction ID: " . $auction_id . " is ending soon. Place your bid before it's too late!";
          $type = 'watchlist_ending';

          $notification_query = "INSERT INTO notifications (username, auction_id, message, type, is_read)
                                 VALUES (?, ?, ?, ?, FALSE)";
          $notification_stmt = $conn->prepare($notification_query);
          if ($notification_stmt) {
              $notification_stmt->bind_param("siss", $username, $auction_id, $message, $type);
              $notification_stmt->execute();
              $notification_stmt->close();
          }
      }

      // Close the statement
      $stmt->close();
  } else {
      error_log("Failed to prepare the watchlist notification query: " . $conn->error);
  }
}
?>
