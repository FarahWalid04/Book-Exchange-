<?php
// --- PHP LOGIC & SETUP ---
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Security Check: Ensure the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// --- DATA FETCHING (PART 1): Get the books this user owns ---
// UPDATED: The query now also selects the 'cover_image' column.
$my_books_stmt = $conn->prepare("SELECT id, title, author, availability, cover_image FROM books WHERE user_id = ? ORDER BY created_at DESC");
$my_books_stmt->bind_param("i", $user_id);
$my_books_stmt->execute();
$my_books_result = $my_books_stmt->get_result();

?>
<!-- --- HTML STRUCTURE & DISPLAY --- -->
<main class="my-books-section">
    <div class="container my-books-container">
        <h2 class="section-title"><?php echo htmlspecialchars($username); ?>'s Dashboard</h2>
        
        <div style="text-align: center; margin-bottom: 40px;">
            <a href="add-book.php" class="btn btn-primary">+ Add New Book</a>
        </div>

        <hr>
        <h3 class="section-subtitle">My Book Listings & Incoming Requests</h3>

        <div class="book-list my-books-list">
            <?php if ($my_books_result->num_rows > 0): ?>
                <?php while ($book = $my_books_result->fetch_assoc()): ?>
                    <article class="book-card">
                        
                        <!-- ============================================= -->
                        <!-- == THE NEW DYNAMIC IMAGE LOGIC IS BELOW    == -->
                        <!-- ============================================= -->
                        <?php
                        if (!empty($book['cover_image'])) {
                            // Create the correct, absolute path to the image
                            $image_path = '/Book Exchange/uploads/covers/' . htmlspecialchars($book['cover_image']);
                        } else {
                            // Use the placeholder image service
                            $image_path = 'https://via.placeholder.com/200x300.png?text=' . urlencode($book['title']);
                        }
                        ?>
                        <img src="<?php echo $image_path; ?>" alt="Cover for <?php echo htmlspecialchars($book['title']); ?>" />

                        <div class="book-info">
                            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                            
                            <div class="status-section">
                                <?php
                                $requests_stmt = $conn->prepare("SELECT e.id, u.username as requester_name FROM exchanges e JOIN users u ON e.requester_id = u.id WHERE e.book_id = ? AND e.status = 'pending'");
                                $requests_stmt->bind_param("i", $book['id']);
                                $requests_stmt->execute();
                                $requests_result = $requests_stmt->get_result();

                                if ($requests_result->num_rows > 0) {
                                    echo '<p><strong>Pending Requests:</strong></p><ul class="list-unstyled">';
                                    while ($request = $requests_result->fetch_assoc()) {
                                ?>
                                        <li>
                                            From <strong><?php echo htmlspecialchars($request['requester_name']); ?></strong>
                                            <form action="handle_exchange_request.php" method="post" class="mt-2">
                                                <input type="hidden" name="exchange_id" value="<?php echo $request['id']; ?>">
                                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                <input type="hidden" name="manage_request" value="1">
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        </li>
                                <?php
                                    }
                                    echo '</ul>';
                                } else {
                                    $status_color = $book['availability'] ? 'green' : 'red';
                                    $status_text = $book['availability'] ? 'Available' : 'Unavailable';
                                    echo "<p>Status: <span style='color: {$status_color}; font-weight: bold;'>{$status_text}</span></p>";
                                }
                                $requests_stmt->close();
                                ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You haven't listed any books yet. Click the button above to add one!</p>
            <?php endif; ?>
        </div> <!-- End of .book-list -->

        <hr style="margin-top: 40px; margin-bottom: 40px;">

        <!-- Section for Outgoing Requests -->
        <h3 class="section-subtitle">My Outgoing Exchange Requests</h3>
        <div class="outgoing-requests-list">
            <!-- ... outgoing requests logic remains the same ... -->
        </div>
    </div>
</main>

<?php
$my_books_stmt->close();
include 'includes/footer.php'; 
?>