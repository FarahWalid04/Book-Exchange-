<?php
// This should be at the top of almost every user-facing page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Use include_once to prevent errors if db.php is already included
include_once 'includes/db.php'; 

$notification_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // New query to count unread notifications from the 'notifications' table
    $stmt = $conn->prepare("SELECT COUNT(id) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $notification_count = $result['unread_count'];
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Exchange</title>
    <!-- We'll use Bootstrap for basic styling. You can add your own style.css later. -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Book Exchange</a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="book-listings.php">Browse Books</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item"><a class="nav-link" href="add-book.php">Add Book</a></li>
                <li class="nav-item"><a class="nav-link" href="my-books.php">My Books</a></li>
                
            <?php endif; ?>
        </ul>
        <ul class="navbar-nav ml-auto">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">
                        Notifications <?php if ($notification_count > 0) echo "<span class='badge badge-danger'>{$notification_count}</span>"; ?>
                    </a>
                </li>
                <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php?id=<?php echo $_SESSION['user_id']; ?>">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<main class="container mt-4">