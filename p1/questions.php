<?php
session_start();
include 'header.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Use correct InfinityFree DB credentials
$conn = new mysqli(
    'sql102.infinityfree.com',
    'if0_39013734',
    'sbnadmin4321',
    'if0_39013734_stackoverflow_clone'
);

// Check connection
if ($conn->connect_error) {
    die("<div style='color:red;'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</div>");
}

// Handle deletion
$submissionMsg = "";
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $qid = intval($_GET['delete']);
    // Only allow delete if the user owns the question
    $check = $conn->prepare("SELECT user_id FROM questions WHERE id=?");
    $check->bind_param('i', $qid);
    $check->execute();
    $check->bind_result($owner_id);
    $check->fetch();
    $check->close();
    if ($owner_id == $_SESSION['user_id']) {
        // Also delete answers to the question (to maintain integrity)
        $conn->query("DELETE FROM answers WHERE question_id=$qid");
        // Now delete question
        $conn->query("DELETE FROM questions WHERE id=$qid");
        $submissionMsg = "<div style='color:#d32f2f;background:#ffebee;padding:10px 18px;margin-bottom:16px;border-radius:6px;font-size:1.08em;'>🗑️ Question deleted.</div>";
    } else {
        $submissionMsg = "<div style='color:#d32f2f;background:#ffebee;padding:10px 18px;margin-bottom:16px;border-radius:6px;font-size:1.08em;'>❌ You can only delete your own questions.</div>";
    }
}

// Handle question submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && isset($_POST['body'])) {
    $stmt = $conn->prepare("INSERT INTO questions (user_id, title, body) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $_SESSION['user_id'], $_POST['title'], $_POST['body']);
    if ($stmt->execute()) {
        $submissionMsg = "<div style='color:#2e7d32;background:#e8f5e9;padding:10px 18px;margin-bottom:16px;border-radius:6px;font-size:1.08em;'>🎉 Your question was posted!</div>";
    } else {
        $submissionMsg = "<div style='color:#d32f2f;background:#ffebee;padding:10px 18px;margin-bottom:16px;border-radius:6px;font-size:1.08em;'>❌ Failed to post question. Try again!</div>";
    }
    $stmt->close();
}

// Get questions
$result = $conn->query("SELECT q.id, q.title, q.body, q.created_at, u.username, q.user_id FROM questions q JOIN users u ON q.user_id=u.user_id ORDER BY q.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Questions - Stack Overflow Clone</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Inter', Arial, sans-serif;
        }
        .container {
            max-width: 870px;
            margin: 32px auto 0 auto;
            background: #fff;
            border-radius: 13px;
            box-shadow: 0 2px 18px #c8e6fa28;
            padding: 32px 36px 36px 36px;
        }
        .question-form {
            background: #f4f9fd;
            padding: 24px 22px 16px 22px;
            border-radius: 10px;
            box-shadow: 0 2px 7px #90caf933;
            margin-bottom: 34px;
        }
        .question-form h2 {
            margin-top: 0;
            color: #1976d2;
        }
        .question-form input, .question-form textarea {
            width: 100%;
            padding: 10px;
            font-size: 1.08em;
            border: 1.5px solid #b4d2f3;
            border-radius: 6px;
            margin-bottom: 12px;
            background: #fafdff;
            transition: border-color .13s;
        }
        .question-form input:focus, .question-form textarea:focus {
            border-color: #1976d2;
            outline: none;
        }
        .question-form button {
            background: #1976d2;
            color: #fff;
            font-weight: 700;
            padding: 10px 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.09em;
            box-shadow: 0 2px 7px #1976d215;
            transition: background .13s;
        }
        .question-form button:hover {
            background: #1256a3;
        }
        .questions-list-section {
            margin-top: 28px;
        }
        .question-card {
            background: #fafdff;
            border: 1.5px solid #e3ecf5;
            border-radius: 11px;
            margin-bottom: 20px;
            padding: 20px 22px 16px 22px;
            box-shadow: 0 2px 6px #dbeafe11;
            transition: box-shadow .14s;
            position: relative;
        }
        .question-card:hover {
            box-shadow: 0 6px 22px #1976d218;
            border-color: #90caf9;
        }
        .question-title {
            font-size: 1.15em;
            font-weight: 700;
            color: #1256a3;
            margin-bottom: 3px;
            text-decoration: none;
            transition: color .12s;
        }
        .question-title:hover {
            color: #1976d2;
            text-decoration: underline;
        }
        .question-snippet {
            color: #444;
            font-size: 1.03em;
            margin-bottom: 6px;
            white-space: pre-line;
        }
        .question-meta {
            color: #7a8699;
            font-size: 0.97em;
        }
        .question-actions {
            position: absolute;
            top: 16px;
            right: 21px;
        }
        .question-actions a {
            text-decoration: none;
            color: #1e88e5;
            margin-left: 12px;
            font-size: 1.01em;
            font-weight: 600;
        }
        .question-actions a.delete {
            color: #d32f2f;
        }
        .question-actions a:hover {
            text-decoration: underline;
        }
        @media (max-width: 700px) {
            .container { padding: 10px 3vw 18px 3vw; }
            .question-form, .question-card { padding: 12px 7px 12px 10px; }
            .question-actions { top: 9px; right: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php echo $submissionMsg; ?>
        <form class="question-form" method="post">
            <h2>Ask a Question</h2>
            <input name="title" placeholder="e.g. How do I reverse a linked list in C?" maxlength="120" required>
            <textarea name="body" placeholder="Describe your problem in detail. Include code, error messages, and what you tried." rows="6" required></textarea>
            <button type="submit">Submit Question 🚀</button>
            <div style="color:#888;font-size:0.96em;margin-top:8px;">
                Tip: Use clear titles and include a code sample for better answers!
            </div>
        </form>
        
        <div class="questions-list-section">
            <h2 style="color:#1976d2;">Latest Questions</h2>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()) { ?>
                    <div class="question-card">
                        <a class="question-title" href="question_view.php?id=<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </a>
                        <div class="question-snippet">
                            <?php
                            // Show a preview of the body (max 140 chars, avoid breaking words)
                            $snippet = strip_tags($row['body']);
                            if (strlen($snippet) > 140) {
                                $snippet = substr($snippet, 0, 137);
                                $lastSpace = strrpos($snippet, ' ');
                                if ($lastSpace !== false) $snippet = substr($snippet, 0, $lastSpace);
                                echo htmlspecialchars($snippet) . "…";
                            } else {
                                echo htmlspecialchars($snippet);
                            }
                            ?>
                        </div>
                        <div class="question-meta">
                            Asked by <b><?php echo htmlspecialchars($row['username']); ?></b> • <?php echo date("M d, Y H:i", strtotime($row['created_at'])); ?>
                        </div>
                        <?php if ($_SESSION['user_id'] == $row['user_id']) { ?>
                            <div class="question-actions">
                                <a href="question_edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <a href="questions.php?delete=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this question? This will also delete all its answers.');">Delete</a>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php else: ?>
                <div style="color:#b0b9c6;font-style:italic;">No questions yet. Be the first to ask!</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>