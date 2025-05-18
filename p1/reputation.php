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

$q_count = $conn->query("SELECT COUNT(*) AS c FROM questions WHERE user_id=$user_id")->fetch_assoc()['c'];
$a_count = $conn->query("SELECT COUNT(*) AS c FROM answers WHERE user_id=$user_id")->fetch_assoc()['c'];
$reputation = ($a_count * 10) + ($q_count * 5);

echo "<h2>Reputation</h2>";
echo "<b>Your reputation:</b> $reputation<br>";
echo "(10 points per answer, 5 per question)";
?>