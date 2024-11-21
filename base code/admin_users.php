<?php
include_once("header_admin.php");
include_once("connection.php");

// Fetch users from the database
$sql = "SELECT username, email, average_rating, accountType FROM users";
$result = $conn->query($sql);
?>

<div class="container mt-5">
  <h2>Registered Users</h2>
  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered mt-3">
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
              <a href="admin_edit_user.php?username=<?php echo urlencode($row['username']); ?>" class="btn btn-primary btn-sm">Edit</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No users found.</p>
  <?php endif; ?>
</div>

<?php $conn->close(); ?>
