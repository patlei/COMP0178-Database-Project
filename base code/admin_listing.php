<?php
include_once("header_admin.php");
include_once("connection.php");
require_once("utilities.php");
date_default_timezone_set('UTC'); // Set default timezone

// Validate and fetch the auction ID from the GET request
if (!isset($_GET['auction_id']) || empty($_GET['auction_id']) || !is_numeric($_GET['auction_id'])) {
    echo "Invalid auction selected!";
    exit;
}

$auction_id = intval($_GET['auction_id']);

// Fetch auction details
$auction_sql = "SELECT a.item_name, a.item_description, a.username, a.starting_price, 
                       a.reserve_price, a.end_date, a.auction_status, a.image_path, 
                       a.item_condition, a.views, c.category_name, s.size, 
                       m.material, co.color
                FROM auction a
                LEFT JOIN categories c ON a.category_id = c.category_id
                LEFT JOIN sizes s ON a.size_id = s.size_id
                LEFT JOIN materials m ON a.material_id = m.material_id
                LEFT JOIN colors co ON a.color_id = co.color_id
                WHERE a.auction_id = ?";

$stmt = $conn->prepare($auction_sql);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Auction not found!";
    exit;
}

$auction = $result->fetch_assoc(); // Fetch auction data

// Extract auction details
$title = $auction['item_name'];
$description = $auction['item_description'];
$seller_username = $auction['username'];
$category = $auction['category_name'] ?? 'Not specified';
$size = $auction['size'] ?? 'Not specified';
$material = $auction['material'] ?? 'Not specified';
$color = $auction['color'] ?? 'Not specified';
$condition = $auction['item_condition'];
$views = $auction['views'];
$starting_price = $auction['starting_price'];
$reserve_price = $auction['reserve_price'];
$end_time = new DateTime($auction['end_date']);
$auction_status = $auction['auction_status'];
$image_path = isset($auction['image_path']) ? htmlspecialchars($auction['image_path']) : null;
            
// Check if image path is valid, else use placeholder image
$image_src = (!empty($image_path) && file_exists($image_path)) ? $image_path : './images/default-placeholder.png';
            

// Fetch bid records
$bids_sql = "SELECT b.username, b.bid_amount, b.bid_time 
             FROM bids b 
             WHERE b.auction_id = ? 
             ORDER BY b.bid_time DESC";
$bids_stmt = $conn->prepare($bids_sql);
$bids_stmt->bind_param("i", $auction_id);
$bids_stmt->execute();
$bids_result = $bids_stmt->get_result();
?>


    <div class="container mt-5">
    <h2>View Auction Details</h2>

    <!-- Auction Details -->
    <div class="card mb-4">
        <div class="row no-gutters">
            <div class="col-md-4">
                <img src="<?php echo $image_src; ?>" class="card-img" alt="Auction Item Image">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($title); ?></h5>
                    <p class="card-text"><strong>Auction ID:</strong> <?php echo htmlspecialchars($auction_id); ?></p>
                    <p class="card-text"><strong>Description:</strong> <?php echo htmlspecialchars($description); ?></p>
                    <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($category); ?></p>
                    <p class="card-text"><strong>Size:</strong> <?php echo htmlspecialchars($size); ?></p>
                    <p class="card-text"><strong>Material:</strong> <?php echo htmlspecialchars($material); ?></p>
                    <p class="card-text"><strong>Color:</strong> <?php echo htmlspecialchars($color); ?></p>
                    <p class="card-text"><strong>Condition:</strong> <?php echo htmlspecialchars($condition); ?></p>
                    <p class="card-text"><strong>Views:</strong> <?php echo htmlspecialchars($views); ?></p>
                    <p class="card-text"><strong>Starting Price:</strong> £<?php echo number_format($starting_price, 2); ?></p>
                    <p class="card-text"><strong>Reserve Price:</strong> £<?php echo number_format($reserve_price, 2); ?></p>
                    <p class="card-text"><strong>End Date:</strong> <?php echo htmlspecialchars($end_time->format('d M Y H:i')); ?></p>
                    <p class="card-text"><strong>Posted By:</strong> <?php echo htmlspecialchars($seller_username); ?></p>
                </div>
            </div>
        </div>
    </div>


    <!-- Bid Records -->
    <div class="card">
        <div class="card-header">
            <h4>Bid Records</h4>
        </div>
        <div class="card-body">
            <?php if ($bids_result->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Bid Amount</th>
                            <th>Bid Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($bid = $bids_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bid['username']); ?></td>
                                <td>£<?php echo number_format($bid['bid_amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($bid['bid_time']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No bids have been placed for this auction.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once("footer.php"); ?>
