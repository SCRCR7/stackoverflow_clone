<?php
include_once('connect.php');

if (isset($_POST['submit'])) {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  // Check for empty fields
  if (empty($username) || empty($email) || empty($password)) {
    die("All fields are required!");
  }

  // Validate email format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format!");
  }

  // Validate password strength
  if (!preg_match('/^(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
    die("Password must be 8+ chars with 1 number and 1 special character!");
  }

  try {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
      die("Email already registered!");
    }

    // Insert user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);

    header("Location: login.php?signup=success");
    exit();
  } catch (PDOException $e) {
    die("Error: " . $e->getMessage());
  }

}

?>