<?php
session_start();
include 'header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit; }

// Use InfinityFree DB credentials (update if needed)
$conn = new mysqli(
    'sql102.infinityfree.com',
    'if0_39013734',
    'sbnadmin4321',
    'if0_39013734_stackoverflow_clone'
);

if ($conn->connect_error) {
    die("<div style='color:red'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</div>");
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT username, email, created_at FROM users WHERE user_id=$user_id")->fetch_assoc();
$q_count = $conn->query("SELECT COUNT(*) AS c FROM questions WHERE user_id=$user_id")->fetch_assoc()['c'];
$a_count = $conn->query("SELECT COUNT(*) AS c FROM answers WHERE user_id=$user_id")->fetch_assoc()['c'];
$reputation = ($a_count * 10) + ($q_count * 5);

// Add a fun code-style box and a little joke
$joke = <<<HTML
<div style="background:#f4f4f4; border-left:6px solid #007bff; padding:18px; margin-bottom:15px; font-family:monospace; font-size:1.06em;">
    <span style="color:#007bff;font-weight:bold;">// Fun Fact:</span><br>
    <span style="color:#333;">if (<span style="color:#795548;">user</span>.answers > 0) {"<span style="color:#388e3c;">You're making the world a better place!</span>"} else {"<span style="color:#d32f2f;">Ask more questions, help more coders!</span>"}</span>
</div>
HTML;

echo "<h2 style='color:#007bff;'>👤 Profile Summary</h2>";
echo $joke;
if ($user) {
    echo "<table style='border-collapse:collapse;margin-bottom:16px;'>";
    echo "<tr><td style='padding:6px 16px 6px 0;font-weight:bold;'>Username:</td><td style='padding:6px;'>" . htmlspecialchars($user['username']) . "</td></tr>";
    echo "<tr><td style='padding:6px 16px 6px 0;font-weight:bold;'>Email:</td><td style='padding:6px;'>" . htmlspecialchars($user['email']) . "</td></tr>";
    echo "<tr><td style='padding:6px 16px 6px 0;font-weight:bold;'>Member since:</td><td style='padding:6px;'>" . htmlspecialchars($user['created_at']) . "</td></tr>";
    echo "<tr><td style='padding:6px 16px 6px 0;font-weight:bold;'>Questions asked:</td><td style='padding:6px;'>$q_count</td></tr>";
    echo "<tr><td style='padding:6px 16px 6px 0;font-weight:bold;'>Answers given:</td><td style='padding:6px;'>$a_count</td></tr>";
    echo "<tr><td style='padding:6px 16px 6px 0;font-weight:bold;'>Reputation:</td><td style='padding:6px;'>$reputation <span style='color:#fbc02d;' title='10/answer, 5/question'>★</span></td></tr>";
    echo "</table>";
    echo "<div style='color:#888; font-size:0.97em;'>Keep asking, answering, and growing your StackOverflow Clone legend!</div>";
} else {
    echo "<div style='color:red'>Could not load user data.</div>";
}
?>