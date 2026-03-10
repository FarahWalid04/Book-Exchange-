<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch all notifications for the current user
$stmt = $conn->prepare("SELECT id, message, link, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container">
    <h3>Notifications</h3>
    <div class="list-group">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Style unread notifications differently
                $unread_style = $row['is_read'] == 0 ? 'list-group-item-info' : '';
                echo "<a href='{$row['link']}' class='list-group-item list-group-item-action {$unread_style}'>";
                echo $row['message'];
                echo "<br><small class='text-muted'>" . date('F j, Y, g:i a', strtotime($row['created_at'])) . "</small>";
                echo "</a>";
            }
        } else {
            echo "<div class='list-group-item'>You have no notifications.</div>";
        }
        ?>
    </div>
</div>

<?php
// After displaying, mark all of this user's unread notifications as read.
$update_stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
$update_stmt->bind_param("i", $user_id);
$update_stmt->execute();
$update_stmt->close();

$stmt->close();
include 'includes/footer.php';
?>