<?php

include_once("header_admin.php");
include_once("connection.php");   


// Fetch auction data with JOINs to get string values for category, material, color, and size
$sql = "
    SELECT 
        a.auction_id,
        a.item_name,
        a.item_description,
        a.username,
        a.starting_price,
        a.reserve_price,
        a.start_date,
        a.end_date,
        a.auction_status,
        a.image_path,
        c.category_name AS category,
        m.material AS material,
        co.color AS color,
        s.size AS size,
        a.item_condition,
        a.views
    FROM auction a
    LEFT JOIN categories c ON a.category_id = c.category_id
    LEFT JOIN materials m ON a.material_id = m.material_id
    LEFT JOIN colors co ON a.color_id = co.color_id
    LEFT JOIN sizes s ON a.size_id = s.size_id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Auctions</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Auction Listings</h2>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <!-- Scrollable Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Auction ID</th>
                        <th>Item Name</th>
                        <th>Item Description</th>
                        <th>Username</th>
                        <th>Starting Price</th>
                        <th>Reserve Price</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Material</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Condition</th>
                        <th>Views</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['auction_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['item_description']); ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>£<?php echo number_format($row['starting_price'], 2); ?></td>
                            <td>£<?php echo number_format($row['reserve_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                            <td><?php echo ucfirst($row['auction_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['material']); ?></td>
                            <td><?php echo htmlspecialchars($row['color']); ?></td>
                            <td><?php echo htmlspecialchars($row['size']); ?></td>
                            <td><?php echo ucfirst($row['item_condition']); ?></td>
                            <td><?php echo $row['views']; ?></td>
                            <td>
                                <a href="admin_edit_auctions.php?auction_id=<?php echo $row['auction_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No auctions found.</p>
    <?php endif; ?>
</div>


<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Debug Dropdown Functionality -->
<script>
    $(document).ready(function () {
        console.log("Dropdowns are initialized.");
        $('.dropdown-toggle').dropdown(); // Ensure dropdowns are activated
    });
</script>

</body>
</html>

<?php
$conn->close(); // Close database connection
?>