<?php
require_once 'config.php';

$page_title = 'Аукцион — Главная';
$use_auction_css = true;

// Получаем активные лоты с пагинацией
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Общее количество лотов
$total = $pdo->query("SELECT COUNT(*) FROM auction_lots WHERE status = 'active'")->fetchColumn();
$total_pages = ceil($total / $per_page);

// Запрос лотов
$sql = "
    SELECT l.*, c.name as category_name,
           (SELECT COUNT(*) FROM auction_bids WHERE lot_id = l.id) as bids_count
    FROM auction_lots l
    LEFT JOIN auction_categories c ON l.category_id = c.id
    WHERE l.status = 'active'
    ORDER BY l.created_at DESC
    LIMIT $per_page OFFSET $offset
";

$lots = $pdo->query($sql)->fetchAll();

// Категории для сайдбара
$categories = $pdo->query("SELECT * FROM auction_categories ORDER BY name")->fetchAll();

// Проверка роли пользователя для админ-кнопок
$user_role = null;
if (isLoggedIn()) {
    $stmt_role = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt_role->execute([$_SESSION['user_id']]);
    $user_role = $stmt_role->fetch()['role'] ?? 'user';
}

include 'header.php';
?>

<main class="page-main">
<div class="container">
    <!-- Hero секция -->
    <section class="auction-hero">
        <h1>Аукционная площадка</h1>
        <p>Тысячи уникальных лотов от коллекционеров со всего мира</p>
        <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn-hero">Присоединиться →</a>
        <?php endif; ?>
    </section>

    <!-- Кнопки пользователя -->
    <?php if (isLoggedIn()): ?>
        <div style="margin-bottom: 30px; text-align: right;">
            <a href="my_lots.php" style="background: #c5a059; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-right: 10px;">
                📦 Мои объявления
            </a>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin_create_lot.php" style="background: #2d8a57; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block;">
                    + Создать лот (админ)
                </a>
                <a href="admin_panel.php" style="background: #243447; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-left: 10px;">
                    Управление лотами
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="auction-grid">
        <!-- Сайдбар -->
        <aside class="auction-sidebar">
            <h3 class="sidebar-title">Все категории</h3>
            <ul class="auction-cat-list">
                <li><a href="auction_index.php"> Все лоты</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="auction_categories.php?cat=<?php echo $cat['slug']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="sidebar-info">
                <h3>Как участвовать?</h3>
                <ol>
                    <li>Зарегистрируйтесь</li>
                    <li>Выберите лот</li>
                    <li>Сделайте ставку</li>
                    <li>Побеждайте!</li>
                </ol>
            </div>
        </aside>

        <!-- Контент -->
        <div class="auction-content">
            <div class="content-header">
                <h2>Активные лоты</h2>
                <div class="sort-block">
                    <span>Всего лотов: <?php echo $total; ?></span>
                </div>
            </div>

            <div class="lots-container">
                <?php if (count($lots) > 0): ?>
                    <?php foreach ($lots as $lot): 
                        $end_time = new DateTime($lot['end_time']);
                        $now = new DateTime();
                        $diff = $now->diff($end_time);
                        $time_left = '';
                        if ($diff->days > 0) $time_left = $diff->days . 'д ';
                        $time_left .= $diff->h . 'ч ' . $diff->i . 'м';
                    ?>
                        <div class="lot-card">
                            <div class="lot-header">
                                <span>Лот #<?php echo $lot['id']; ?></span>
                                <span class="lot-category-badge"><?php echo htmlspecialchars($lot['category_name'] ?? 'Без категории'); ?></span>
                            </div>
                            <div class="lot-body">
                                <h4><?php echo htmlspecialchars($lot['title']); ?></h4>
                                <p><?php echo htmlspecialchars(mb_substr($lot['description'], 0, 100)) . '...'; ?></p>
                                <div class="lot-status">
                                    <span>Ставок: <?php echo $lot['bids_count']; ?></span>
                                    <span>Осталось: <?php echo $time_left; ?></span>
                                </div>
                            </div>
                            <div class="lot-footer">
                                <div class="current-price">
                                    <?php echo number_format($lot['current_price'], 0, '', ' '); ?> ₽
                                </div>
                                <a href="auction_lot.php?id=<?php echo $lot['id']; ?>" class="btn-3d btn-bid">
                                    Смотреть →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-lots">
                        <p>Активных лотов пока нет.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Пагинация -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>