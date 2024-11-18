<?php
include_once("connection.php");    
include_once("header_admin.php");  

// Check if auction_id is provided in the URL
if (!isset($_GET['auction_id'])) {
    echo "No auction specified.";
    exit();
}

$auction_id = $_GET['auction_id'];

// Fetch auction data from the database using the auction_id
$sql = "SELECT * FROM auction WHERE auction_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Auction not found.";
    exit();
}

$auction = $result->fetch_assoc();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $auction_id = $_POST['auction_id'];
    $item_name = $_POST['item_name'];
    $item_description = $_POST['item_description'];
    $category_id = $_POST['category_id'];
    $size_id = $_POST['size_id'];
    $material_id = $_POST['material_id'];
    $color_id = $_POST['color_id'];
    $item_condition = $_POST['item_condition'];
    $starting_price = $_POST['starting_price'];
    $reserve_price = $_POST['reserve_price'];
    $end_date = $_POST['end_date'];

    // Update auction information
    $update_sql = "UPDATE auction 
                   SET item_name = ?, item_description = ?, category_id = ?, size_id = ?, material_id = ?, 
                       color_id = ?, item_condition = ?, starting_price = ?, reserve_price = ?, end_date = ?
                   WHERE auction_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param(
        "ssiiissddsi",
        $item_name,
        $item_description,
        $category_id,
        $size_id,
        $material_id,
        $color_id,
        $item_condition,
        $starting_price,
        $reserve_price,
        $end_date,
        $auction_id
    );

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Auction updated successfully.</div>";
        // Refresh auction data after update
        $auction['item_name'] = $item_name;
        $auction['item_description'] = $item_description;
        $auction['category_id'] = $category_id;
        $auction['size_id'] = $size_id;
        $auction['material_id'] = $material_id;
        $auction['color_id'] = $color_id;
        $auction['item_condition'] = $item_condition;
        $auction['starting_price'] = $starting_price;
        $auction['reserve_price'] = $reserve_price;
        $auction['end_date'] = $end_date;
    } else {
        echo "<div class='alert alert-danger'>Error updating auction.</div>";
    }
}

// Fetch categories, materials, colors, and sizes for dropdowns
$categories = $conn->query("SELECT category_id, CONCAT(category_section, ' - ', category_name) AS full_category FROM categories")->fetch_all(MYSQLI_ASSOC);
$materials = $conn->query("SELECT material_id, material FROM materials")->fetch_all(MYSQLI_ASSOC);
$colors = $conn->query("SELECT color_id, color FROM colors")->fetch_all(MYSQLI_ASSOC);
$sizes = $conn->query("SELECT size_id, size FROM sizes")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Auction - Auction ID <?php echo htmlspecialchars($auction['auction_id']); ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Edit Auction - Auction ID <?php echo htmlspecialchars($auction['auction_id']); ?></h2>
    
    <form method="POST" action="">
        <!-- Hidden field to pass auction ID -->
        <input type="hidden" name="auction_id" value="<?php echo htmlspecialchars($auction['auction_id']); ?>">

        <!-- Title of Auction -->
        <div class="form-group">
            <label for="item_name">Title of auction</label>
            <input type="text" class="form-control" id="item_name" name="item_name" value="<?php echo htmlspecialchars($auction['item_name']); ?>" required>
        </div>

        <!-- Item Description -->
        <div class="form-group">
            <label for="item_description">Item description</label>
            <input type="text" class="form-control" id="item_description" name="item_description" value="<?php echo htmlspecialchars($auction['item_description']); ?>" required>
        </div>

        <!-- Category -->
        <div class="form-group">
            <label for="category_id">Category</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <option value="">Choose...</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>" <?php if ($auction['category_id'] == $category['category_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($category['full_category']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Size -->
        <div class="form-group">
            <label for="size_id">Size</label>
            <select class="form-control" id="size_id" name="size_id">
                <option value="">Choose...</option>
                <?php foreach ($sizes as $size): ?>
                    <option value="<?php echo $size['size_id']; ?>" <?php if ($auction['size_id'] == $size['size_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($size['size']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Material -->
        <div class="form-group">
            <label for="material_id">Material</label>
            <select class="form-control" id="material_id" name="material_id">
                <option value="">Choose...</option>
                <?php foreach ($materials as $material): ?>
                    <option value="<?php echo $material['material_id']; ?>" <?php if ($auction['material_id'] == $material['material_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($material['material']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Color -->
        <div class="form-group">
            <label for="color_id">Color</label>
            <select class="form-control" id="color_id" name="color_id">
                <option value="">Choose...</option>
                <?php foreach ($colors as $color): ?>
                    <option value="<?php echo $color['color_id']; ?>" <?php if ($auction['color_id'] == $color['color_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($color['color']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Condition -->
        <div class="form-group">
            <label for="item_condition">Condition</label>
            <select class="form-control" id="item_condition" name="item_condition">
                <option value="new" <?php if ($auction['item_condition'] == 'new') echo 'selected'; ?>>New</option>
                <option value="used" <?php if ($auction['item_condition'] == 'used') echo 'selected'; ?>>Used</option>
            </select>
        </div>

        <!-- Starting Price -->
        <div class="form-group">
            <label for="starting_price">Starting Price (£)</label>
            <input type="number" class="form-control" id="starting_price" name="starting_price" value="<?php echo htmlspecialchars($auction['starting_price']); ?>" required>
        </div>

        <!-- Reserve Price -->
        <div class="form-group">
            <label for="reserve_price">Reserve Price (£)</label>
            <input type="number" class="form-control" id="reserve_price" name="reserve_price" value="<?php echo htmlspecialchars($auction['reserve_price']); ?>" required>
        </div>

        <!-- End Date -->
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($auction['end_date']); ?>" required>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="admin_auctions.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conn->close();
?>
