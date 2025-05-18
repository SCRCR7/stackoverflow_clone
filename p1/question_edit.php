<?php
session_start();
include 'header.php';

// Use correct DB credentials
$conn = new mysqli(
    'sql102.infinityfree.com', // host
    'if0_39013734',            // username
    'sbnadmin4321',            // password
    'if0_39013734_stackoverflow_clone' // database
);

if ($conn->connect_error) {
    die("<div style='color:red;'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</div>");
}

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if question ID is set
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('No question specified');
}
$question_id = intval($_GET['id']);

// Fetch question and authorize
$stmt = $conn->prepare("SELECT * FROM questions WHERE id=?");
$stmt->bind_param('i', $question_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die('Question not found.');
}
if ($_SESSION['user_id'] != $row['user_id']) {
    die('Not authorized.');
}

// Handle update
if (isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);
    if ($title && $body) {
        $stmt = $conn->prepare("UPDATE questions SET title=?, body=? WHERE id=?");
        $stmt->bind_param('ssi', $title, $body, $question_id);
        $stmt->execute();
        $stmt->close();
        header("Location: question_view.php?id=" . $question_id);
        exit;
    } else {
        $message = "<div style='color:red;'>Title and body cannot be empty.</div>";
    }
}

// Handle delete
if (isset($_POST['delete'])) {
    // Also delete answers to this question
    $conn->query("DELETE FROM answers WHERE question_id=" . $question_id);
    $conn->query("DELETE FROM questions WHERE id=" . $question_id);
    header("Location: questions.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Question</title>
    <style>
        body { background: #f6f8fa; font-family: 'Inter', Arial, sans-serif;}
        .edit-container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 18px #c8e6fa28; padding: 32px; }
        input, textarea { width: 100%; font-size: 1.08em; margin-bottom: 18px; padding: 9px; border-radius: 6px; border: 1.5px solid #b4d2f3; background: #fafdff; }
        input:focus, textarea:focus { border-color: #1976d2; outline: none; }
        button { background: #1976d2; color: #fff; font-weight: 700; padding: 9px 25px; border: none; border-radius: 6px; cursor: pointer; font-size: 1.07em; margin-right: 12px;}
        button.delete { background: #d32f2f; }
        button:hover { opacity: 0.93;}
    </style>
</head>
<body>
<div class="edit-container">
    <h2>Edit Question</h2>
    <?php if (!empty($message)) echo $message; ?>
    <form method="post">
        <label>Title:</label>
        <input name="title" value="<?php echo htmlspecialchars($row['title']); ?>" maxlength="120" required>
        <label>Body:</label>
        <textarea name="body" rows="7" required><?php echo htmlspecialchars($row['body']); ?></textarea>
        <br>
        <button name="update" type="submit">Update</button>
        <button name="delete" type="submit" class="delete" onclick="return confirm('Are you sure you want to delete this question and all its answers?')">Delete</button>
    </form>
    <br>
    <a href="question_view.php?id=<?php echo $question_id; ?>" style="color:#1976d2;">&larr; Back to question</a>
</div>
</body>
</html>