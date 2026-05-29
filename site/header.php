<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Demo Project'; ?></title>
    <link rel="stylesheet" href="styles.css">
    <?php if (isset($use_auction_css)): ?>
        <link rel="stylesheet" href="auction_style.css">
    <?php endif; ?>
</head>
<body>
<div class="site-wrapper">
    <header class="page-header">
        <div class="container header-inner">
            <div class="logo-area">
                <div class="logo-placeholder">🛒</div>
                <div class="logo-text">AUCTION<span style="font-weight:400">SPACE</span></div>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="auction_index.php">Главная</a></li>
                    <li><a href="auction_index.php">Аукцион</a></li>
                    <li><a href="auction_categories.php">Категории</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/my_lots.php">Мои лоты</a></li>
                        <li><a href="profile.php"><?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                        <li><a href="logout.php">Выйти</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Вход</a></li>
                        <li><a href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>