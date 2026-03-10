<?php
session_start();
include 'includes/db.php';

// --- Security Check: Ensure the user is logged in before processing anything ---
if (!isset($_SESSION['user_id'])) {
    // If not logged in, send them to the login page.
    header('Location: login.php');
    exit();
}

// Get the current user's ID and username from the session
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username']; // Note: Make sure 'username' is saved to the session in your login.php file.

// --- ACTION 1: Handle a New Exchange Request ---
// This block runs when a user clicks the "Request Exchange" button on book-details.php
if (isset($_POST['request_exchange'])) {
    $book_id = intval($_POST['book_id']);
    $owner_id = intval($_POST['owner_id']);

    // Get the book's title to use in the notification message
    $book_stmt = $conn->prepare("SELECT title FROM books WHERE id = ?");
    $book_stmt->bind_param("i", $book_id);
    $book_stmt->execute();
    $book_title_result = $book_stmt->get_result()->fetch_assoc();
    $book_title = $book_title_result ? $book_title_result['title'] : 'a book'; // Fallback title
    $book_stmt->close();

    // Insert the new exchange request into the database with 'pending' status
    $stmt = $conn->prepare("INSERT INTO exchanges (book_id, requester_id, owner_id, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("iii", $book_id, $current_user_id, $owner_id);

    if ($stmt->execute()) {
        // If successful, create a notification for the book's owner
        $message = htmlspecialchars($current_username) . " has requested your book: '" . htmlspecialchars($book_title) . "'.";
        $link = "my-books.php";
        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
        $notify_stmt->bind_param("iss", $owner_id, $message, $link);
        $notify_stmt->execute();
        $notify_stmt->close();

        // Redirect the user back to the book details page with a success message
        header('Location: book-details.php?id=' . $book_id . '&success=requested');
    } else {
        // If there was a database error, redirect back with an error message
        header('Location: book-details.php?id=' . $book_id . '&error=dberror');
    }
    $stmt->close();
    exit();
}

// --- ACTION 2: Handle an Owner's Decision (Approve/Reject) ---
// This block runs when a book owner clicks "Approve" or "Reject" on my-books.php
if (isset($_POST['manage_request'])) {
    $exchange_id = intval($_POST['exchange_id']);
    $action = $_POST['action']; // This will be 'approve' or 'reject'
    $book_id = intval($_POST['book_id']);

    // Get the exchange details to verify the current user is the owner
    $verify_stmt = $conn->prepare("SELECT owner_id, requester_id FROM exchanges WHERE id = ?");
    $verify_stmt->bind_param("i", $exchange_id);
    $verify_stmt->execute();
    $exchange = $verify_stmt->get_result()->fetch_assoc();

    // Only proceed if the exchange exists and the current user is the owner
    if ($exchange && $exchange['owner_id'] == $current_user_id) {
        $new_status = ($action == 'approve') ? 'approved' : 'rejected';
        $requester_id = $exchange['requester_id'];

        // Update the exchange's status in the database
        $update_stmt = $conn->prepare("UPDATE exchanges SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_status, $exchange_id);
        $update_stmt->execute();
        $update_stmt->close();

        // If the owner approved the request, mark the book as unavailable
        if ($new_status == 'approved') {
            $book_update_stmt = $conn->prepare("UPDATE books SET availability = 0 WHERE id = ?");
            $book_update_stmt->bind_param("i", $book_id);
            $book_update_stmt->execute();
            $book_update_stmt->close();
        }

        // Create a notification for the user who made the request
        $book_title_stmt = $conn->prepare("SELECT title FROM books WHERE id = ?");
        $book_title_stmt->bind_param("i", $book_id);
        $book_title_stmt->execute();
        $book_title = $book_title_stmt->get_result()->fetch_assoc()['title'];
        
        $message = "Your request for '" . htmlspecialchars($book_title) . "' has been " . $new_status . ".";
        $link = "my-books.php";
        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
        $notify_stmt->bind_param("iss", $requester_id, $message, $link);
        $notify_stmt->execute();
        $notify_stmt->close();
    }
    // Redirect the owner back to their "My Books" page
    header('Location: my-books.php?managed=true');
    exit();
}

// --- ACTION 3: Handle a Requester Completing an Exchange ---
// This block runs when the requester clicks "Mark as Completed" on my-books.php
if (isset($_POST['complete_exchange'])) {
    $exchange_id = intval($_POST['exchange_id']);
    
    // Get exchange details to verify the current user is the requester and the status is 'approved'
    $verify_stmt = $conn->prepare("SELECT requester_id, owner_id, book_id FROM exchanges WHERE id = ? AND status = 'approved'");
    $verify_stmt->bind_param("i", $exchange_id);
    $verify_stmt->execute();
    $exchange = $verify_stmt->get_result()->fetch_assoc();

    // Only proceed if the exchange is valid and the user is the requester
    if ($exchange && $exchange['requester_id'] == $current_user_id) {
        // Update the status to 'completed'
        $update_stmt = $conn->prepare("UPDATE exchanges SET status = 'completed' WHERE id = ?");
        $update_stmt->bind_param("i", $exchange_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Create a notification for the book's owner
        $owner_id = $exchange['owner_id'];
        $book_id = $exchange['book_id'];

        $book_title_stmt = $conn->prepare("SELECT title FROM books WHERE id = ?");
        $book_title_stmt->bind_param("i", $book_id);
        $book_title_stmt->execute();
        $book_title = $book_title_stmt->get_result()->fetch_assoc()['title'];

        $message = "The exchange for '" . htmlspecialchars($book_title) . "' with " . htmlspecialchars($current_username) . " has been marked as complete.";
        $link = "profile.php?id=" . $owner_id; // Link to the owner's profile
        $notify_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
        $notify_stmt->bind_param("iss", $owner_id, $message, $link);
        $notify_stmt->execute();
        $notify_stmt->close();
    }
    // Redirect the requester back to their "My Books" page
    header('Location: my-books.php?status=completed');
    exit();
}

// If the script is accessed directly without a valid POST action, redirect to the homepage
header('Location: index.php');
exit();
?>