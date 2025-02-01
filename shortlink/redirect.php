<?php
$host = 'localhost';
$dbname = 'link_shortener'; // Change this to your local database name
$username = 'root'; // Default XAMPP/MAMP/LAMP username
$password = ''; // Default is empty for local MySQL

// Establish DB connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Get short code from URL
if (isset($_GET['code'])) {
    $short_code = $_GET['code'];

    // Fetch the original URL and expiration time based on the short code
    $sql = "SELECT * FROM links WHERE short_code = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$short_code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the link exists and is not expired
    if ($row) {
        // If the current time is greater than expiration time, show an error
        if (strtotime($row['expires_at']) < time()) {
            echo "Sorry, this link has expired.";
        } else {
            // Redirect if not expired
            header("Location: " . $row['original_url']);
            exit();
        }
    } else {
        echo "Invalid or non-existent link.";
    }
} else {
    echo "No code provided.";
}
?>