<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// --- CORE FIX ---
// The query now selects from the correct 'message' column instead of 'message_body'.
$stmt = $conn->prepare("
    SELECT 
        u.id as partner_id,
        u.username as partner_name,
        m.message, 
        m.created_at
    FROM messages m
    JOIN users u ON u.id = IF(m.sender_id = ?, m.receiver_id, m.sender_id)
    WHERE m.id IN (
        SELECT MAX(id)
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
    )
    ORDER BY m.created_at DESC
");
// The number of bind params must match the number of '?'
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result();
?>

<div class="generic-page-header"><div class="container"><h1>My Messages</h1></div></div>

<main class="page-main-content">
    <div class="container">
        <div class="messages-inbox">
            <?php if ($conversations->num_rows > 0): ?>
                <ul class="conversation-list">
                    <?php while ($convo = $conversations->fetch_assoc()): ?>
                        <li>
                            <a href="conversation.php?partner_id=<?php echo $convo['partner_id']; ?>" class="conversation-link">
                                <div class="conversation-partner"><strong><?php echo htmlspecialchars($convo['partner_name']); ?></strong></div>
                                <div class="conversation-excerpt">
                                    <!-- We now echo the correct 'message' variable -->
                                    <p><?php echo htmlspecialchars(substr($convo['message'], 0, 80)) . '...'; ?></p>
                                </div>
                                <div class="conversation-timestamp"><small><?php echo date('M j, Y, g:i a', strtotime($convo['created_at'])); ?></small></div>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>You have no messages yet. You can start a conversation from a book's detail page.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
?>