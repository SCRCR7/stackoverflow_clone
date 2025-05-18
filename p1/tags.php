<?php
session_start();
include 'header.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// InfinityFree DB credentials
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

// Validate 'id'
if (!isset($_GET['id'])) die('No answer specified');

$id = intval($_GET['id']);
$row = $conn->query("SELECT * FROM answers WHERE id=$id")->fetch_assoc();

if (!$row || $_SESSION['user_id'] != $row['user_id']) die('Not authorized');

// Update answer
if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE answers SET body=? WHERE id=?");
    $stmt->bind_param('si', $_POST['body'], $id);
    $stmt->execute();
    header("Location: question_view.php?id=" . $row['question_id']);
    exit;
}

// Delete answer
if (isset($_POST['delete'])) {
    $conn->query("DELETE FROM answers WHERE id=$id");
    header("Location: question_view.php?id=" . $row['question_id']);
    exit;
}
?>
<form method="post">
    <textarea name="body"><?php echo htmlspecialchars($row['body']); ?></textarea><br>
    <button name="update" type="submit">Update</button>
    <button name="delete" type="submit" onclick="return confirm('Are you sure?')">Delete</button>
</form>