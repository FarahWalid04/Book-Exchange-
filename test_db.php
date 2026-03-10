<?php
echo "<h1>Database Connection Test</h1>";

// These are the settings from your includes/db.php file
$servername = "localhost";
$username = "root";
$password = ""; // For default XAMPP, this is empty.
$dbname = "book_exchangedb";

// Attempt to connect
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    echo "<p style='color: red; font-weight: bold;'>Connection FAILED.</p>";
    echo "<p>Error: " . $conn->connect_error . "</p>";
    echo "<hr>";
    echo "<p><strong>Troubleshooting Tips:</strong></p>";
    echo "<ul>";
    echo "<li>Is the MySQL server running in the XAMPP Control Panel?</li>";
    echo "<li>Is the database name '<strong>" . $dbname . "</strong>' spelled correctly?</li>";
    echo "<li>Is the password correct? (Default is empty: '')</li>";
    echo "</ul>";
} else {
    echo "<p style='color: green; font-weight: bold;'>Connection SUCCESSFUL!</p>";
    echo "<p>PHP can successfully connect to the '" . $dbname . "' database.</p>";
    $conn->close();
}
?>