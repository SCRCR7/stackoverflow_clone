<?php
session_start();
include 'header.php';

// Require login
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.html"); // Use login.html if that's your login page
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
$res = $conn->query("SELECT b.*, q.title FROM bounties b JOIN questions q ON b.question_id=q.id WHERE b.user_id=$user_id");

echo "<h2>Your Bounties</h2>";
if ($res && $res->num_rows === 0) {
    echo "You haven't placed any bounties.";
} elseif ($res) {
    echo "<ul>";
    while ($row = $res->fetch_assoc()) {
        echo "<li>Bounty of <b>" . htmlspecialchars($row['amount']) . "</b> on question <a href='question_view.php?id=" . htmlspecialchars($row['question_id']) . "'>" . htmlspecialchars($row['title']) . "</a> (Placed: " . htmlspecialchars($row['created_at']) . ")</li>";
    }
    echo "</ul>";
} else {
    echo "Error fetching bounties.";
}
?>