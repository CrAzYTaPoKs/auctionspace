<?php
require_once 'config.php';

requireAdmin();

$page_title = 'Создание лота';
$use_auction_css = true;

$error = '';
$success = '';

$categories = $pdo->query("SELECT * FROM auction_categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_price = (int)($_POST['start_price'] ?? 0);
    $end_time = $_POST['end_time'] ?? '';
    
    if (empty($title)) {
        $error = 'Введите название лота';
    } elseif (empty($description)) {
        $error = 'Введите описание лота';
    } elseif ($start_price < 100) {
        $error = 'Начальная цена должна быть не менее 100 рублей';
    } elseif (empty($end_time)) {
        $error = 'Укажите дату и время окончания торгов';
    } else {
        $current_price = $start_price;
        $seller_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("
            INSERT INTO auction_lots (category_id, title, description, start_price, current_price, end_time, seller_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        if ($stmt->execute([$category_id, $title, $description, $start_price, $current_price, $end_time, $seller_id])) {
            $lot_id = $pdo->lastInsertId();
            $success = "Лот успешно создан! ID: $lot_id";
            $_POST = [];
        } else {
            $error = 'Ошибка при создании лота';
        }
    }
}

include 'header.php';
?>

<main class="page-main">
<div class="container">
    <style>
        .create-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .create-form-container h1 {
            color: #243447;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #c5a059;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #243447;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            transition: 0.2s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #c5a059;
            box-shadow: 0 0 0 3px rgba(197, 160, 89, 0.1);
        }
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        .btn-submit {
            background: #c5a059;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: 0.2s;
        }
        .btn-submit:hover {
            background: #a8863e;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            color: #c5a059;
            text-decoration: none;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #065f46;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #b91c1c;
        }
        .help-text {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
    </style>

    <div class="create-form-container">
        <h1>Создание нового лота</h1>
        
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Категория</label>
                <select name="category_id">
                    <option value="">-- Без категории --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Название лота *</label>
                <input type="text" name="title" required 
                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                       placeholder="Например: Антикварная ваза XIX века">
            </div>
            
            <div class="form-group">
                <label>Описание *</label>
                <textarea name="description" required 
                          placeholder="Подробное описание предмета, его состояние, история, особенности..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Начальная цена (руб) *</label>
                <input type="number" name="start_price" required 
                       value="<?php echo htmlspecialchars($_POST['start_price'] ?? '1000'); ?>"
                       min="100" step="100">
                <div class="help-text">Минимальная сумма: 100 рублей</div>
            </div>
            
            <div class="form-group">
                <label>Дата и время окончания торгов *</label>
                <input type="datetime-local" name="end_time" required 
                       value="<?php echo htmlspecialchars($_POST['end_time'] ?? date('Y-m-d\TH:i', strtotime('+7 days'))); ?>">
                <div class="help-text">Укажите дату и время завершения аукциона</div>
            </div>
            
            <button type="submit" class="btn-submit">Создать лот</button>
        </form>
        
        <a href="admin_panel.php" class="btn-back">← Назад в админ-панель</a>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>