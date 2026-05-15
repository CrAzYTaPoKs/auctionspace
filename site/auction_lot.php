<?php
require_once 'config.php';

$page_title = 'Просмотр лота';
$use_auction_css = true;

$lot_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем данные лота
$stmt = $pdo->prepare("
    SELECT l.*, c.name as category_name, u.username as seller_name
    FROM auction_lots l
    LEFT JOIN auction_categories c ON l.category_id = c.id
    LEFT JOIN users u ON l.seller_id = u.id
    WHERE l.id = ?
");
$stmt->execute([$lot_id]);
$lot = $stmt->fetch();

if (!$lot) {
    die("<div class='container'><h2>❌ Лот не найден</h2><a href='auction_index.php'>Вернуться</a></div>");
}

// Обновляем просмотры
$pdo->prepare("UPDATE auction_lots SET views = views + 1 WHERE id = ?")->execute([$lot_id]);

// Получаем текущую максимальную ставку
$stmtMax = $pdo->prepare("SELECT MAX(bid_amount) as max_bid FROM auction_bids WHERE lot_id = ?");
$stmtMax->execute([$lot_id]);
$maxBid = $stmtMax->fetch()['max_bid'];
$current_price = $maxBid ? $maxBid : $lot['current_price'];
$min_next_bid = $current_price + 100;

// Получаем историю ставок
$stmtHistory = $pdo->prepare("
    SELECT b.*, u.username 
    FROM auction_bids b
    JOIN users u ON b.user_id = u.id
    WHERE b.lot_id = ?
    ORDER BY b.bid_time DESC
    LIMIT 20
");
$stmtHistory->execute([$lot_id]);
$bidHistory = $stmtHistory->fetchAll();

// Сообщения об ошибках/успехах
$error_msg = $_SESSION['bid_error'] ?? null;
$success_msg = $_SESSION['bid_success'] ?? null;
unset($_SESSION['bid_error'], $_SESSION['bid_success']);

include 'header.php';
?>

<main class="page-main">
<div class="container">
    <div class="lot-detail">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="auction_index.php">Главная</a> / 
            <a href="auction_categories.php?cat=<?php echo $lot['category_name']; ?>">
                <?php echo htmlspecialchars($lot['category_name'] ?? 'Категория'); ?>
            </a> / 
            <span><?php echo htmlspecialchars($lot['title']); ?></span>
        </div>

        <div class="lot-detail-grid">
            <div class="lot-info">
                <h1><?php echo htmlspecialchars($lot['title']); ?></h1>
                <div class="lot-meta">
                    <span> Лот #<?php echo $lot['id']; ?></span>
                    <span> Продавец: <?php echo htmlspecialchars($lot['seller_name'] ?? 'Администратор'); ?></span>
                    <span> Просмотров: <?php echo $lot['views']; ?></span>
                </div>

                <div class="lot-description">
                    <h3> Описание</h3>
                    <p><?php echo nl2br(htmlspecialchars($lot['description'])); ?></p>
                </div>

                <div class="lot-status-block">
                    <div class="status-item">
                        <span>⏰ Окончание торгов:</span>
                        <strong><?php echo date('d.m.Y H:i', strtotime($lot['end_time'])); ?></strong>
                    </div>
                    <div class="status-item">
                        <span>📊 Статус:</span>
                        <?php if ($lot['status'] == 'active'): ?>
                            <span class="status-active"> Активен</span>
                        <?php elseif ($lot['status'] == 'sold'): ?>
                            <span class="status-sold"> Продан</span>
                        <?php else: ?>
                            <span class="status-expired"> Завершён</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="lot-bid-panel">
                <div class="current-price-big">
                    Текущая цена
                    <span><?php echo number_format($current_price, 0, '', ' '); ?> ₽</span>
                </div>

                <?php if ($error_msg): ?>
                    <div class="alert-error"> <?php echo htmlspecialchars($error_msg); ?></div>
                <?php endif; ?>
                
                <?php if ($success_msg): ?>
                    <div class="alert-success"> <?php echo htmlspecialchars($success_msg); ?></div>
                <?php endif; ?>

                <?php if ($lot['status'] == 'active'): ?>
                    <?php if (isLoggedIn()): ?>
                        <form action="auction_place_bid.php" method="POST" class="bid-form">
                            <input type="hidden" name="lot_id" value="<?php echo $lot['id']; ?>">
                            <div class="form-group">
                                <label for="bid_amount">Ваша ставка (мин. <?php echo number_format($min_next_bid, 0, '', ' '); ?> ₽)</label>
                                <input type="number" name="bid_amount" id="bid_amount" 
                                       value="<?php echo $min_next_bid; ?>" 
                                       step="100" min="<?php echo $min_next_bid; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-big"> Сделать ставку</button>
                        </form>
                    <?php else: ?>
                        <div class="alert-warning">
                             <a href="login.php">Войдите</a> или <a href="register.php">зарегистрируйтесь</a>, чтобы делать ставки
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert-info"> Торги по данному лоту завершены.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- История ставок -->
        <div class="bid-history">
            <h3> История ставок (<?php echo count($bidHistory); ?>)</h3>
            <?php if (count($bidHistory) > 0): ?>
                <table class="bid-table">
                    <thead>
                        <tr><th>Пользователь</th><th>Ставка</th><th>Время</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bidHistory as $bid): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bid['username']); ?></td>
                                <td><strong><?php echo number_format($bid['bid_amount'], 0, '', ' '); ?> ₽</strong></td>
                                <td><?php echo date('d.m.Y H:i:s', strtotime($bid['bid_time'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-bids"> Ставок пока нет. Будьте первым!</p>
            <?php endif; ?>
        </div>

        <div class="lot-actions">
            <a href="auction_index.php" class="btn-back">← Назад к списку лотов</a>
        </div>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>