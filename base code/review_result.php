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
            // Update the average_rating in the users table
            $update_sql = "
                UPDATE users u
                SET u.average_rating = (
                    SELECT AVG(r.rating)
                    FROM review r
                    WHERE r.reviewed_user = u.username
                )
                WHERE u.username = ?";
            
            if ($update_stmt = $conn->prepare($update_sql)) {
                // Bind the username to update the average_rating for the reviewed user
                $update_stmt->bind_param("s", $reviewed_user);

                // Execute the update statement
                if ($update_stmt->execute()) {
                    echo "<div class='alert alert-info'>Average rating updated for user: $reviewed_user.</div>";
                } else {
                    echo "<div class='alert alert-warning'>Error updating average rating: " . $update_stmt->error . "</div>";
                }

                // Close the update statement
                $update_stmt->close();
            } else {
                echo "<div class='alert alert-warning'>Error preparing update query: " . $conn->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error submitting review: " . $stmt->error . "</div>";
        }
            
        // Close the prepared statement
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Error preparing the query: " . $conn->error . "</div>";
    }
}

?>