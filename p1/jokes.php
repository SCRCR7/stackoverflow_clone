<?php
session_start();
include 'header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.html"); exit; }

// Use InfinityFree credentials if needed
$conn = new mysqli(
    'sql102.infinityfree.com',             // host
    'if0_39013734',                        // username
    'sbnadmin4321',                        // password
    'if0_39013734_stackoverflow_clone'     // database
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// JOKE!
$joke = <<<HTML
<div style="background: #fff7c0; border: 1px solid #ffe066; color: #6c4400; padding: 18px; border-radius: 8px; margin-bottom: 20px; font-size: 1.1em;">
    <b>🧙 Programmer Joke of the Day:</b><br>
    A programmer's spouse tells them, "Run to the store and pick up a loaf of bread. If they have eggs, get a dozen."<br>
    The programmer comes home with 12 loaves of bread.<br>
    <small>(Because, you know... if-then logic!)</small>
</div>
HTML;

echo $joke;

$res = $conn->query("
SELECT a.*, u.username AS answerer, q.title 
FROM answers a 
JOIN questions q ON a.question_id = q.id 
JOIN users u ON a.user_id = u.user_id 
WHERE q.user_id = $user_id 
ORDER BY a.created_at DESC
");
echo "<h2>Responses (Answers to Your Questions)</h2>";
if ($res->num_rows === 0) {
    echo "<div style='color:#888;font-style:italic'>No responses yet...<br>
    (Maybe your questions are so perfect, nobody dares answer! Or maybe everyone’s still debugging. 🐞)</div>";
} else {
    echo "<ul>";
    while ($row = $res->fetch_assoc()) {
        echo "<li>";
        echo "<b>" . htmlspecialchars($row['answerer']) . "</b> answered your question ";
        echo "<a href='question_view.php?id=" . htmlspecialchars($row['question_id']) . "'>" . htmlspecialchars($row['title']) . "</a>:<br>";
        echo "<div style='margin-left:20px'>" . nl2br(htmlspecialchars($row['body'])) . "</div>";
        echo "<small>Answered on " . htmlspecialchars($row['created_at']) . "</small>";
        echo "<br><span style='color:#aaa;font-size:0.95em'>P.S.: If the answer is 42, you know it's correct. 😉</span>";
        echo "</li>";
    }
    echo "</ul>";
}
?>