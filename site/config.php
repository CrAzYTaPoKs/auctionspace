<?php
session_start();

$host = 'localhost';
$db   = 'demo';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId($pdo) {
    if (!isset($_SESSION['user_id'])) return null;
    return $_SESSION['user_id'];
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>