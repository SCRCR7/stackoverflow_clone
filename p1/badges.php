<?php
session_start();
include 'header.php';

// Require login
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.html"); // Use login.html as per your project
    exit; 
}

// Use InfinityFree DB credentials
$conn = new mysqli(
    'sql102.infinityfree.com',             // host
    'if0_39013734',                        // username
    'sbnadmin4321',                        // password
    'if0_39013734_stackoverflow_clone'     // database
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT b.name, b.description, ub.awarded_at FROM user_badges ub JOIN badges b ON ub.badge_id = b.id WHERE ub.user_id=$user_id");

echo "<h2>Your Badges</h2>";
if ($res && $res->num_rows === 0) {
    echo "No badges earned yet.";
} elseif ($res) {
    echo "<ul>";
    while ($row = $res->fetch_assoc()) {
        echo "<li><b>" . htmlspecialchars($row['name']) . "</b>: " . htmlspecialchars($row['description']) . " <small>(awarded at " . htmlspecialchars($row['awarded_at']) . ")</small></li>";
    }
    echo "</ul>";
} else {
    echo "Error fetching badges.";
}
?>