<?php
session_start();
include 'header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$conn = new mysqli('localhost', 'root', '', 'stackoverflow_clone');
$user_id = $_SESSION['user_id'];
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
    echo "No responses yet.";
} else {
    echo "<ul>";
    while ($row = $res->fetch_assoc()) {
        echo "<li>";
        echo "<b>" . htmlspecialchars($row['answerer']) . "</b> answered your question ";
        echo "<a href='question_view.php?id={$row['question_id']}'>" . htmlspecialchars($row['title']) . "</a>:<br>";
        echo "<div style='margin-left:20px'>" . nl2br(htmlspecialchars($row['body'])) . "</div>";
        echo "<small>Answered on {$row['created_at']}</small>";
        echo "</li>";
    }
    echo "</ul>";
}
?>