<?php
session_start();
include 'header.php';
$conn = new mysqli('localhost', 'root', '', 'stackoverflow_clone');
if (!isset($_GET['id'])) die('No answer specified');
$row = $conn->query("SELECT * FROM answers WHERE id=" . intval($_GET['id']))->fetch_assoc();
if (!$row || $_SESSION['user_id'] != $row['user_id']) die('Not authorized');
if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE answers SET body=? WHERE id=?");
    $stmt->bind_param('si', $_POST['body'], $_GET['id']);
    $stmt->execute();
    header("Location: question_view.php?id=" . $row['question_id']);
    exit;
}
if (isset($_POST['delete'])) {
    $conn->query("DELETE FROM answers WHERE id=" . intval($_GET['id']));
    header("Location: question_view.php?id=" . $row['question_id']);
    exit;
}
?>
<form method="post">
    <textarea name="body"><?php echo htmlspecialchars($row['body']); ?></textarea><br>
    <button name="update" type="submit">Update</button>
    <button name="delete" type="submit" onclick="return confirm('Are you sure?')">Delete</button>
</form>