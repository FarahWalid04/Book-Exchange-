<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$partner_id = isset($_GET['partner_id']) ? intval($_GET['partner_id']) : 0;

if ($partner_id <= 0) {
    header('Location: messages.php');
    exit();
}

$partner_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$partner_stmt->bind_param("i", $partner_id);
$partner_stmt->execute();
$partner = $partner_stmt->get_result()->fetch_assoc();

if (!$partner) {
    header('Location: messages.php');
    exit();
}

$messages_stmt = $conn->prepare("SELECT sender_id, message, created_at FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
$messages_stmt->bind_param("iiii", $user_id, $partner_id, $partner_id, $user_id);
$messages_stmt->execute();
$messages = $messages_stmt->get_result();

$update_read_stmt = $conn->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE sender_id = ? AND receiver_id = ?");
$update_read_stmt->bind_param("ii", $partner_id, $user_id);
$update_read_stmt->execute();
?>

<div class="generic-page-header"><div class="container"><h1>Conversation with <?php echo htmlspecialchars($partner['username']); ?></h1></div></div>

<main class="page-main-content">
    <div class="container">
        <div class="chat-window">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($msg = $messages->fetch_assoc()):
                    $message_class = ($msg['sender_id'] == $user_id) ? 'chat-message-sent' : 'chat-message-received';
                ?>
                    <div class="chat-message <?php echo $message_class; ?>">
                        <!-- --- CORE FIX ---: Echo the correct 'message' variable -->
                        <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                        <small><?php echo date('M j, g:i a', strtotime($msg['created_at'])); ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>This is the beginning of your conversation. Send a message to get started!</p>
            <?php endif; ?>
        </div>
        <div class="message-form-container">
            <form action="send-message.php" method="POST" class="message-form">
                <input type="hidden" name="receiver_id" value="<?php echo $partner_id; ?>">
                <textarea name="message_body" placeholder="Type your message..." required></textarea>
                <button type="submit" class="btn">Send</button>
            </form>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
?>