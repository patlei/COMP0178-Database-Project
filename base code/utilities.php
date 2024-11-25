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


// Function to update auction status based on the current date and time
function update_auction_status($conn) {
  // Set PHP's default timezone to UTC
  date_default_timezone_set('UTC');

  // Get current date and time
  $current_time = date('Y-m-d H:i:s');

  // Step 1: Find auctions that have ended but are still marked as 'active'
  $auction_end_query = "SELECT auction_id, username FROM auction WHERE end_date < ? AND auction_status = 'active'";
  $auction_end_stmt = $conn->prepare($auction_end_query);
  if ($auction_end_stmt) {
      $auction_end_stmt->bind_param("s", $current_time);
      $auction_end_stmt->execute();
      $result = $auction_end_stmt->get_result();

      // Loop through each auction that needs to be closed
      while ($row = $result->fetch_assoc()) {
          $auction_id = $row['auction_id'];
          $seller_username = $row['username'];

          // Step 2: Update the auction status to 'closed'
          $update_status_query = "UPDATE auction SET auction_status = 'closed' WHERE auction_id = ?";
          $update_status_stmt = $conn->prepare($update_status_query);
          if ($update_status_stmt) {
              $update_status_stmt->bind_param("i", $auction_id);
              $update_status_stmt->execute();
              $update_status_stmt->close();
          }

          // Step 3: Find the highest bidder for the auction
          $highest_bid_query = "SELECT username, bid_amount FROM bids WHERE auction_id = ? ORDER BY bid_amount DESC LIMIT 1";
          $highest_bid_stmt = $conn->prepare($highest_bid_query);
          if ($highest_bid_stmt) {
              $highest_bid_stmt->bind_param("i", $auction_id);
              $highest_bid_stmt->execute();
              $highest_bid_stmt->bind_result($highest_bidder, $winning_bid);

              if ($highest_bid_stmt->fetch()) {
                  // Step 4: Notify the highest bidder
                  $winner_message = "Congratulations! You have won the auction for auction ID: " . $auction_id . " with a bid of £" . number_format($winning_bid, 2);
                  $winner_type = 'auction';
                  $winner_notification_query = "INSERT INTO notifications (username, auction_id, message, type, is_read) VALUES (?, ?, ?, ?, FALSE)";
                  $winner_notification_stmt = $conn->prepare($winner_notification_query);
                  if ($winner_notification_stmt) {
                      $winner_notification_stmt->bind_param("siss", $highest_bidder, $auction_id, $winner_message, $winner_type);
                      $winner_notification_stmt->execute();
                      $winner_notification_stmt->close();
                  }
              }

              // Step 5: Notify the seller that the auction has ended
              $seller_message = "Your auction (ID: " . $auction_id . ") has ended.";
              if (isset($highest_bidder)) {
                  $seller_message .= " The winning bidder is " . $highest_bidder . " with a bid of £" . number_format($winning_bid, 2) . ".";
              }
              $seller_type = 'auction';
              $seller_notification_query = "INSERT INTO notifications (username, auction_id, message, type, is_read) VALUES (?, ?, ?, ?, FALSE)";
              $seller_notification_stmt = $conn->prepare($seller_notification_query);
              if ($seller_notification_stmt) {
                  $seller_notification_stmt->bind_param("siss", $seller_username, $auction_id, $seller_message, $seller_type);
                  $seller_notification_stmt->execute();
                  $seller_notification_stmt->close();
              }

              $highest_bid_stmt->close();
          }
      }

      $auction_end_stmt->close();
  } else {
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
