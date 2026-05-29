<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$lot_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Получаем данные лота
$stmt = $pdo->prepare("SELECT * FROM auction_lots WHERE id = ?");
$stmt->execute([$lot_id]);
$lot = $stmt->fetch();

if (!$lot) {
    die("<div class='container' style='text-align:center; padding:50px;'><h2>Лот не найден</h2><a href='auction_index.php'>Вернуться</a></div>");
}

// Проверка прав: владелец или админ (используем seller_id)
if ($lot['seller_id'] != $user_id && !$is_admin) {
    die("<div class='container' style='text-align:center; padding:50px;'><h2>Доступ запрещен</h2><p>Вы не можете редактировать этот лот.</p><a href='auction_index.php'>Вернуться</a></div>");
}

$page_title = 'Редактирование лота #' . $lot_id;
$use_auction_css = true;

$error = '';
$success = '';

$categories = $pdo->query("SELECT * FROM auction_categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $end_time = $_POST['end_time'] ?? '';
    $status = $_POST['status'] ?? $lot['status'];
    
    if (empty($title)) {
        $error = 'Введите название лота';
    } elseif (empty($description)) {
        $error = 'Введите описание лота';
    } elseif (empty($end_time)) {
        $error = 'Укажите дату и время окончания торгов';
    } else {
        $stmt = $pdo->prepare("
            UPDATE auction_lots 
            SET category_id = ?, title = ?, description = ?, end_time = ?, status = ?
            WHERE id = ?
        ");
        
        if ($stmt->execute([$category_id, $title, $description, $end_time, $status, $lot_id])) {
            $success = "Лот успешно обновлен!";
        } else {
            $error = 'Ошибка при обновлении лота';
        }
    }
}

include 'header.php';
?>

<main class="page-main">
<div class="container">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .form-container h1 {
            color: #243447;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #c5a059;
        }
        .lot-info-badge {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        .lot-info-badge div {
            font-size: 14px;
        }
        .lot-info-badge strong {
            color: #2d8a57;
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
        .readonly-field {
            background: #f5f5f5;
            color: #666;
        }
        .warning-note {
            background: #fef3c7;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #92400e;
            border-left: 4px solid #92400e;
        }
    </style>

    <div class="form-container">
        <h1>Редактирование лота #<?php echo $lot_id; ?></h1>
        
        <?php if ($is_admin && $lot['seller_id'] != $user_id): ?>
            <div class="warning-note">
                 Вы редактируете лот пользователя. Будьте внимательны!
            </div>
        <?php endif; ?>
        
        <div class="lot-info-badge">
            <div> Текущая цена: <strong><?php echo number_format($lot['current_price'], 0, '', ' '); ?> ₽</strong></div>
            <div> Начальная цена: <strong><?php echo number_format($lot['start_price'], 0, '', ' '); ?> ₽</strong></div>
            <div> Просмотров: <strong><?php echo $lot['views']; ?></strong></div>
            <div> Создан: <strong><?php echo date('d.m.Y', strtotime($lot['created_at'])); ?></strong></div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-success">
                <?php echo htmlspecialchars($success); ?>
                <div style="margin-top: 10px;">
                    <a href="auction_lot.php?id=<?php echo $lot_id; ?>" style="color: #065f46; font-weight: bold;">→ Посмотреть лот</a>
                </div>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Категория</label>
                <select name="category_id">
                    <option value="">-- Без категории --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                            <?php echo ($lot['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Название лота *</label>
                <input type="text" name="title" required 
                       value="<?php echo htmlspecialchars($lot['title']); ?>"
                       maxlength="255">
            </div>
            
            <div class="form-group">
                <label>Описание *</label>
                <textarea name="description" required><?php echo htmlspecialchars($lot['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Начальная цена (не редактируется)</label>
                <input type="text" class="readonly-field" readonly 
                       value="<?php echo number_format($lot['start_price'], 0, '', ' '); ?> ₽">
                <div class="help-text">Начальную цену нельзя изменить после создания лота</div>
            </div>
            
            <div class="form-group">
                <label>Дата и время окончания торгов *</label>
                <input type="datetime-local" name="end_time" required 
                       value="<?php echo date('Y-m-d\TH:i', strtotime($lot['end_time'])); ?>">
                <div class="help-text">Измените дату завершения аукциона при необходимости</div>
            </div>
            
            <div class="form-group">
                <label>Статус лота</label>
                <select name="status">
                    <option value="active" <?php echo $lot['status'] == 'active' ? 'selected' : ''; ?>>Активен</option>
                    <option value="sold" <?php echo $lot['status'] == 'sold' ? 'selected' : ''; ?>>Продан</option>
                    <option value="expired" <?php echo $lot['status'] == 'expired' ? 'selected' : ''; ?>>Завершен</option>
                </select>
                <div class="help-text">Измените статус лота</div>
            </div>
            
            <button type="submit" class="btn-submit">Сохранить изменения</button>
        </form>
        
        <a href="my_lots.php" class="btn-back">← Назад к моим лотам</a>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>