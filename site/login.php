<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        // Ищем пользователя по username или email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        // Отладка: временно раскомментируй, чтобы увидеть результат
        // echo "Найден пользователь: ";
        // var_dump($user);
        // echo "Проверка пароля: ";
        // var_dump(password_verify($password, $user['password']));
        // exit;

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            redirect('profile.php');
        } else {
            $error = 'Неверное имя пользователя или пароль';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-section {
            max-width: 500px;
            margin: 0 auto;
        }
        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #b91c1c;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="site-wrapper">
    <?php include 'header.php'; ?>
    
    <main class="page-main">
        <div class="container">
            <div class="form-section">
                <h2>Вход в аккаунт</h2>
                
                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Имя пользователя или Email</label>
                        <input type="text" name="login" value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn">Войти</button>
                </form>
                
                <div class="register-link">
                    <a href="register.php">Нет аккаунта? Зарегистрируйтесь</a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
</div>
</body>
</html>