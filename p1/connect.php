<?php
$host = "sql102.infinityfree.com";
$dbname = "if0_39013734_stackoverflow_clone";
$username = "if0_39013734";
$password = "sbnadmin4321";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>