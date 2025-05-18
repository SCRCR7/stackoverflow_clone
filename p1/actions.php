<?php
session_start();
include 'header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$conn = new mysqli('localhost', 'root', '', 'stackoverflow_clone');
$user_id = $_SESSION['user_id'];
$activities = [];
$q = $conn->query("SELECT 'question' AS type, id, title AS content, created_at FROM questions WHERE user_id=$user_id");
while ($row = $q->fetch_assoc()) $activities[] = $row;
$a = $conn->query("SELECT 'answer' AS type, question_id AS id, body AS content, created_at FROM answers WHERE user_id=$user_id");
while ($row = $a->fetch_assoc()) $activities[] = $row;
usort($activities, function($a, $b){ return strtotime($b['created_at']) - strtotime($a['created_at']); });
echo "<h2>All Actions</h2>";
if (empty($activities)) {
    echo "No actions yet.";
} else {
    echo "<ul>";
    foreach ($activities as $act) {
        if ($act['type'] === 'question') {
            echo "<li>Asked a question: <a href='question_view.php?id={$act['id']}'>" . htmlspecialchars($act['content']) . "</a> on {$act['created_at']}</li>";
        } else {
            echo "<li>Answered a question: <a href='question_view.php?id={$act['id']}'>View</a> on {$act['created_at']}</li>";
        }
    }
    echo "</ul>";
}
?>r