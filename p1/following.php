<?php
session_start();
include 'header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// CHANGE TO YOUR REMOTE DATABASE IF NEEDED
$conn = new mysqli(
    'sql102.infinityfree.com', // or 'localhost'
    'if0_39013734',            // or 'root'
    'sbnadmin4321',            // or ''
    'if0_39013734_stackoverflow_clone' // or 'stackoverflow_clone'
);

$user_id = $_SESSION['user_id'];

// Fetch the list of users you follow
$res = $conn->query("SELECT u.user_id, u.username, u.created_at, u.email FROM following f JOIN users u ON f.followee_id=u.user_id WHERE f.follower_id=$user_id ORDER BY u.username ASC");

// Count of followers/following for you (for summary card)
$countFollowing = $conn->query("SELECT COUNT(*) as cnt FROM following WHERE follower_id=$user_id")->fetch_assoc()['cnt'];
$countFollowers = $conn->query("SELECT COUNT(*) as cnt FROM following WHERE followee_id=$user_id")->fetch_assoc()['cnt'];

// Handle unfollow action
if (isset($_GET['unfollow']) && is_numeric($_GET['unfollow'])) {
    $unfollow_id = intval($_GET['unfollow']);
    // Only unfollow if you actually follow this user
    $conn->query("DELETE FROM following WHERE follower_id=$user_id AND followee_id=$unfollow_id");
    header("Location: following.php");
    exit;
}

// Search for users to follow
$searchUsers = [];
if (isset($_GET['search']) && strlen(trim($_GET['search'])) > 1) {
    $search = $conn->real_escape_string($_GET['search']);
    $searchRes = $conn->query(
        "SELECT user_id, username 
         FROM users 
         WHERE username LIKE '%$search%' 
           AND user_id != $user_id
           AND user_id NOT IN (SELECT followee_id FROM following WHERE follower_id=$user_id)
         LIMIT 10"
    );
    while ($row = $searchRes->fetch_assoc()) $searchUsers[] = $row;
}

// Handle follow action
if (isset($_GET['follow']) && is_numeric($_GET['follow'])) {
    $follow_id = intval($_GET['follow']);
    // Prevent following yourself and duplicate follows
    if ($follow_id != $user_id && !$conn->query("SELECT 1 FROM following WHERE follower_id=$user_id AND followee_id=$follow_id")->fetch_assoc()) {
        $conn->query("INSERT INTO following (follower_id, followee_id) VALUES ($user_id, $follow_id)");
    }
    header("Location: following.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Following - Stack Overflow Clone</title>
    <link href="https://fonts.googleapis.com/css?family=Inter:400,600,700&display=swap" rel="stylesheet">
    <style>
        body { background: #f5f7fa; font-family: 'Inter', Arial, sans-serif; }
        .container { max-width: 730px; margin: 38px auto 0 auto; background: #fff; border-radius: 14px; box-shadow: 0 2px 16px #c8e6fa28; padding: 33px 34px 28px 34px; }
        h2 { color: #1976d2; font-size: 2em; margin-bottom: 28px; }
        .summary-card { background: #e6f3ff; border-radius: 10px; padding: 15px 20px 10px 20px; margin-bottom: 27px; box-shadow: 0 2px 7px #90caf933; }
        .summary-card span { display: inline-block; margin-right: 18px; font-weight: 600; color: #1976d2;}
        .following-list { list-style: none; padding: 0; margin: 0;}
        .following-list li { background: #fafdff; border: 1.5px solid #e3ecf5; border-radius: 10px; margin-bottom: 16px; padding: 16px 20px 12px 20px; box-shadow: 0 2px 6px #dbeafe11; display: flex; align-items: center; justify-content: space-between;}
        .following-list .user-meta { color: #8e9ab2; font-size: 0.98em;}
        .following-list .unfollow-btn { color: #d32f2f; background: #ffeaea; border: none; border-radius: 5px; padding: 5px 17px; font-weight: 600; cursor: pointer; font-size: 1em; transition: background .14s;}
        .following-list .unfollow-btn:hover { background: #ffd0d0;}
        .empty-state { color: #b0b9c6; font-style: italic; background: #f4f8fb; border-radius: 7px; padding: 12px 16px; margin: 0 0 18px 0;}
        .search-section { margin: 32px 0 0 0; }
        .search-box { width: 100%; padding: 10px; border-radius: 7px; border: 1.5px solid #b4d2f3; background: #fafdff; font-size: 1.07em; margin-bottom: 8px;}
        .search-btn { background: #1976d2; color: #fff; font-weight: 700; padding: 8px 26px; border: none; border-radius: 6px; cursor: pointer; font-size: 1.03em; box-shadow: 0 2px 7px #1976d215; transition: background .13s;}
        .search-btn:hover { background: #1256a3;}
        .search-results { margin-top: 10px;}
        .search-results li { background: #f6fff8; border: 1px solid #b7e4c7; border-radius: 7px; margin-bottom: 11px; padding: 10px 17px; display: flex; align-items: center; justify-content: space-between;}
        .follow-btn { background: #388e3c; color: #fff; border: none; border-radius: 5px; padding: 5px 17px; font-weight: 600; cursor: pointer; font-size: 1em; transition: background .14s;}
        .follow-btn:hover { background: #1b5e20; }
        @media (max-width: 650px) { .container { padding: 10px 2vw 14px 2vw; } }
        a.username-link { color: #1976d2; font-weight: 600; text-decoration: none; }
        a.username-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Users You Follow</h2>
    <div class="summary-card">
        <span>Following: <?= $countFollowing ?></span>
        <span>Followers: <?= $countFollowers ?></span>
        <span style="font-size:0.98em;color:#888;font-weight:400;">Tip: Following users lets you easily find their questions and answers.</span>
    </div>

    <?php if ($res->num_rows === 0): ?>
        <div class="empty-state">You are not following anyone yet.</div>
    <?php else: ?>
        <ul class="following-list">
        <?php while ($row = $res->fetch_assoc()): ?>
            <li>
                <div>
                    <a class="username-link" href="profile_view.php?id=<?= $row['user_id'] ?>">
                        <?= htmlspecialchars($row['username']) ?>
                    </a><br>
                    <span class="user-meta">Joined <?= date('M Y', strtotime($row['created_at'])) ?> | <?= htmlspecialchars($row['email']) ?></span>
                </div>
                <form method="get" style="margin:0;">
                    <input type="hidden" name="unfollow" value="<?= $row['user_id'] ?>">
                    <button type="submit" class="unfollow-btn" onclick="return confirm('Unfollow <?= htmlspecialchars($row['username']) ?>?');">Unfollow</button>
                </form>
            </li>
        <?php endwhile; ?>
        </ul>
    <?php endif; ?>

    <!-- SEARCH USERS TO FOLLOW -->
    <div class="search-section">
        <form method="get">
            <input class="search-box" type="text" name="search" placeholder="Find users to follow..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button class="search-btn" type="submit">Search</button>
        </form>
        <?php if (isset($_GET['search'])): ?>
            <div class="search-results">
            <?php if (empty($searchUsers)): ?>
                <div class="empty-state" style="background:#fffbe4;color:#c7a100;">No users found.</div>
            <?php else: ?>
                <ul>
                    <?php foreach ($searchUsers as $su): ?>
                    <li>
                        <span><?= htmlspecialchars($su['username']) ?></span>
                        <form method="get" style="margin:0;">
                            <input type="hidden" name="follow" value="<?= $su['user_id'] ?>">
                            <button class="follow-btn" type="submit">Follow</button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>