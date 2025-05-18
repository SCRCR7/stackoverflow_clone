<?php
session_start();
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once('connect.php');

// Fetch all answers by this user, with their question's title
$stmt = $pdo->prepare("
    SELECT a.id AS answer_id, a.body, a.created_at, q.id AS question_id, q.title AS question_title
    FROM answers a
    JOIN questions q ON a.question_id = q.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Answers</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', 'Inter', Arial, sans-serif;
        }
        .container {
            max-width: 820px;
            margin: 34px auto 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 16px #c8e6fa28;
            padding: 32px 34px 26px 34px;
        }
        h2 {
            color: #1976d2;
            margin-bottom: 30px;
            font-size: 2em;
        }
        .answer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .answer-list li {
            background: #fafdff;
            border: 1.5px solid #e3ecf5;
            border-radius: 11px;
            margin-bottom: 20px;
            padding: 18px 22px 12px 22px;
            box-shadow: 0 2px 6px #dbeafe16;
            transition: box-shadow .14s, border-color .13s;
            position: relative;
        }
        .answer-list li:hover {
            box-shadow: 0 6px 18px #a5d2ff25;
            border-color: #90caf9;
        }
        .answer-list strong {
            font-size: 1.13em;
            color: #1976d2;
        }
        .answer-list .meta {
            color: #8592a6;
            margin-top: 7px;
            font-size: 0.98em;
            display: block;
        }
        .answer-list .answer-actions {
            margin-top: 9px;
        }
        .answer-list .answer-actions a {
            text-decoration: none;
            color: #0a95ff;
            font-size: 0.98em;
            margin-right: 20px;
            font-weight: 500;
        }
        .answer-list .answer-actions a:hover {
            text-decoration: underline;
        }
        .empty-state {
            color: #b0b9c6;
            font-style: italic;
            background: #f4f8fb;
            border-radius: 7px;
            padding: 16px 18px;
            margin: 0 0 18px 0;
        }
        .back-link {
            color: #2264be;
            font-weight: 500;
            text-decoration: none;
            margin-top: 26px;
            display: inline-block;
            padding-left: 5px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        /* Tooltip for copy */
        .copy-btn {
            background: #f1f8e9;
            color: #388e3c;
            border: none;
            border-radius: 5px;
            padding: 3px 13px;
            font-size: 0.96em;
            margin-left: 13px;
            cursor: pointer;
            transition: background .12s;
        }
        .copy-btn:hover {
            background: #b9f6ca;
        }
        .copied-tooltip {
            display: none;
            position: absolute;
            right: 18px;
            top: 18px;
            background: #388e3c;
            color: #fff;
            padding: 3px 13px;
            border-radius: 6px;
            font-size: 0.97em;
            z-index: 10;
        }
        .show-tooltip {
            display: inline-block !important;
            animation: fadeInOut 2s;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; }
        }
        /* Responsive */
        @media (max-width: 700px) {
            .container { padding: 11px 2vw 18px 2vw; }
        }
    </style>
    <script>
        function copyAnswer(answerId) {
            var text = document.getElementById('answer-body-' + answerId).innerText;
            navigator.clipboard.writeText(text);
            var tooltip = document.getElementById('tooltip-' + answerId);
            tooltip.classList.add('show-tooltip');
            setTimeout(function() {
                tooltip.classList.remove('show-tooltip');
            }, 1800);
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>My Answers</h2>
        <?php if (empty($answers)): ?>
            <div class="empty-state">You have not answered any questions.</div>
        <?php else: ?>
            <ul class="answer-list">
                <?php foreach ($answers as $answer): ?>
                    <li>
                        <strong>
                            On: <a href="question_view.php?id=<?= $answer['question_id'] ?>">
                                <?= htmlspecialchars($answer['question_title']) ?>
                            </a>
                        </strong>
                        <button class="copy-btn" onclick="copyAnswer(<?= $answer['answer_id'] ?>)">Copy</button>
                        <span id="tooltip-<?= $answer['answer_id'] ?>" class="copied-tooltip">Copied!</span>
                        <div id="answer-body-<?= $answer['answer_id'] ?>" style="margin:9px 0 2px 0;"><?= nl2br(htmlspecialchars($answer['body'])) ?></div>
                        <span class="meta"><?= date('Y-m-d', strtotime($answer['created_at'])) ?></span>
                        <div class="answer-actions">
                            <a href="answer_edit.php?id=<?= $answer['answer_id'] ?>">Edit</a>
                            <a href="answer_delete.php?id=<?= $answer['answer_id'] ?>" onclick="return confirm('Are you sure you want to delete this answer?');">Delete</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <a href="profile.php" class="back-link">&larr; Back to profile</a>
    </div>
</body>
</html>