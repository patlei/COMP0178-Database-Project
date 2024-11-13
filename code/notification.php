<?php include_once("header.php") ?>
<?php require("utilities.php") ?>


<div class="container">

<h2 class="my-3">My Notification Box</h2>

<?php
// Check if there is a notification in the session
$notification = isset($_SESSION['notification']) ? $_SESSION['notification'] : null;

// Display the notification if it exists and then clear it from the session
if ($notification):
?>

  <div class="alert alert-success mt-4">
    <strong><?php echo htmlspecialchars($notification['message']); ?></strong><br>
    Auction ID: <?php echo htmlspecialchars($notification['auction_id']); ?><br>
    Title: <?php echo htmlspecialchars($notification['title']); ?><br>
    Starting Price: £<?php echo htmlspecialchars($notification['starting_price']); ?><br>
    End Date: <?php echo htmlspecialchars($notification['end_date']); ?>
  </div>

<?php
// Clear the notification from the session to avoid duplicate messages
unset($_SESSION['notification']);
else:
?>

  <p>No new notifications.</p>

<?php endif; ?>

</div>

<?php include_once("footer.php") ?>
