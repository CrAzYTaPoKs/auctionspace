<?php
require_once 'config.php';

$page_title = 'Категории аукциона';
$use_auction_css = true;

$slug = $_GET['cat'] ?? '';
$selected_category = null;

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM auction_categories WHERE slug = ?");
    $stmt->execute([$slug]);
    $selected_category = $stmt->fetch();
}

// Получаем лоты для категории
if ($selected_category) {
    $stmt = $pdo->prepare("
        SELECT l.*, (SELECT COUNT(*) FROM auction_bids WHERE lot_id = l.id) as bids_count
        FROM auction_lots l
        WHERE l.category_id = ? AND l.status = 'active'
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$selected_category['id']]);
} else {
    $stmt = $pdo->query("
        SELECT l.*, c.name as category_name,
               (SELECT COUNT(*) FROM auction_bids WHERE lot_id = l.id) as bids_count
        FROM auction_lots l
        LEFT JOIN auction_categories c ON l.category_id = c.id
        WHERE l.status = 'active'
        ORDER BY l.created_at DESC
    ");
}
$lots = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM auction_categories ORDER BY name")->fetchAll();

include 'header.php';
?>

<main class="page-main">
<div class="container">
    <div class="auction-grid">
        <aside class="auction-sidebar">
            <h3 class="sidebar-title">Все категории</h3>
            <ul class="auction-cat-list">
                <li><a href="auction_categories.php"> Все лоты</a></li>
               <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="?cat=<?php echo $cat['slug']; ?>" 
                           class="<?php echo ($selected_category && $selected_category['id'] == $cat['id']) ? 'active' : ''; ?>">
                             <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="auction-content">
            <div class="content-header">
                <h2>
                    <?php if ($selected_category): ?>
                        <?php echo htmlspecialchars($selected_category['name']); ?>
                    <?php else: ?>
                        Все лоты
                    <?php endif; ?>
                </h2>
                <span class="lots-count">Найдено: <?php echo count($lots); ?> лотов</span>
            </div>

            <div class="lots-container">
                <?php if (count($lots) > 0): ?>
                    <?php foreach ($lots as $lot): ?>
                        <div class="lot-card">
                            <div class="lot-header">Лот #<?php echo $lot['id']; ?></div>
                            <div class="lot-body">
                                <h4><?php echo htmlspecialchars($lot['title']); ?></h4>
                                <p><?php echo htmlspecialchars(mb_substr($lot['description'], 0, 100)); ?>...</p>
                                <div class="lot-status">
                                    Ставок: <?php echo $lot['bids_count']; ?>
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
                    <div class="no-lots"> В этой категории пока нет активных лотов.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>