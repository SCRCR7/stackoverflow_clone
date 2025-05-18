<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>StackOverflow Clone</title>
    <link rel="stylesheet" href="style.css">
    <style>
      html, body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        width: 100%;
        overflow-x: hidden;
      }
      body {
        background: #f6f7fa;
        font-family: 'Inter', Arial, sans-serif;
      }
      header {
        width: 100%;
        background: linear-gradient(90deg, #2673b8 65%, #43b0e0 100%);
        color: #fff;
        box-shadow: 0 2px 12px #2a5da720;
      }
      .header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 1200px;
        width: 100%;
        padding: 18px 18px 12px 18px;
        margin: 0 auto;
      }
      .logo-box {
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .logo-icon {
        background: #fff;
        color: #2673b8;
        border-radius: 10px;
        font-size: 1.65em;
        font-weight: 700;
        padding: 4px 10px 4px 8px;
        box-shadow: 0 2px 8px #2673b822;
        margin-right: 6px;
      }
      header h1 {
        font-size: 1.35em;
        margin: 0;
        font-weight: 800;
        color: #fff;
        letter-spacing: 0.5px;
        text-shadow: 0 1px 8px #2673b815;
        background: none;
        padding: 0 6px;
        border-radius: 5px;
      }
      .user-nav {
        display: flex;
        align-items: center;
        gap: 13px;
      }
      .user-nav span {
        font-size: 1em;
        font-weight: 500;
        color: #effaff;
        letter-spacing: .05em;
        background: #2f81bf;
        padding: 5px 11px 4px 11px;
        border-radius: 7px;
        box-shadow: 0 1px 6px #fff2;
      }
      .user-nav .logout-link, .user-nav .login-link {
        color: #2370b2;
        background: #fff;
        border-radius: 7px;
        padding: 7px 16px;
        text-decoration: none;
        font-weight: 700;
        font-size: 1em;
        margin-left: 4px;
        box-shadow: 0 2px 8px #2673b81a;
        border: none;
        transition: background .14s, color .11s, box-shadow .14s;
        outline: none;
        cursor: pointer;
        letter-spacing: 0.02em;
      }
      .user-nav .logout-link:hover, .user-nav .login-link:hover {
        background: #ffe082;
        color: #174067;
        box-shadow: 0 6px 16px #2673b82d;
      }
      nav {
        width: 100%;
        background: #eaf3fa;
        box-shadow: 0 2px 8px #b1caf522;
        margin-bottom: 18px;
        border-bottom: 1.2px solid #c3dbe7;
      }
      nav ul {
        display: flex;
        flex-wrap: wrap;
        list-style: none;
        margin: 0 auto;
        padding: 0 0 0 12px;
        max-width: 1200px;
        width: 100%;
        /* Remove vertical scrollbar by hiding vertical overflow */
        overflow-y: hidden;
      }
      nav ul li {
        margin: 0 1px;
      }
      nav ul li a {
        display: inline-block;
        padding: 10px 13px 9px 13px;
        color: #2673b8;
        font-weight: 600;
        border-radius: 7px 7px 0 0;
        text-decoration: none;
        font-size: 1em;
        letter-spacing: 0.01em;
        margin-bottom: -2px;
        border-bottom: 2px solid transparent;
        transition: background .13s, color .13s, border-bottom 0.13s;
      }
      nav ul li a.active, nav ul li a:hover {
        background: #fff;
        color: #174067;
        border-bottom: 2px solid #2673b8;
        font-weight: 700;
        box-shadow: 0 2px 8px #b8e7ff30;
      }
      .container {
        max-width: 1100px;
        width: 96vw;
        margin: 30px auto 0 auto;
        padding: 26px 18px 24px 18px;
        background: #fafdff;
        border-radius: 10px;
        box-shadow: 0 2px 12px #cbeafd22;
      }
      /* Prevent horizontal overflow */
      html, body, header, nav {
        overflow-x: hidden !important;
      }
      @media (max-width: 800px) {
        .header-inner, nav ul {
          max-width: 100vw;
          padding-left: 4vw;
          padding-right: 4vw;
        }
        .container {padding: 11vw 2vw;}
      }
      @media (max-width:500px) {
        .header-inner {
          flex-direction: column;
          align-items: flex-start;
          padding: 13px 3vw 8px 3vw;
        }
        .logo-box { margin-bottom: 6px;}
        nav ul {padding-left:2vw;}
        .container {padding: 6vw 1vw;}
      }
    </style>
</head>
<body>
<header>
  <div class="header-inner">
    <div class="logo-box">
      <span class="logo-icon">🧑‍💻</span>
      <h1>StackOverflow Clone</h1>
    </div>
    <div class="user-nav">
      <?php if (isset($_SESSION['user_id'])): ?>
        <span>Welcome, <b><?=htmlspecialchars($_SESSION['username'] ?? 'User');?></b></span>
        <a href="logout.php" class="logout-link">Logout</a>
      <?php else: ?>
        <a href="login.php" class="login-link">Login</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<nav>
  <ul>
    <li><a href="index.php" <?=($current=='index.php')?'class="active"':'';?>>Home</a></li>
    <li><a href="questions.php" <?=($current=='questions.php')?'class="active"':'';?>>Questions</a></li>
    <li><a href="answers.php" <?=($current=='answers.php')?'class="active"':'';?>>Answers</a></li>
    <li><a href="tags.php" <?=($current=='tags.php')?'class="active"':'';?>>Tags</a></li>
    <li><a href="articles.php" <?=($current=='articles.php')?'class="active"':'';?>>Articles</a></li>
    <li><a href="profile.php" <?=($current=='profile.php')?'class="active"':'';?>>Profile</a></li>
    <li><a href="badges.php" <?=($current=='badges.php')?'class="active"':'';?>>Badges</a></li>
    <li><a href="bounties.php" <?=($current=='bounties.php')?'class="active"':'';?>>Bounties</a></li>
    <li><a href="reputation.php" <?=($current=='reputation.php')?'class="active"':'';?>>Reputation</a></li>
    <li><a href="jokes.php" <?=($current=='jokes.php')?'class="active"':'';?>>Jokes</a></li>
  </ul>
</nav>
<div class="container">