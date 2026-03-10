<?php
// --- PHP SETUP AND DATA FETCHING ---
session_start();
include 'includes/db.php';
include 'includes/header.php';

// UPDATED: The query now also selects the 'cover_image' column.
$featured_books_stmt = $conn->prepare("SELECT id, title, author, cover_image FROM books WHERE availability = 1 ORDER BY created_at DESC LIMIT 4");
$featured_books_stmt->execute();
$featured_books_result = $featured_books_stmt->get_result();
?>

<!--
    --- HTML STRUCTURE THAT MATCHES YOUR style.css ---
-->
<main>
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="container hero-container">
        <div class="hero-content">
          <span class="subtitle">Start Exchanging</span>
          <h1>You're Only One Book Away From a Good Mood</h1>
          <p>
            Find your next favorite book. List your own books and exchange them with a community of readers.
          </p>
          <a href="book-listings.php" class="btn">Discover Books</a>
        </div>
        <div class="hero-books">
           <div class="book-grid-small">
              <article class="book-card-sm"><img src="uploads/covers/crime and punishment by dostoevsky.jpeg" alt="Book cover" /></article>
              <article class="book-card-sm"><img src="uploads/covers/Moby Dick by Herman Melville_ The Original Classic Hardcover - A Riveting Exploration of One Man’s Obsession and Nature’s Unforgiving Power.jpeg" alt="Book cover" /></article>
              <article class="book-card-sm"><img src="uploads/covers/The Alchemist cover design by Jim Tierney; art direction by Michele Wetherbee and Laura Beers (HarperOne).jpeg" alt="Book cover" /></article>
           </div>
        </div>
      </div>
    </section>

    <!-- Featured Books Section -->
    <section class="featured-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Discover Your New Book</h2>
          <p>Explore some of the great books recently added by our community.</p>
        </div>
        <div class="book-list">
          <?php
            if ($featured_books_result && $featured_books_result->num_rows > 0) {
                while ($book = $featured_books_result->fetch_assoc()) {
          ?>
                    <article class="book-card">
                        <a href="book-details.php?id=<?php echo $book['id']; ?>">
                            
                            <!-- ============================================= -->
                            <!-- == THE NEW DYNAMIC IMAGE LOGIC IS BELOW    == -->
                            <!-- ============================================= -->
                            <?php
                            if (!empty($book['cover_image'])) {
                                // Create the correct, absolute path to the image
                                $image_path = '/Book Exchange/uploads/covers/' . htmlspecialchars($book['cover_image']);
                            } else {
                                // Use the placeholder image service
                                $image_path = 'https://via.placeholder.com/300x450.png?text=' . urlencode($book['title']);
                            }
                            ?>
                            <img src="<?php echo $image_path; ?>" alt="Cover for <?php echo htmlspecialchars($book['title']); ?>" />
                        </a>
                        <div class="book-info">
                            <h3 class="book-title"><a href="book-details.php?id=<?php echo $book['id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a></h3>
                            <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                        </div>
                    </article>
          <?php
                } // End the while loop
            } else {
                echo "<p>No books have been added yet. Be the first!</p>";
            }
            if ($featured_books_stmt) {
                $featured_books_stmt->close();
            }
          ?>
        </div>
        <div class="section-footer">
          <a href="book-listings.php" class="btn">Discover More Books</a>
        </div>
      </div>
    </section>

    <!-- Community Join Section -->
    <section class="community-section">
        <!-- ... content remains the same ... -->
    </section>
</main>

<?php
include 'includes/footer.php'; 
?>