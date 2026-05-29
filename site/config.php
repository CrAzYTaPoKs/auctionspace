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

function isAdmin() {
    if (!isset($_SESSION['user_id'])) return false;
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    return ($user && $user['role'] === 'admin');
}

function requireAdmin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    if (!isAdmin()) {
        die("<div class='container' style='text-align:center; padding:50px;'><h2>Доступ запрещен</h2><p>У вас нет прав администратора.</p><a href='auction_index.php'>Вернуться на главную</a></div>");
    }
}

// Добавляем колонку role в таблицу users, если её нет
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'user'");
    // Устанавливаем админа для пользователя admin
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE username = 'admin'");
    $stmt->execute();
} catch(PDOException $e) {
    // Колонка уже существует или ошибка - игнорируем
}
?>