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

  // Query to check all auctions where the end date has passed and status is still 'active'
  $query = "UPDATE auction SET auction_status = 'closed' WHERE end_date < ? AND auction_status = 'active'";

  // Prepare statement
  $stmt = $conn->prepare($query);
  if ($stmt) {
      // Bind current time as parameter
      $stmt->bind_param("s", $current_time);

      // Execute the query
      $stmt->execute();
      
      // Close the statement
      $stmt->close();
  } else {
      echo "Error updating auction status: " . $conn->error;
  }
}

?>