<?php
session_start();
include 'header.php';

// Require login
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.html"); // Use login.html as per your setup
    exit; 
}

// Use InfinityFree DB credentials
$conn = new mysqli(
    'sql102.infinityfree.com',
    'if0_39013734',
    'sbnadmin4321',
    'if0_39013734_stackoverflow_clone'
);

if ($conn->connect_error) {
    die("<div style='color:red;'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</div>");
}

$user_id = $_SESSION['user_id'];

// Handle submission
$submit_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title']) && isset($_POST['body'])) {
    $stmt = $conn->prepare("INSERT INTO articles (user_id, title, body) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $user_id, $_POST['title'], $_POST['body']);
    if ($stmt->execute()) {
        $submit_message = "<div style='color:#2e7d32;background:#e8f5e9;padding:8px 15px;border-radius:6px;margin-bottom:12px;font-size:1.07em;'>🎉 Article posted successfully!</div>";
    } else {
        $submit_message = "<div style='color:#d32f2f;background:#ffebee;padding:8px 15px;border-radius:6px;margin-bottom:12px;font-size:1.07em;'>❌ Failed to post your article. Please try again.</div>";
    }
    $stmt->close();
}

// Fetch articles
$res = $conn->query("SELECT * FROM articles WHERE user_id=$user_id ORDER BY created_at DESC");

?>
<div style="max-width:700px;margin:38px auto 0 auto;padding:0 12px;">
    <h2 style="color:#007bff;margin-bottom:18px;">📝 Your Articles</h2>
    <?php echo $submit_message; ?>
    <div style="background:#f4f8fb;border:1.5px solid #007bff30;padding:22px 26px 18px 26px;border-radius:11px;box-shadow:0 2px 10px #007bff14;margin-bottom:32px;">
        <h3 style="margin-top:0;">Create a New Article</h3>
        <form method="post" style="margin-bottom:0;">
            <input name="title" placeholder="Title" required maxlength="120"
                   style="width:100%;padding:9px;font-size:1.09em;border:1.5px solid #90caf9;border-radius:5px;margin-bottom:10px;"><br>
            <textarea name="body" placeholder="Write your article..." required rows="7"
                      style="width:100%;padding:10px;font-size:1.09em;border:1.5px solid #90caf9;border-radius:5px;margin-bottom:10px;resize:vertical;"></textarea><br>
            <button type="submit" style="background:#007bff;color:white;font-size:1.07em;font-weight:bold;padding:9px 26px;border:none;border-radius:6px;cursor:pointer;box-shadow:0 2px 5px #007bff22;">
                ✍️ Submit Article
            </button>
        </form>
        <div style="color:#888;font-size:0.97em;margin-top:7px;">
            Tip: Use clear titles and break your article into paragraphs for best readability.<br>
            <span style="font-family:monospace;color:#1976d2;">&lt;code&gt; ... &lt;/code&gt;</span> for code sections!
        </div>
    </div>

    <h3 style="margin-bottom:14px;">Your Previous Articles</h3>
    <?php if ($res && $res->num_rows > 0): ?>
        <ul style="list-style:none;padding:0;">
        <?php while($row = $res->fetch_assoc()) { ?>
            <li style="background:#f8fafc;border:1px solid #b6c3d6;border-radius:8px;margin-bottom:18px;padding:17px 19px;box-shadow:0 2px 7px #90caf933;">
                <b style="font-size:1.17em;"><?php echo htmlspecialchars($row['title']); ?></b>
                <div style="color:#333;padding:7px 0 7px 0;white-space:pre-line;">
                    <?php echo nl2br(htmlspecialchars($row['body'])); ?>
                </div>
                <small style="color:#888;">Posted on <?php echo htmlspecialchars($row['created_at']); ?></small>
            </li>
        <?php } ?>
        </ul>
    <?php else: ?>
        <div style="color:#aaa;font-style:italic;">You haven't written any articles yet. Start sharing your knowledge!</div>
    <?php endif; ?>
</div>