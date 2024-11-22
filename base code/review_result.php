<?php include_once("header.php");
include 'connection.php'; 
// Retrieve username from session
$username = $_SESSION['username'];
// Handle form submission (if POST request is made)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the inputs
    $auction_id = intval($_POST['auction_id']);
    $reviewed_user = $_POST['reviewed_user'];
    $review = trim($_POST['review']);
    $rating = intval($_POST['rating']);

    // Validate the input data
    if (empty($auction_id) || empty($reviewed_user)) {
        echo "Error fetching the data.";
        exit;
    }
    // Validate the input data
    if (empty($review) || empty($rating) || $rating < 1 || $rating > 5) {
        echo "Please provide a valid review and rating.";
        exit;
    }
    // Prepare the SQL query to insert the review into the database
    $sql = "INSERT INTO review (auction_id, review_author, reviewed_user, review, rating) 
            VALUES (?, ?, ?, ?, ?)";
        
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the prepared statement
        $stmt->bind_param("isssi", $auction_id, $username, $reviewed_user, $review, $rating);
            
        // Execute the statement and check for success
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Review submitted successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error submitting review: " . $stmt->error . "</div>";
        }
            
        // Close the prepared statement
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Error preparing the query: " . $conn->error . "</div>";
    }}

?>