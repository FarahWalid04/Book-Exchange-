<?php
session_start();
include 'includes/db.php';

// Security Check: User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check for correct request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: messages.php');
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
// Use 'message_body' to match the form in conversation.php
$message_body = isset($_POST['message_body']) ? trim($_POST['message_body']) : '';
// The form does not send a book_id, so this will be null, which is fine.
$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : null;

// Validate input
if ($receiver_id <= 0 || empty($message_body)) {
    header('Location: conversation.php?partner_id=' . $receiver_id . '&error=empty');
    exit();
}

// Prevent user from messaging themselves
if ($sender_id === $receiver_id) {
    header('Location: messages.php?error=self');
    exit();
}

// --- CORE FIX ---
// The INSERT statement now uses the EXACT column names from your screenshot.
// `book_id` is used instead of `related_book_id`.
// `message` is used instead of `message_body`.
$stmt_insert = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, book_id, message) VALUES (?, ?, ?, ?)");
$stmt_insert->bind_param("iiis", $sender_id, $receiver_id, $book_id, $message_body);

if ($stmt_insert->execute()) {
    // --- ADD NOTIFICATION FOR THE RECEIVER ---
    $sender_username = $_SESSION['username'] ?? 'Someone';
    $notification_message = "You have a new message from " . htmlspecialchars($sender_username);
    $link = "conversation.php?partner_id=" . $sender_id;
    
    $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
    $notify_stmt->bind_param("iss", $receiver_id, $notification_message, $link);
    $notify_stmt->execute();
    $notify_stmt->close();
}

$stmt_insert->close();
$conn->close();

// --- Redirect back to the conversation ---
header('Location: conversation.php?partner_id=' . $receiver_id);
exit();
?>