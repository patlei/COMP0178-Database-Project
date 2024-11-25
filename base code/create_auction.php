<?php 
include_once("header.php");
include 'connection.php'; 

 // Set PHP's default timezone to UTC
 date_default_timezone_set('UTC');

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: login.php'); // Redirect to login if not logged in
  exit;
}

// Query to get the categories from the database
$query = "SELECT category_section, category_name FROM categories ORDER BY category_id";  
$category_result = mysqli_query($conn, $query);

// Check if the query was successful
if (!$category_result) {
    die("Query failed: " . mysqli_error($conn));
}
// Query to get the sizes from the database
$query2 = "SELECT size FROM sizes ORDER BY size_id ASC";  
$size_result = mysqli_query($conn, $query2);

// Check if the query was successful
if (!$size_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Query to get the materials from the database
$query3 = "SELECT material FROM materials";  
$material_result = mysqli_query($conn, $query3);

// Check if the query was successful
if (!$material_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Query to get the materials from the database
$query4 = "SELECT color FROM colors";  
$color_result = mysqli_query($conn, $query4);

// Check if the query was successful
if (!$color_result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<div class="container">

<!-- Create auction form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">
      <!-- Note: This form does not do any dynamic / client-side / 
      JavaScript-based validation of data. It only performs checking after 
      the form has been submitted, and only allows users to try once. You 
      can make this fancier using JavaScript to alert users of invalid data
      before they try to send it, but that kind of functionality should be
      extremely low-priority / only done after all database functions are
      complete. -->
      <form method="post" action="create_auction_result.php" enctype="multipart/form-data">
        <div class="form-group row">
          <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of auction</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="auctionTitle" name="auctionTitle" placeholder="e.g. Black mountain bike">
            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
          <div class="col-sm-10">
            <textarea class="form-control" id="auctionDetails" name="auctionDetails" rows="4"></textarea>
            <small id="detailsHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Full details of the listing to help bidders decide if it's what they're looking for.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionCategory" name="auctionCategory">
              <option selected>Choose...</option>
              <?php
                // Loop through the categories and display them in the dropdown
                while ($row = mysqli_fetch_assoc($category_result)) {
                    $categorySection = $row['category_section'];
                    $categoryName = $row['category_name'];
                    echo "<option value='$categoryName'>$categorySection - $categoryName</option>";
                }
              ?>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select a category for this item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionSize" class="col-sm-2 col-form-label text-right">Size</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionSize" name="auctionSize">
              <option value="" selected>Choose...</option>
              <?php
                  // Check if the query result is valid and has rows
                  if ($size_result && mysqli_num_rows($size_result) > 0) {
                      // Loop through the sizes and display them in the dropdown
                      while ($row = mysqli_fetch_assoc($size_result)) {
                          $size = $row['size'];
                          echo "<option value='" . htmlspecialchars($size) . "'>" . htmlspecialchars($size) . "</option>";
                      }
                  } else {
                      echo "<option>No sizes available</option>";
                  }
              ?>
            </select>
            <small id="sizeHelp" class="form-text text-muted"> Optional. Select a size for this item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionMaterial" class="col-sm-2 col-form-label text-right">Material</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionMaterial" name="auctionMaterial">
              <option value="" selected>Choose...</option>
              <?php
                  // Check if the query result is valid and has rows
                  if ($material_result && mysqli_num_rows($material_result) > 0) {
                      // Loop through the sizes and display them in the dropdown
                      while ($row = mysqli_fetch_assoc($material_result)) {
                          $material = $row['material'];
                          echo "<option value='" . htmlspecialchars($material) . "'>" . htmlspecialchars($material) . "</option>";
                      }
                  } else {
                      echo "<option>No materials available</option>";
                  }
              ?>
            </select>
            <small id="materialHelp" class="form-text text-muted">Optional. Select material for this item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionColor" class="col-sm-2 col-form-label text-right">Color</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionColor" name="auctionColor">
              <option value="" selected>Choose...</option>
              <?php
                  // Check if the query result is valid and has rows
                  if ($color_result && mysqli_num_rows($color_result) > 0) {
                      // Loop through the sizes and display them in the dropdown
                      while ($row = mysqli_fetch_assoc($color_result)) {
                          $color = $row['color'];
                          echo "<option value='" . htmlspecialchars($color) . "'>" . htmlspecialchars($color) . "</option>";
                      }
                  } else {
                      echo "<option>No colors available</option>";
                  }
              ?>
            </select>
            <small id="colorHelp" class="form-text text-muted">Optional. Select color for this item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionCondition" class="col-sm-2 col-form-label text-right">Condition</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionCondition" name="auctionCondition" required>
              <option value="" selected>Choose...</option>
              <option value="new">new</option>
              <option value="used">used</option>
            </select>
            <small id="conditionHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select the condition of the item.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionImage" class="col-sm-2 col-form-label text-right">Image</label>
          <div class="col-sm-10">
            <input type="file" id="auctionImage" name="auctionImage" value="">
            <small id="imageHelp" class="form-text text-muted"> Optional. Upload an image of the item in your auction</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionStartPrice" name="auctionStartPrice">
            </div>
            <small id="startBidHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Initial bid amount.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionReservePrice" name="auctionReservePrice">
            </div>
            <small id="reservePriceHelp" class="form-text text-muted">Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.</small>
          </div>
        </div>
        <div class="form-group row">
          <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
          <div class="col-sm-10">
            <input type="datetime-local" class="form-control" id="auctionEndDate" name="auctionEndDate">
            <small id="endDateHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Day for the auction to end.</small>
          </div>
        </div>
        <button type="submit" class="btn btn-primary form-control">Create Auction</button>
      </form>
    </div>
  </div>
</div>

</div>

<?php include_once("footer.php")?>
