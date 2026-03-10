<?php
// STEP 1: THE PHP LOGIC (ADAPTED FOR OUR PROJECT)
// This code runs on the server before any HTML is sent.

session_start();
include 'includes/db.php'; // Our project's database connection ($conn)

$error_message = ''; // A variable to hold any error messages

// Check if the form was submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the data from the form
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm-password"];

    // --- Validation ---
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // --- Process Registration ---
        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement using our project's schema and $conn variable
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        // Execute the statement and check for success
        if ($stmt->execute()) {
            // If registration is successful, redirect to the login page
            header("Location: login.php?registered=true");
            exit();
        } else {
            // If it fails, it's likely a duplicate username or email
            $error_message = "This username or email is already taken.";
        }
        $stmt->close();
    }
}
?>

<!-- STEP 2: THE HTML STRUCTURE (FROM YOUR .html FILE) -->
<!-- This is the visual part of the page -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Book Exchange</title>
  <!-- NOTE: This should point to your main CSS file in the assets folder -->
  <link rel="stylesheet" href="assets/style.css"> 
</head>
<body class="page-register">

  <!-- We will replace this with our include file for consistency -->
  <?php include 'includes/header.php'; ?>

  <!-- Register Section -->
  <main class="auth-section">
    <div class="auth-card">
      <h2 class="auth-title">Create an Account</h2>
      
      <!-- The form now submits to itself (register.php) -->
      <form action="register.php" method="POST" class="auth-form">
      
        <!-- STEP 3: DISPLAY PHP ERROR MESSAGES IN THE HTML -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required>
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
        <div class="form-group">
          <label for="confirm-password">Confirm Password:</label>
          <input type="password" id="confirm-password" name="confirm-password" class="form-control" placeholder="Re-enter your password" required>
        </div>
        <button type="submit" class="btn" style="width: 100%;">Sign Up</button>
        <p class="auth-text">Already have an account? <a href="login.php">Login</a></p>
      </form>
    </div>
  </main>

  <!-- We will also replace this with our include file -->
  <?php include 'includes/footer.php'; ?>

</body>
</html>