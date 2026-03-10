<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
 }
$user_id = $_SESSION['user_id'];
$exchange_id = isset($_GET['exchange_id']) ? intval($_GET['exchange_id']) : 0;

// Fetch exchange details to verify the user is part of it and it's completed
$stmt = $conn->prepare("SELECT requester_id, owner_id FROM exchanges WHERE id = ? AND status = 'completed'");
$stmt->bind_param("i", $exchange_id);
$stmt->execute();
$result = $stmt->get_result();
$exchange = $result->fetch_assoc();

if (!$exchange || ($user_id != $exchange['requester_id'] && $user_id != $exchange['owner_id'])) {
    echo "<p class='container'>Invalid request or you do not have permission to review this exchange.</p>";
    include 'includes/footer.php';
    exit();
}

// Determine who is being reviewed
$reviewed_user_id = ($user_id == $exchange['requester_id']) ? $exchange['owner_id'] : $exchange['requester_id'];
?>

<div class="container">
    <h3>Leave a Review</h3>
    <form action="submit_review.php" method="post">
        <input type="hidden" name="exchange_id" value="<?php echo $exchange_id; ?>">
        <input type="hidden" name="reviewed_user_id" value="<?php echo $reviewed_user_id; ?>">

        <div class="form-group">
            <label for="rating">Rating (1-5):</label>
            <select name="rating" id="rating" class="form-control" required>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Very Good</option>
                <option value="3">3 - Good</option>
                <option value="2">2 - Fair</option>
                <option value="1">1 - Poor</option>
            </select>
        </div>
        <div class="form-group">
            <label for="review">Review (optional):</label>
            <textarea name="review" id="review" class="form-control" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Review</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>