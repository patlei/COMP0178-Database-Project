<?php
include_once("header_admin.php"); // Include the admin header for consistent navigation
include_once("connection.php"); // Include your database connection

// Get auction details if editing an existing auction
$auction_id = isset($_GET['auction_id']) ? intval($_GET['auction_id']) : 0;
$auction = null;

if ($auction_id) {
    $sql = "SELECT * FROM auction WHERE auction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $auction = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch categories with `category_section - category_name` format
$categories = [];
$sql = "SELECT category_id, CONCAT(category_section, ' - ', category_name) AS full_category FROM categories";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch materials, colors, and sizes
$materials = $conn->query("SELECT material_id, material FROM materials")->fetch_all(MYSQLI_ASSOC);
$colors = $conn->query("SELECT color_id, color FROM colors")->fetch_all(MYSQLI_ASSOC);
$sizes = $conn->query("SELECT size_id, size FROM sizes")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Auction - Auction ID <?php echo htmlspecialchars($auction['auction_id'] ?? ''); ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Edit Auction - Auction ID <?php echo htmlspecialchars($auction['auction_id'] ?? ''); ?></h2>
    
    <form action="process_edit_auction.php" method="POST">
        <!-- Include auction_id as hidden field to identify the auction -->
        <input type="hidden" name="auction_id" value="<?php echo htmlspecialchars($auction['auction_id'] ?? ''); ?>">

        <!-- Title of Auction -->
        <div class="form-group row">
            <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of auction</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="auctionTitle" name="item_name" value="<?php echo htmlspecialchars($auction['item_name'] ?? ''); ?>" placeholder="e.g. Black mountain bike">
            </div>
        </div>

        <!-- Item Description -->
        <div class="form-group row">
            <label for="itemDescription" class="col-sm-2 col-form-label text-right">Item Description</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="auctionTitle" name="item_name" value="<?php echo htmlspecialchars($auction['item_description'] ?? ''); ?>" placeholder="e.g. Black mountain bike">
            </div>
        </div>

        <!-- Category -->
        <div class="form-group row">
            <label for="category" class="col-sm-2 col-form-label text-right">Category</label>
            <div class="col-sm-10">
                <select class="form-control" id="category" name="category_id">
                    <option value="">Choose...</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php if (($auction['category_id'] ?? '') == $category['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($category['full_category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Size -->
        <div class="form-group row">
            <label for="size" class="col-sm-2 col-form-label text-right">Size</label>
            <div class="col-sm-10">
                <select class="form-control" id="size" name="size_id">
                    <option value="">Choose...</option>
                    <?php foreach ($sizes as $size): ?>
                        <option value="<?php echo $size['size_id']; ?>" <?php if (($auction['size_id'] ?? '') == $size['size_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($size['size']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Material -->
        <div class="form-group row">
            <label for="material" class="col-sm-2 col-form-label text-right">Material</label>
            <div class="col-sm-10">
                <select class="form-control" id="material" name="material_id">
                    <option value="">Choose...</option>
                    <?php foreach ($materials as $material): ?>
                        <option value="<?php echo $material['material_id']; ?>" <?php if (($auction['material_id'] ?? '') == $material['material_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($material['material']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Color -->
        <div class="form-group row">
            <label for="color" class="col-sm-2 col-form-label text-right">Color</label>
            <div class="col-sm-10">
                <select class="form-control" id="color" name="color_id">
                    <option value="">Choose...</option>
                    <?php foreach ($colors as $color): ?>
                        <option value="<?php echo $color['color_id']; ?>" <?php if (($auction['color_id'] ?? '') == $color['color_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($color['color']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Condition -->
        <div class="form-group row">
            <label for="condition" class="col-sm-2 col-form-label text-right">Condition</label>
            <div class="col-sm-10">
                <select class="form-control" id="condition" name="item_condition">
                    <option value="new" <?php if (($auction['item_condition'] ?? '') == 'new') echo 'selected'; ?>>New</option>
                    <option value="used" <?php if (($auction['item_condition'] ?? '') == 'used') echo 'selected'; ?>>Used</option>
                </select>
            </div>
        </div>

        <!-- Starting Price -->
        <div class="form-group row">
            <label for="startingPrice" class="col-sm-2 col-form-label text-right">Starting Price (£)</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="startingPrice" name="starting_price" value="<?php echo htmlspecialchars($auction['starting_price'] ?? ''); ?>" required>
            </div>
        </div>

        <!-- Reserve Price -->
        <div class="form-group row">
            <label for="reservePrice" class="col-sm-2 col-form-label text-right">Reserve Price (£)</label>
            <div class="col-sm-10">
                <input type="number" class="form-control" id="reservePrice" name="reserve_price" value="<?php echo htmlspecialchars($auction['reserve_price'] ?? ''); ?>" required>
            </div>
        </div>

        <!-- End Date -->
        <div class="form-group row">
            <label for="endDate" class="col-sm-2 col-form-label text-right">End Date</label>
            <div class="col-sm-10">
                <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo htmlspecialchars($auction['end_date'] ?? ''); ?>" required>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group row">
            <div class="col-sm-10 offset-sm-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="admin_users.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close(); // Close database connection
?>
