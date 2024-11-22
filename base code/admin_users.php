<?php
include_once("header_admin.php");
include_once("connection.php");
 
// Fetch users data from the database
$sql = "SELECT username, email, average_rating, accountType FROM users";
$result = $conn->query($sql);

// Check for query errors
if (!$result) {
    die("Error retrieving users: " . $conn->error);
}
?>

<div class="container mt-5">
    <h2 class="my-4">Registered Users</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Average Rating</th>
                    <th>Account Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['average_rating']); ?></td>
                        <td><?php echo htmlspecialchars($row['accountType']); ?></td>
                        <td>
                            <a href="admin_edit_users.php?username=<?php echo urlencode($row['username']); ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No users found in the system.</p>
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