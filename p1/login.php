<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once('connect.php');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        header("Location: login.html?error=empty_fields");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.html?error=invalid_email");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            // Redirect to index.php within p1 folder
            header("Location: index.php");
            exit();
        }
        
        header("Location: login.html?error=invalid_credentials");
        exit();
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: login.html?error=database_error");
        exit();
    }
}

// Redirect non-POST requests
header("Location: login.html");
exit();
?>