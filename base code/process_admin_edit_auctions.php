<?php
include_once("connection.php"); // Database connection

// Check if the request method is POST and auction_id is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['auction_id'])) {
    // Retrieve auction details from the form submission
    $auction_id = intval($_POST['auction_id']);
    $item_name = $_POST['item_name'] ?? '';
    $item_description = $_POST['item_description'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $size_id = $_POST['size_id'] ?? null;
    $material_id = $_POST['material_id'] ?? null;
    $color_id = $_POST['color_id'] ?? null;
    $item_condition = $_POST['item_condition'] ?? 'new';
    $starting_price = $_POST['starting_price'] ?? 0;
    $reserve_price = $_POST['reserve_price'] ?? 0;
    $end_date = $_POST['end_date'] ?? '';

    // Prepare the SQL query to update the auction details
    $sql = "UPDATE auction SET 
            item_name = ?, 
            item_description = ?, 
            category_id = ?, 
            size_id = ?, 
            material_id = ?, 
            color_id = ?, 
            item_condition = ?, 
            starting_price = ?, 
            reserve_price = ?, 
            end_date = ? 
            WHERE auction_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssiiiiidssi", 
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

    // Execute the update query
    if ($stmt->execute()) {
        // Redirect to admin_auctions.php with a success message
        header("Location: admin_edit_auctions.php?update=success");
    } else {
        // Redirect with an error message in case of failure
        header("Location: admin_auctions.php?update=error");
    }

    $stmt->close();
} else {
    // Redirect to admin_auctions.php if accessed without POST data
    header("Location: admin_auctions.php");
}

$conn->close(); // Close the database connection
exit;
?>
