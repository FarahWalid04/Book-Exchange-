<?php
// --- STEP 1: PHP LOGIC (ADAPTED FOR OUR PROJECT) ---
session_start();
include 'includes/db.php'; // Our project's database connection ($conn)

$error_message = ''; // Variable to hold login errors

// If the user is already logged in, redirect them to their dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: my-books.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $error_message = "Both email and password are required.";
    } else {
        // --- SECURE LOGIN PROCESS ---
        // Prepare a statement to find the user by their email
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify the user exists and the password is correct
        if ($user && password_verify($password, $user["password_hash"])) {
            // Login successful! Save user data into the session.
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            
            // Redirect to the user's main dashboard page
            header("Location: my-books.php");
            exit;
        } else {
            // If login fails, set a generic error message
            $error_message = "Invalid email or password.";
        }
        $stmt->close();
    }
}

// --- STEP 2: INCLUDE THE SITE HEADER ---
// This provides the top of the HTML page and the beautiful navigation bar
include 'includes/header.php';
?>

<!--
    --- STEP 3: THE HTML STRUCTURE (FROM YOUR .html FILE) ---
    This is the visual part of the page, now connected to our PHP logic.
-->
<main class="page-main-content">
    <div class="container">
        <div class="auth-section">
          <h2 class="auth-title">Login</h2>

          <!-- The form now submits to itself (login.php) -->
          <form action="login.php" method="POST" class="auth-form">
          
            <!-- Display any login error messages here -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" style="color: red; background: #ffdddd; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Display a success message after registration -->
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success" style="color: green; background: #ddffdd; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                    Registration successful! Please log in.
                </div>
            <?php endif; ?>

            <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
              <label for="password">Password:</label>
              <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Login</button>
            
            <!-- Link now points to the correct register.php file -->
            <p class="auth-text">Don't have an account? <a href="register.php">Sign up</a></p>
          </form>
        </div>
    </div>
</main>

<?php
// --- STEP 4: INCLUDE THE SITE FOOTER ---
// This closes the page with the consistent, beautiful footer
include 'includes/footer.php';
?>