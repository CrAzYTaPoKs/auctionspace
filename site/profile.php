<?php
require_once 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Сначала войдите в аккаунт';
    redirect('login.php');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Проверка роли пользователя
$stmt_role = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt_role->execute([$_SESSION['user_id']]);
$user_role = $stmt_role->fetch()['role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .profile-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .profile-info {
            margin: 25px 0;
        }
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        .btn-logout {
            background: #d9534f;
        }
        .btn-logout:hover {
            background: #c9302c;
        }
        .welcome {
            text-align: center;
            font-size: 20px;
            color: #243447;
            margin-bottom: 20px;
        }
        .admin-link {
            text-align: center;
            margin-top: 20px;
        }
        .admin-link a {
            background: #243447;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
        }
        .admin-link a:hover {
            background: #1a2a38;
        }
    </style>
</head>
<body>
<div class="site-wrapper">
    <?php include 'header.php'; ?>
    
    <main class="page-main">
        <div class="container">
            <div class="profile-card">
                <h2>Мой профиль</h2>
                
                <div class="welcome">
                    Здравствуйте, <?php echo htmlspecialchars($user['username']); ?>!
                </div>
                
                <div class="profile-info">
                    <div class="info-row">
                        <div class="info-label">ID:</div>
                        <div class="info-value"><?php echo $user['id']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Имя пользователя:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Роль:</div>
                        <div class="info-value">
                            <?php echo $user_role === 'admin' ? 'Администратор' : 'Пользователь'; ?>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Дата регистрации:</div>
                        <div class="info-value"><?php echo date('d.m.Y H:i:s', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>
                
                <div class="btn-group">
                    <a href="auction_index.php" class="btn">На аукцион</a>
                    <a href="logout.php" class="btn btn-logout">Выйти</a>
                </div>
                
                <?php if ($user_role === 'admin'): ?>
                    <div class="admin-link">
                        <a href="admin_panel.php">Админ-панель</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</div>
</body>
</html>