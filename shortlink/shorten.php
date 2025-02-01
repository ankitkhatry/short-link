<?php
$host = 'localhost';
$dbname = 'link_shortener'; // Change this to your local database name
$username = 'root'; // Default XAMPP/MAMP/LAMP username
$password = ''; // Default is empty for local MySQL

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['url'])) {
    $original_url = $conn->real_escape_string($_POST['url']);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $short_code = substr(md5(uniqid(rand(), true)), 0, 6);
    $created_at = date("Y-m-d H:i:s");
    $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $check_sql = "SELECT * FROM links WHERE original_url = '$original_url' AND ip_address = '$ip_address' AND created_at > NOW() - INTERVAL 1 HOUR";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'error' => 'You can only shorten the same link once every hour.',
            'short_url' => 'http://localhost/' . $row['short_code'], // Changed for local testing
        ]);
    } else {
        $sql = "INSERT INTO links (original_url, short_code, created_at, expires_at, ip_address) 
                VALUES ('$original_url', '$short_code', '$created_at', '$expires_at', '$ip_address')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode([
                'short_url' => 'http://localhost/' . $short_code, // Changed for local testing
                'expires_at' => $expires_at,
            ]);
        } else {
            echo json_encode(['error' => 'Unable to shorten the URL.']);
        }
    }

    $conn->close();
} else {
    echo json_encode(['error' => 'URL not provided.']);
}
?>
