<?php
// Show PHP errors for debugging (remove in production!)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include_once('connect.php');

// Fetch user info
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found!");
}

// Calculate member duration
$createdAt = new DateTime($user['created_at']);
$now = new DateTime();
$memberFor = $now->diff($createdAt)->format('%a days');

// You can replace these with real calculations if you wish
$lastSeen = "recently";
$visitedDays = "N/A";

// Fetch user's questions
$questionsStmt = $pdo->prepare("SELECT id, title, created_at FROM questions WHERE user_id = ? ORDER BY created_at DESC");
$questionsStmt->execute([$_SESSION['user_id']]);
$questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's answers and their related question titles
$answersStmt = $pdo->prepare("
    SELECT a.id, a.body, a.created_at, q.id AS question_id, q.title AS question_title 
    FROM answers a 
    JOIN questions q ON a.question_id = q.id 
    WHERE a.user_id = ? 
    ORDER BY a.created_at DESC
");
$answersStmt->execute([$_SESSION['user_id']]);
$answers = $answersStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's articles
$articlesStmt = $pdo->prepare("SELECT id, title, created_at FROM articles WHERE user_id = ? ORDER BY created_at DESC");
$articlesStmt->execute([$_SESSION['user_id']]);
$articles = $articlesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's tags (from questions and answers)
$userTags = [];
try {
    $tagsStmt = $pdo->prepare("
        SELECT t.name, COUNT(*) as count
        FROM (
            SELECT qt.tag_id FROM questions q 
            JOIN question_tags qt ON qt.question_id = q.id 
            WHERE q.user_id = ?
            UNION ALL
            SELECT qt.tag_id FROM answers a
            JOIN questions q ON a.question_id = q.id
            JOIN question_tags qt ON qt.question_id = q.id
            WHERE a.user_id = ?
        ) AS user_tags
        JOIN tags t ON user_tags.tag_id = t.id
        GROUP BY t.name
        ORDER BY count DESC, t.name
    ");
    $tagsStmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $userTags = $tagsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $userTags = [];
}

// Fetch user's badges (if you have a badge system)
$badges = [];
try {
    $badgesStmt = $pdo->prepare("
        SELECT b.name, b.description, ub.earned_at
        FROM user_badges ub
        JOIN badges b ON ub.badge_id = b.id
        WHERE ub.user_id = ?
        ORDER BY ub.earned_at DESC
    ");
    $badgesStmt->execute([$_SESSION['user_id']]);
    $badges = $badgesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $badges = [];
}

// Fetch followed posts
$followedPosts = [];
try {
    $followedPostsStmt = $pdo->prepare("
        SELECT q.id, q.title, q.created_at
        FROM post_following pf
        JOIN questions q ON pf.question_id = q.id
        WHERE pf.follower_id = ?
        ORDER BY q.created_at DESC
    ");
    $followedPostsStmt->execute([$_SESSION['user_id']]);
    $followedPosts = $followedPostsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $followedPosts = [];
}

// Fetch users you follow
$following = [];
try {
    $followingStmt = $pdo->prepare("
        SELECT u.user_id, u.username, u.created_at
        FROM following f
        JOIN users u ON f.followee_id = u.user_id
        WHERE f.follower_id = ?
        ORDER BY u.username ASC
    ");
    $followingStmt->execute([$_SESSION['user_id']]);
    $following = $followingStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $following = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($user['username']) ?> - Stack Overflow Clone</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Inter', Arial, sans-serif;
        }
        .profile-container {
            display: flex;
            max-width: 1200px;
            margin: 38px auto 0 auto;
            gap: 36px;
        }
        .profile-sidebar {
            flex: 0 0 240px;
            background: #fff;
            border-radius: 13px;
            box-shadow: 0 2px 12px #dbeafe55;
            padding: 32px 18px 18px 18px;
            position: sticky;
            top: 36px;
            height: fit-content;
        }
        .profile-card {
            background: #fafdff;
            border-radius: 11px;
            box-shadow: 0 2px 6px #e0e7ef33;
            padding: 24px 20px 16px 22px;
            margin-bottom: 24px;
        }
        .profile-main {
            flex: 1;
            min-width: 0;
        }
        .profile-header {
            background: linear-gradient(90deg, #e3f0ff 10%, #f9fafc 100%);
            border-radius: 10px;
            padding: 30px 32px 22px 32px;
            margin-bottom: 28px;
            box-shadow: 0 2px 8px #90caf933;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .username-container {
            display: flex;
            align-items: center;
            gap: 22px;
            justify-content: space-between;
        }
        .username-container h1 {
            font-size: 2.1em;
            color: #206ecb;
            margin: 0;
        }
        .header-actions .logout-btn {
            color: #d32f2f;
            background: #f5e8e8;
            border-radius: 6px;
            padding: 7px 18px;
            text-decoration: none;
            font-weight: 600;
            transition: background .13s;
        }
        .header-actions .logout-btn:hover {
            background: #ffd6d6;
        }
        .logout-icon {
            margin-right: 7px;
            font-size: 1.1em;
        }
        .profile-meta {
            color: #6a7b8d;
            font-size: 1.03em;
            margin-left: 2px;
        }
        .profile-section {
            margin-bottom: 28px;
        }
        .profile-section h2 {
            margin-bottom: 10px;
            color: #1a4d89;
            font-size: 1.18em;
        }
        .profile-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .profile-card li {
            margin-bottom: 8px;
            padding: 0 0 6px 0;
        }
        .profile-card li:last-child {
            border-bottom: none;
        }
        .meta {
            color: #9eabb6;
            font-size: 0.99em;
            margin-left: 7px;
        }
        .empty-state {
            color: #b0b9c6;
            font-style: italic;
            background: #f4f8fb;
            border-radius: 7px;
            padding: 11px 13px;
            margin: 0 0 8px 0;
        }
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }
        .nav-menu li {
            margin-bottom: 6px;
        }
        .nav-menu a {
            color: #2264be;
            text-decoration: none;
            font-weight: 500;
            padding: 4px 0 4px 6px;
            display: inline-block;
            border-radius: 5px;
            transition: background .11s;
        }
        .nav-menu a:hover {
            background: #e7f0fa;
        }
        .profile-card ul li a {
            font-weight: 600;
            color: #1c5e8d;
        }
        .profile-card ul li a:hover {
            text-decoration: underline;
            color: #1479e5;
        }
        .profile-section .profile-card {
            border-left: 4px solid #e0e7ef;
            transition: border-color .13s;
        }
        .profile-section .profile-card:hover {
            border-left: 4px solid #91d3fb;
        }
        .badge-flare {
            display: inline-block;
            background: #ffe67d;
            color: #a98d00;
            border-radius: 9px;
            padding: 2px 11px 2px 9px;
            font-size: 0.94em;
            font-weight: 600;
            margin-left: 7px;
        }
        .tag-badge {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 6px;
            padding: 2px 11px;
            font-size: 1em;
            font-weight: 600;
            margin-right: 7px;
            margin-bottom: 3px;
        }
        .badge-earned {
            display: inline-block;
            background: #ffe67d;
            color: #a98d00;
            border-radius: 8px;
            padding: 2px 11px;
            font-size: 0.98em;
            font-weight: 600;
            margin-right: 7px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- Sidebar Navigation -->
        <aside class="profile-sidebar">
            <div class="profile-card">
                <ul class="nav-menu">
                    <li><a href="index.php">🏠 Home</a></li>
                    <li><a href="summary.php">📋 Summary</a></li>
                    <li><a href="answers.php">💬 Answers</a></li>
                    <li><a href="questions.php">❓ Questions</a></li>
                    <li><a href="tags.php">🏷️ Tags</a></li>
                    <li><a href="articles.php">📝 Articles</a></li>
                    <li><a href="badges.php">🏅 Badges</a></li>
                    <li><a href="following.php">👁️ Following</a></li>
                    <li><a href="bounties.php">💰 Bounties</a></li>
                    <li><a href="reputation.php">🌟 Reputation</a></li>
                    <li><a href="actions.php">⚡ All actions</a></li>
                    <li><a href="responses.php">📬 Responses</a></li>
                    <li><a href="votes.php">👍 Votes</a></li>
                </ul>
            </div>
            <div class="profile-card">
                <h3 style="margin-top:0;">Accounts</h3>
                <ul class="nav-menu">
                    <li><strong>Stack Overflow</strong></li>
                </ul>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="profile-main">
            <div class="profile-header">
                <div class="username-container">
                    <h1><?= htmlspecialchars($user['username']) ?></h1>
                    <div class="header-actions">
                        <a href="logout.php" class="logout-btn">
                            <span class="logout-icon">⎋</span> Log out
                        </a>
                    </div>
                </div>
                <div class="profile-meta">
                    <span title="How long you've been a member">👤 Member for <b><?= htmlspecialchars($memberFor) ?></b></span><br>
                    <span title="Last time you were seen active">🟢 Last seen <b><?= htmlspecialchars($lastSeen) ?></b></span><br>
                    <span title="Days you visited">📅 Visited <b><?= htmlspecialchars($visitedDays) ?></b></span>
                </div>
            </div>
            
            <!-- Summary Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Summary</h2>
                    <p style="font-weight:600;">Reputation is how the community thanks you!</p>
                    <p>When users upvote your helpful posts, you'll earn reputation and unlock new privileges.</p>
                    <div style="margin-bottom:7px;">
                        <a href="reputation.php" class="badge-flare" style="text-decoration:none;">Check your rep</a>
                        <a href="#" style="margin-left:11px;font-size:0.98em;color:#5095d8;">Learn more about reputation & privileges</a>
                    </div>
                </div>
            </section>
            
            <!-- Answers Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Answers</h2>
                    <?php if (empty($answers)): ?>
                        <div class="empty-state">You have not answered any questions.</div>
                    <?php else: ?>
                        <ul>
                        <?php foreach ($answers as $a): ?>
                            <li>
                                <span style="color:#388e3c;">Answered</span> 
                                <a href="question_view.php?id=<?= $a['question_id'] ?>">
                                    <?= htmlspecialchars($a['question_title']) ?>
                                </a>
                                <span class="meta">(<?= date('Y-m-d', strtotime($a['created_at'])) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Questions Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Questions</h2>
                    <?php if (empty($questions)): ?>
                        <div class="empty-state">You have not asked any questions.</div>
                    <?php else: ?>
                        <ul>
                        <?php foreach ($questions as $q): ?>
                            <li>
                                <span style="color:#1a4d89;">Asked</span> 
                                <a href="question_view.php?id=<?= $q['id'] ?>">
                                    <?= htmlspecialchars($q['title']) ?>
                                </a>
                                <span class="meta">(<?= date('Y-m-d', strtotime($q['created_at'])) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>
          
            <!-- Tags Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Tags</h2>
                    <?php if (empty($userTags)): ?>
                        <div class="empty-state">You have not participated in any tags.</div>
                    <?php else: ?>
                        <div>
                        <?php foreach ($userTags as $tag): ?>
                            <span style="display:inline-block;background:#e3f2fd;color:#1976d2;border-radius:6px;padding:2px 11px;font-size:1em;font-weight:600;margin-right:7px;margin-bottom:3px;">
                                <?= htmlspecialchars($tag['name']) ?> (<?= $tag['count'] ?>)
                            </span>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Badges Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Badges</h2>
                    <?php if (empty($badges)): ?>
                        <div class="empty-state">You have not earned any badges yet.</div>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($badges as $badge): ?>
                            <li>
                                <span style="display:inline-block;background:#ffe67d;color:#a98d00;border-radius:8px;padding:2px 11px;font-size:0.98em;font-weight:600;margin-right:7px;margin-bottom:3px;">
                                    <?= htmlspecialchars($badge['name']) ?>
                                </span>
                                <span class="meta">(<?= date('Y-m-d', strtotime($badge['earned_at'])) ?>)</span>
                                <span style="color:#7b6512;font-size:0.97em;"><?= htmlspecialchars($badge['description']) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Users You Follow Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Users You Follow</h2>
                    <?php if (empty($following)): ?>
                        <div class="empty-state">You are not following anyone yet.</div>
                    <?php else: ?>
                        <ul>
                        <?php foreach ($following as $follow): ?>
                            <li>
                                <a href="profile_view.php?id=<?= $follow['user_id'] ?>">
                                    <?= htmlspecialchars($follow['username']) ?>
                                </a>
                                <span class="meta">(joined <?= date('Y-m', strtotime($follow['created_at'])) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Followed posts Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Followed posts</h2>
                    <?php if (empty($followedPosts)): ?>
                        <div class="empty-state">You are not following any posts.</div>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($followedPosts as $fp): ?>
                                <li>
                                    <a href="question_view.php?id=<?= $fp['id'] ?>">
                                        <?= htmlspecialchars($fp['title']) ?>
                                    </a>
                                    <span class="meta">(<?= date('Y-m-d', strtotime($fp['created_at'])) ?>)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Bounties Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Active bounties (0)</h2>
                    <div class="empty-state">You have no active bounties.</div>
                </div>
            </section>
            
            <!-- Articles Section -->
            <section class="profile-section">
                <div class="profile-card">
                    <h2>Articles</h2>
                    <?php if (empty($articles)): ?>
                        <div class="empty-state">You have no published articles.</div>
                    <?php else: ?>
                        <ul>
                        <?php foreach ($articles as $article): ?>
                            <li>
                                <a href="article_view.php?id=<?= $article['id'] ?>">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                                <span class="meta">(<?= date('Y-m-d', strtotime($article['created_at'])) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>