<?php
session_start();
include 'header.php';

// Use InfinityFree credentials
$conn = new mysqli(
    'sql102.infinityfree.com',
    'if0_39013734',
    'sbnadmin4321',
    'if0_39013734_stackoverflow_clone'
);

if ($conn->connect_error) {
    die('<div style="color:red;">Database connection failed: ' . htmlspecialchars($conn->connect_error) . '</div>');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('<div style="color:red;">No question specified.</div>');
}
$qid = intval($_GET['id']);

$q = $conn->query("SELECT q.*, u.username FROM questions q JOIN users u ON q.user_id=u.user_id WHERE q.id=$qid")->fetch_assoc();
if (!$q) die('<div style="color:red;">Question not found.</div>');

// Handle answer submission
if (isset($_POST['body']) && isset($_SESSION['user_id'])) {
    $body = trim($_POST['body']);
    if ($body) {
        $stmt = $conn->prepare("INSERT INTO answers (question_id, user_id, body) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $qid, $_SESSION['user_id'], $body);
        $stmt->execute();
        $stmt->close();
        // Redirect to prevent resubmission
        header("Location: question_view.php?id=$qid");
        exit;
    }
}

$answers = $conn->query("SELECT a.*, u.username FROM answers a JOIN users u ON a.user_id=u.user_id WHERE a.question_id=$qid ORDER BY a.created_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($q['title']); ?> - Question</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', 'Inter', Arial, sans-serif; }
        .container-main { max-width: 760px; margin: 38px auto 0 auto; }
        .question-card {
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px 28px 18px 28px;
            background: #f8fafc;
            box-shadow: 0 2px 8px #007bff18;
        }
        .question-header { color: #007bff; margin-bottom: 5px; }
        .question-body {
            white-space: pre-wrap;
            background: #f4f4f4;
            padding: 14px;
            border-radius: 7px;
            font-size: 1.09em;
            margin-bottom: 12px;
        }
        .question-meta {
            text-align: right;
            color: #555;
            margin-bottom: 0;
        }
        .question-meta small { color: #888; }
        .answers-section { margin-top: 32px; }
        .answers-section h3 { margin-top: 0; color: #388e3c; }
        .answer-list { list-style: none; padding: 0; }
        .answer-item {
            margin-bottom: 22px;
            padding: 13px 16px;
            background: #eafaf1;
            border-radius: 8px;
            box-shadow: 0 1px 5px #388e3c12;
            position: relative;
        }
        .answer-body { font-size: 1.07em; }
        .answer-meta {
            margin-top: 8px;
            color: #337a52;
            font-size: 0.97em;
        }
        .answer-actions {
            display: inline-block;
            margin-left: 10px;
        }
        .answer-actions a {
            color: #d32f2f;
            text-decoration: none;
            margin-left: 8px;
            font-size: 0.97em;
        }
        .answer-actions a:hover { text-decoration: underline; }
        .empty-state {
            color: #aaa;
            font-style: italic;
            margin: 10px 0 30px 0;
        }
        .answer-form-section { margin-top: 36px; }
        .answer-form-section h4 { margin-bottom: 7px; }
        .answer-form textarea {
            width: 100%;
            border: 1.5px solid #007bff;
            border-radius: 7px;
            padding: 10px;
            font-size: 1.06em;
            margin-bottom: 8px;
            background: #fafdff;
            resize: vertical;
        }
        .answer-form button {
            background: #007bff;
            color: white;
            font-weight: bold;
            padding: 8px 22px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.06em;
        }
        .answer-form button:hover { background: #005cbf; }
        .answer-tip {
            font-size: 0.95em;
            color: #888;
            margin-top: 8px;
        }
        .login-prompt {
            margin-top: 32px;
            color: #888;
            background: #f4f4f4;
            border-radius: 8px;
            padding: 12px 18px;
        }
        .login-prompt a { color: #007bff; }
        @media (max-width: 700px) {
            .container-main { padding: 0 6px; }
            .question-card { padding: 13px 7px 12px 10px; }
        }
    </style>
</head>
<body>
<div class="container-main">
    <!-- QUESTION CARD -->
    <div class="question-card">
        <h2 class="question-header"><?php echo htmlspecialchars($q['title']); ?></h2>
        <div class="question-body"><?php echo htmlspecialchars($q['body']); ?></div>
        <div class="question-meta">
            <small>— <?php echo htmlspecialchars($q['username']); ?>, 
                <span><?php echo htmlspecialchars($q['created_at']); ?></span>
            </small>
        </div>
    </div>
    <!-- ANSWERS SECTION -->
    <div class="answers-section">
        <h3>Answers</h3>
        <?php if ($answers->num_rows === 0): ?>
            <div class="empty-state">
                No answers yet. Be the first to sprinkle your wisdom 👇
            </div>
        <?php else: ?>
            <ul class="answer-list">
                <?php while($a = $answers->fetch_assoc()) { ?>
                    <li class="answer-item">
                        <div class="answer-body"><?php echo nl2br(htmlspecialchars($a['body'])); ?></div>
                        <div class="answer-meta">
                            by <?php echo htmlspecialchars($a['username']); ?>
                            <span style="color:#aaa;">on <?php echo htmlspecialchars($a['created_at']); ?></span>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $a['user_id']) { ?>
                                <span class="answer-actions">
                                    | <a href="answer_edit.php?id=<?php echo $a['id']; ?>">Edit/Delete</a>
                                </span>
                            <?php } ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        <?php endif; ?>
    </div>
    <!-- ANSWER FORM SECTION -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="answer-form-section">
            <h4>Your Answer</h4>
            <form method="post" class="answer-form">
                <textarea name="body" rows="5" required placeholder="Type your answer here..."></textarea><br>
                <button type="submit">Post Answer 🚀</button>
            </form>
            <div class="answer-tip">Remember: code samples and clear explanations help everyone! <span style="font-family:monospace;color:#444;">print('Good luck!')</span></div>
        </div>
    <?php else: ?>
        <div class="login-prompt">
            Please <a href="login.html">log in</a> to post an answer.
        </div>
    <?php endif; ?>
</div>
</body>
</html>