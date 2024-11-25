<?php
include_once("header.php");
include_once("connection.php");

// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    if (!headers_sent()) {
        header('Location: login.php'); // Redirect to login if not logged in
        exit();
    } else {
        echo "<script>window.location.href='login.php';</script>";
        exit();
    }
}

// Retrieve the logged-in username
$username = $_SESSION['username'];

// Display success or error messages (if available)
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
             ' . htmlspecialchars($_SESSION['success_message']) . '
             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
             </button>
          </div>';
    unset($_SESSION['success_message']); // Clear the message after displaying
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
             ' . htmlspecialchars($_SESSION['error_message']) . '
             <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
             </button>
          </div>';
    unset($_SESSION['error_message']); // Clear the message after displaying
}

// Include unread notifications count
$unread_count = 0;
$unread_count_sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE username = ? AND is_read = FALSE";
$stmt_unread = $conn->prepare($unread_count_sql);
$stmt_unread->bind_param("s", $username);
$stmt_unread->execute();
$stmt_unread->bind_result($unread_count);
$stmt_unread->fetch();
$stmt_unread->close();

// Pagination setup
$notifications_per_page = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $notifications_per_page;

// Notification Categories (matching the types used in your database)
$categories = ['all', 'bidding', 'auction', 'watchlist'];
$current_category = isset($_GET['category']) && in_array($_GET['category'], $categories) ? $_GET['category'] : 'all';

// Spinner loader
echo '<div id="loading-spinner" class="spinner-border text-primary" role="status" style="display:none;">
        <span class="sr-only">Loading...</span>
      </div>';
?>

<div class="container mt-5">
    <h3>Your Notifications</h3>
    
    <!-- Notification Categories Tabs -->
    <ul class="nav nav-tabs mt-4" id="notificationTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_category == 'all') ? 'active' : ''; ?>" href="?category=all">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_category == 'bidding') ? 'active' : ''; ?>" href="?category=bidding">Bidding Updates</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_category == 'auction') ? 'active' : ''; ?>" href="?category=auction">Auction Updates</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_category == 'watchlist') ? 'active' : ''; ?>" href="?category=watchlist">Watchlist Updates</a>
        </li>
    </ul>
    
    <div class="list-group mt-4">
        <?php
        // Fetch notifications for the logged-in user with pagination
        $notifications_sql = "SELECT notification_id, auction_id, message, type, is_read, created_at 
                              FROM notifications 
                              WHERE username = ? ";
        if ($current_category != 'all') {
            $notifications_sql .= " AND type = ? ";
        }
        $notifications_sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($notifications_sql);
        if ($current_category == 'all') {
            $stmt->bind_param("sii", $username, $notifications_per_page, $offset);
        } else {
            $stmt->bind_param("ssii", $username, $current_category, $notifications_per_page, $offset);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $notification_id = $row['notification_id'];
                $auction_id = $row['auction_id'];
                $message = $row['message'];
                $is_read = $row['is_read'];
                $created_at = date('j M Y, H:i', strtotime($row['created_at']));

                // Construct listing link if auction_id is present
                $listing_link = $auction_id ? '<a href="listing.php?auction_id=' . $auction_id . '">View Listing</a>' : '';

                // Style for unread notifications
                $unread_class = !$is_read ? 'list-group-item-primary' : '';

                // Notification item with JavaScript for marking as read
                echo '<div class="list-group-item ' . $unread_class . '" id="notification-' . $notification_id . '">
                        <p>' . htmlspecialchars($message) . '</p>
                        <small class="text-muted">Received on ' . $created_at . '</small><br>' .
                        $listing_link . 
                        '<button onclick="markAsRead(' . $notification_id . ', this)" class="btn btn-link btn-sm ml-3">Mark as read</button>
                      </div>';
            }
        } else {
            echo '<p class="text-muted">You have no notifications at this time.</p>';
        }

        $stmt->close();
        ?>
    </div>
    
    <!-- Pagination Links -->
    <nav aria-label="Notification pagination">
        <ul class="pagination mt-4">
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?category=<?php echo $current_category; ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
            </li>
            <li class="page-item <?php if ($result->num_rows < $notifications_per_page) echo 'disabled'; ?>">
                <a class="page-link" href="?category=<?php echo $current_category; ?>&page=<?php echo $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>

    <!-- Mark All as Read Button -->
    <div class="mt-3">
        <form action="mark_as_read.php" method="post" class="mt-3">
            <button type="submit" class="btn btn-primary">Mark All as Read</button>
        </form>
    </div>
</div>

<script>
    function markAsRead(notificationId, element) {
        // Show the spinner before making the AJAX request
        $('#loading-spinner').show();

        // Send an AJAX request to mark the notification as read
        $.ajax({
            url: 'mark_as_read.php',
            type: 'POST',
            data: { notification_id: notificationId },
            success: function(response) {
                if (response.trim() === 'success') {
                    $(element).closest('.list-group-item').removeClass('list-group-item-primary');
                } else {
                    console.error('Failed to mark as read');
                }
                // Hide the spinner after processing
                $('#loading-spinner').hide();
            },
            error: function() {
                console.error('Error during the AJAX request');
                // Hide the spinner even if there is an error
                $('#loading-spinner').hide();
            }
        });
    }

    // Set an interval to update the unread count every 30 seconds
    setInterval(updateUnreadCount, 30000);

    function updateUnreadCount() {
    // Show the spinner before making the AJAX request
    $('#loading-spinner').show();

    $.ajax({
        url: 'fetch_unread_count.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.unread_count !== undefined) {
                let unreadCount = data.unread_count;
                let badgeElem = $('#unread-count');

                if (unreadCount > 0) {
                    badgeElem.text(unreadCount);
                    badgeElem.show();
                } else {
                    badgeElem.hide();
                }
            }
            // Hide the spinner after the request completes successfully
            $('#loading-spinner').hide();
        },
        error: function() {
            console.error('Error fetching unread notifications count.');
            // Hide the spinner even if there is an error
            $('#loading-spinner').hide();
        }
    });
}
</script>

<?php include_once("footer.php"); ?>
