<?php
session_start();
include_once('connect.php');

// Tab logic for question feeds
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'newest';

switch ($tab) {
    case 'unanswered':
        $questions = $pdo->query("
            SELECT q.id, q.title, q.created_at, u.username,
                (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) AS answers_count
            FROM questions q
            JOIN users u ON q.user_id = u.user_id
            WHERE (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) = 0
            ORDER BY q.created_at DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'hot':
        $questions = $pdo->query("
            SELECT q.id, q.title, q.created_at, u.username,
                (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) AS answers_count
            FROM questions q
            JOIN users u ON q.user_id = u.user_id
            ORDER BY answers_count DESC, q.created_at DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
        break;
    default:
        $questions = $pdo->query("
            SELECT q.id, q.title, q.created_at, u.username,
                (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) AS answers_count
            FROM questions q
            JOIN users u ON q.user_id = u.user_id
            ORDER BY q.created_at DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch top tags (limit 12)
$tags = $pdo->query("
    SELECT t.id, t.name, COUNT(qt.question_id) as usage_count
    FROM tags t
    LEFT JOIN question_tags qt ON t.id = qt.tag_id
    GROUP BY t.id, t.name
    ORDER BY usage_count DESC
    LIMIT 12
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch site stats
$totalQuestions = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAnswers = $pdo->query("SELECT COUNT(*) FROM answers")->fetchColumn();

// Fetch recent users
$recentUsers = $pdo->query("SELECT username, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Fun Fact/Joke (randomized)
$jokes = [
    "Why do programmers prefer dark mode? Because light attracts bugs!",
    "A SQL query walks into a bar, approaches two tables and asks: 'Can I join you?'",
    "There are only 10 types of people: those who understand binary and those who don't.",
    "Why did the developer go broke? Because he used up all his cache.",
    "Why do Java developers wear glasses? Because they can't C#."
];
$joke = $jokes[array_rand($jokes)];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home - Stack Overflow Clone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Inter', Arial, sans-serif;}
        .welcome-banner { background: #f48024; color: #fff; padding: 2.2rem 1rem; border-radius: 8px; position: relative; }
        .search-bar { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border-radius: 8px; }
        .tag-badge { background: #e1ecf4; color: #39739d; margin-right: 0.5em; margin-bottom: 0.5em; font-size: 1em;}
        .stats-card { background: #fff; border-radius: 8px; box-shadow:0 2px 10px rgba(0,0,0,0.07); padding: 1.2rem 1rem; margin-bottom: 1rem;}
        .question-list li { margin-bottom: 1.5rem; }
        .ask-btn { background: #0a95ff; color: #fff; border-radius: 4px; padding: 0.65em 1.2em; font-weight: 500; }
        .ask-btn:hover { background: #0074cc; color: #fff; }
        .tab-link { text-decoration: none; color: #444; padding: 0.7rem 1.2rem; border-radius: 24px 24px 0 0; font-weight: 500;}
        .tab-link.active, .tab-link:hover { background: #f48024; color: #fff; }
        .sidebar { background: #fff; border-radius: 8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); padding: 1.3rem 1rem; }
        .sidebar .section-title { font-size: 1.15rem; font-weight: 600; margin-bottom: 0.8rem;}
        .hot-meta { background: #fff3cd; color: #856404; border-radius: 6px; padding: 0.8rem 1rem; margin-bottom: 1rem;}
        .header-actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }
        .joke-card {
            background: #fffde7;
            border-left: 6px solid #ffe082;
            padding: 1rem 1.3rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 1.09em;
            color: #694800;
            box-shadow: 0 2px 6px #ffecb366;
        }
        .recent-users-section {
            background: #f7fafc;
            border-radius: 8px;
            padding: 1rem 1.1rem;
            margin-bottom: 1.2rem;
        }
        .recent-users-section .user-badge {
            display: inline-block;
            margin-right: 9px;
            margin-bottom: 7px;
            padding: 0.38em 1.1em;
            border-radius: 16px;
            background: #e0f7fa;
            color: #007c91;
            font-weight: 500;
            font-size: 1em;
        }
        .question-list .btn-vote {
            background: #f3f4f6;
            color: #0a95ff;
            border: none;
            border-radius: 6px;
            padding: 0.2em 0.7em;
            margin-right: 7px;
            font-size: 1.11em;
            font-weight: 600;
            transition: background 0.1s;
        }
        .question-list .btn-vote:hover {
            background: #e3f2fd;
        }
        @media (max-width: 991px) {
            .sidebar {margin-top:2rem;}
        }
        @media (max-width: 767px) {
            .header-actions-bar {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }
            .welcome-banner {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
    <script>
        // Upvote/Downvote animation (demo only, no backend)
        function vote(btn, up) {
            let span = btn.parentNode.querySelector('.vote-count');
            let count = parseInt(span.innerText);
            span.innerText = up ? count + 1 : Math.max(0, count - 1);
            btn.classList.add('active-vote');
            setTimeout(() => btn.classList.remove('active-vote'), 350);
        }
    </script>
</head>
<body>
    <div class="container my-4 position-relative">
        <!-- Header Actions Bar -->
        <div class="header-actions-bar mb-2">
            <a href="profile.php" class="btn btn-outline-secondary">
                &larr; Back to Profile
            </a>
            <a href="questions.php" class="ask-btn">Ask a Question</a>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner mb-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div>
                <h2>Welcome to Stack Overflow Clone!</h2>
                <p class="mb-0">Get unstuck — ask questions, find answers, and share your knowledge.</p>
            </div>
        </div>
        
        <!-- Fun Joke Card -->
        <div class="joke-card mb-3">
            <b>😂 Developer Joke:</b> <?= $joke ?>
        </div>

        <div class="row">
            <!-- Main content -->
            <div class="col-lg-8">
                <!-- Search Bar -->
                <div class="search-bar p-3 mb-4">
                    <form action="search.php" method="get" class="d-flex" role="search">
                        <input type="text" name="q" class="form-control me-2" placeholder="Search for questions, tags, users..." aria-label="Search" required>
                        <button class="btn btn-primary" type="submit">Search</button>
                    </form>
                </div>

                <!-- Tabs for question feeds -->
                <ul class="nav mb-3">
                    <li class="nav-item"><a class="tab-link<?= $tab == 'newest' ? ' active' : '' ?>" href="?tab=newest">Newest</a></li>
                    <li class="nav-item"><a class="tab-link<?= $tab == 'unanswered' ? ' active' : '' ?>" href="?tab=unanswered">Unanswered</a></li>
                    <li class="nav-item"><a class="tab-link<?= $tab == 'hot' ? ' active' : '' ?>" href="?tab=hot">Hot</a></li>
                </ul>

                <!-- Question List -->
                <div>
                    <ul class="list-unstyled question-list">
                        <?php foreach ($questions as $q): ?>
                            <li class="p-3 bg-white rounded shadow-sm">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <a href="question_view.php?id=<?= $q['id'] ?>" class="fs-5 fw-semibold text-decoration-none"><?= htmlspecialchars($q['title']) ?></a>
                                    <span class="badge bg-secondary"><?= $q['answers_count'] ?> answer<?= $q['answers_count']==1?'':'s' ?></span>
                                </div>
                                <div class="mb-2 d-flex align-items-center">
                                    <button class="btn-vote me-1" onclick="vote(this, true)" aria-label="Upvote">&#9650;</button>
                                    <span class="vote-count" style="min-width:2em;">0</span>
                                    <button class="btn-vote ms-1" onclick="vote(this, false)" aria-label="Downvote">&#9660;</button>
                                    <span class="text-muted small ms-2">
                                        Asked by <?= htmlspecialchars($q['username']) ?> &bull; <?= date('M j, Y', strtotime($q['created_at'])) ?>
                                    </span>
                                </div>
                                <!-- Question tags -->
                                <?php
                                $tagStmt = $pdo->prepare("
                                    SELECT t.name FROM tags t
                                    JOIN question_tags qt ON t.id = qt.tag_id
                                    WHERE qt.question_id = ?
                                ");
                                $tagStmt->execute([$q['id']]);
                                $qTags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);
                                foreach($qTags as $tag):
                                ?>
                                    <a href="tag.php?name=<?= urlencode($tag) ?>" class="badge tag-badge"><?= htmlspecialchars($tag) ?></a>
                                <?php endforeach; ?>
                                <div class="mt-2">
                                    <a href="question_view.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-outline-primary me-1">View</a>
                                    <a href="answers.php?question_id=<?= $q['id'] ?>" class="btn btn-sm btn-primary">Answer</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="questions.php" class="btn btn-link">View all questions &rarr;</a>
                </div>
            </div>
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar mb-4">
                    <div class="section-title">Top Tags</div>
                    <?php foreach ($tags as $tag): ?>
                        <a href="tag.php?name=<?= urlencode($tag['name']) ?>" class="badge tag-badge">
                            <?= htmlspecialchars($tag['name']) ?> <span class="badge bg-secondary"><?= $tag['usage_count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="sidebar mb-4">
                    <div class="section-title">Site Stats</div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">Total Questions <span><?= $totalQuestions ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Total Answers <span><?= $totalAnswers ?></span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Total Users <span><?= $totalUsers ?></span></li>
                    </ul>
                </div>
                <div class="sidebar mb-4 recent-users-section">
                    <div class="section-title">Recent Users</div>
                    <?php foreach ($recentUsers as $ru): ?>
                        <span class="user-badge">
                            <?= htmlspecialchars($ru['username']) ?>
                            <small style="color:#6aacb7; font-size:0.95em;">(<?= date('M j', strtotime($ru['created_at'])) ?>)</small>
                        </span>
                    <?php endforeach; ?>
                </div>
                <div class="hot-meta">
                    <div class="fw-bold mb-2">Hot Meta Posts</div>
                    <ul class="mb-0 ps-3">
                        <li><a href="#" class="text-decoration-none">How do I ask a good question?</a></li>
                        <li><a href="#" class="text-decoration-none">Stack Overflow community guidelines</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>