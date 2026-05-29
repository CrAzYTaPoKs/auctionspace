<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = 'Мои лоты';
$use_auction_css = true;

$user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

// Обработка удаления лота
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT seller_id FROM auction_lots WHERE id = ?");
    $stmt->execute([$delete_id]);
    $lot = $stmt->fetch();
    
    if ($lot && ($lot['seller_id'] == $user_id || $is_admin)) {
        $pdo->prepare("DELETE FROM auction_lots WHERE id = ?")->execute([$delete_id]);
        $success = "Лот #$delete_id успешно удален";
    } else {
        $error = "У вас нет прав для удаления этого лота";
    }
}

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Получаем лоты пользователя
$stmt = $pdo->prepare("SELECT COUNT(*) FROM auction_lots WHERE seller_id = ?");
$stmt->execute([$user_id]);
$total = $stmt->fetchColumn();

$sql = "
    SELECT l.*, c.name as category_name
    FROM auction_lots l
    LEFT JOIN auction_categories c ON l.category_id = c.id
    WHERE l.seller_id = ?
    ORDER BY l.created_at DESC
    LIMIT $per_page OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$lots = $stmt->fetchAll();

$total_pages = ceil($total / $per_page);

include 'header.php';
?>

<main class="page-main">
<div class="container">
    <style>
        .my-lots-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .my-lots-header h1 {
            color: #243447;
            font-size: 28px;
            margin: 0;
        }
        .btn-create-lot {
            background: #2d8a57;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: 0.2s;
        }
        .btn-create-lot:hover {
            background: #247a4a;
            transform: translateY(-2px);
        }
        .lots-table {
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .lots-table th,
        .lots-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .lots-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #243447;
        }
        .lots-table tr:hover td {
            background: #fafbfd;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        .status-sold {
            background: #fee2e2;
            color: #b91c1c;
        }
        .status-expired {
            background: #fef3c7;
            color: #92400e;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .action-buttons a {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
        }
        .action-view {
            background: #e0f2fe;
            color: #075985;
        }
        .action-view:hover {
            background: #bae6fd;
        }
        .action-edit {
            background: #fef3c7;
            color: #92400e;
        }
        .action-edit:hover {
            background: #fde68a;
        }
        .action-delete {
            background: #fee2e2;
            color: #b91c1c;
        }
        .action-delete:hover {
            background: #fecaca;
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
        .pagination {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        .pagination a {
            padding: 8px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: #243447;
            transition: 0.2s;
        }
        .pagination a:hover,
        .pagination a.active {
            background: #c5a059;
            color: white;
            border-color: #c5a059;
        }
        .empty-lots {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 16px;
        }
        .empty-lots p {
            color: #888;
            font-size: 18px;
            margin-bottom: 20px;
        }
        .price-cell {
            font-weight: bold;
            color: #2d8a57;
        }
        @media (max-width: 768px) {
            .lots-table {
                font-size: 12px;
            }
            .lots-table th,
            .lots-table td {
                padding: 10px;
            }
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>

    <div class="my-lots-header">
        <h1>Мои объявления</h1>
        <a href="create_lot.php" class="btn-create-lot">+ Создать объявление</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (count($lots) > 0): ?>
        <div class="lots-table-wrapper">
            <table class="lots-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Нач. цена</th>
                        <th>Тек. цена</th>
                        <th>Статус</th>
                        <th>Создан</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lots as $lot): ?>
                        <tr>
                            <td>#<?php echo $lot['id']; ?></td>
                            <td style="max-width: 250px;">
                                <?php echo htmlspecialchars(mb_substr($lot['title'], 0, 40)); ?>
                                <?php if (mb_strlen($lot['title']) > 40): ?>...<?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($lot['category_name'] ?? '-'); ?></td>
                            <td><?php echo number_format($lot['start_price'], 0, '', ' '); ?> ₽</td>
                            <td class="price-cell"><?php echo number_format($lot['current_price'], 0, '', ' '); ?> ₽</td>
                            <td>
                                <span class="status-badge status-<?php echo $lot['status']; ?>">
                                    <?php 
                                        $status_text = [
                                            'active' => 'Активен',
                                            'sold' => 'Продан',
                                            'expired' => 'Завершен'
                                        ];
                                        echo $status_text[$lot['status']] ?? $lot['status'];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($lot['created_at'])); ?></td>
                            <td class="action-buttons">
                                <a href="auction_lot.php?id=<?php echo $lot['id']; ?>" class="action-view">Просмотр</a>
                                <a href="edit_lot.php?id=<?php echo $lot['id']; ?>" class="action-edit">Редактировать</a>
                                <a href="?delete=<?php echo $lot['id']; ?>" 
                                   class="action-delete" 
                                   onclick="return confirm('Удалить лот &quot;<?php echo htmlspecialchars($lot['title']); ?>&quot;? Это действие нельзя отменить.')">
                                    Удалить
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-lots">
            <p>📦 У вас пока нет созданных объявлений.</p>
            <a href="create_lot.php" class="btn-create-lot">Создать первое объявление →</a>
        </div>
    <?php endif; ?>
</div>
</main>

<?php include 'footer.php'; ?>