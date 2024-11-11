<?php 
include_once("header.php");
include 'connection.php'; 

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: login.php'); // Redirect to login if not logged in
  exit;
}

// Get the auction ID from the URL parameter
if (!isset($_GET['auction_id'])) {
  echo "No auction selected!";
  exit;
}

$auction_id = $_GET['auction_id'];

// Query to fetch the auction details
$sql = "SELECT * FROM auction WHERE auction_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Auction not found!";
    exit;
}

$auction = $result->fetch_assoc();

// Query to get the categories from the database
$query = "SELECT category_id, category_section, category_name FROM categories ORDER BY category_section, category_name";
$category_result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$category_result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<div class="container">

<!-- Edit auction form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Edit Auction ID: <?php echo htmlspecialchars($auction['auction_id']); ?></h2>
  <div class="card">
    <div class="card-body">
      <form method="post" action="process_edit_listing.php">
        <input type="hidden" name="auction_id" value="<?php echo htmlspecialchars($auction['auction_id']); ?>">

        <!-- Title of Auction -->
        <div class="form-group row">
          <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of auction</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="auctionTitle" name="auctionTitle" value="<?php echo htmlspecialchars($auction['item_name']); ?>" required>
            <small class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
          </div>
        </div>

        <!-- Details -->
        <div class="form-group row">
          <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
          <div class="col-sm-10">
            <textarea class="form-control" id="auctionDetails" name="auctionDetails" rows="4"><?php echo htmlspecialchars($auction['item_description']); ?></textarea>
            <small class="form-text text-muted">Full details of the listing to help bidders decide if it's what they're looking for.</small>
          </div>
        </div>

        <!-- Category -->
        <div class="form-group row">
          <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionCategory" name="auctionCategory" required>
              <option selected>Choose...</option>
              <?php
                // Loop through the categories and display them in the dropdown
                while ($row = mysqli_fetch_assoc($category_result)) {
                    $categoryId = $row['category_id'];
                    $categorySection = $row['category_section'];
                    $categoryName = $row['category_name'];
                    $selected = ($categoryId == $auction['category_id']) ? "selected" : "";
                    echo "<option value='$categoryId' $selected>$categorySection - $categoryName</option>";
                }
              ?>
            </select>
            <small class="form-text text-muted"><span class="text-danger">* Required.</span> Select a category for this item.</small>
          </div>
        </div>

        <!-- Starting Price -->
        <div class="form-group row">
          <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionStartPrice" name="auctionStartPrice" value="<?php echo htmlspecialchars($auction['starting_price']); ?>" required>
            </div>
            <small class="form-text text-muted"><span class="text-danger">* Required.</span> Initial bid amount.</small>
          </div>
        </div>

        <!-- Reserve Price -->
        <div class="form-group row">
          <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionReservePrice" name="auctionReservePrice" value="<?php echo htmlspecialchars($auction['reserve_price']); ?>">
            </div>
            <small class="form-text text-muted">Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.</small>
          </div>
        </div>

        <!-- End Date -->
        <div class="form-group row">
          <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
          <div class="col-sm-10">
            <input type="datetime-local" class="form-control" id="auctionEndDate" name="auctionEndDate" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($auction['end_date']))); ?>" required>
            <small class="form-text text-muted"><span class="text-danger">* Required.</span> Day for the auction to end.</small>
          </div>
        </div>

        <button type="submit" class="btn btn-primary form-control">Edit Auction</button>
      </form>
    </div>
  </div>
</div>

</div>

<?php include_once("footer.php"); ?>
