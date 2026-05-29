<?php
require_once 'config.php';

requireAdmin();

$page_title = 'Админ-панель';
$use_auction_css = true;

// Обработка удаления лота
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT id FROM auction_lots WHERE id = ?");
    $stmt->execute([$delete_id]);
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM auction_lots WHERE id = ?")->execute([$delete_id]);
        $success = "Лот #$delete_id успешно удален";
    } else {
        $error = "Лот не найден";
    }
}

// Получение всех лотов для админ-панели
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$total = $pdo->query("SELECT COUNT(*) FROM auction_lots")->fetchColumn();
$total_pages = ceil($total / $per_page);

// ИСПРАВЛЕННЫЙ ЗАПРОС - без подготовленного выражения для LIMIT
$sql = "
    SELECT l.*, c.name as category_name, u.username as seller_name
    FROM auction_lots l
    LEFT JOIN auction_categories c ON l.category_id = c.id
    LEFT JOIN users u ON l.seller_id = u.id
    ORDER BY l.created_at DESC
    LIMIT $per_page OFFSET $offset
";

$lots = $pdo->query($sql)->fetchAll();

include 'header.php';
?>

<main class="page-main">
<div class="container">
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .admin-header h1 {
            color: #243447;
            font-size: 28px;
        }
        .btn-admin {
            background: #243447;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-admin:hover {
            background: #1a2a38;
        }
        .btn-create {
            background: #2d8a57;
        }
        .btn-create:hover {
            background: #247a4a;
        }
        .admin-table {
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .admin-table th,
        .admin-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #243447;
        }
        .admin-table tr:hover td {
            background: #fafbfd;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
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
            gap: 10px;
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
        .admin-stats {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .admin-stats span {
            font-weight: bold;
            color: #2d8a57;
        }
        @media (max-width: 768px) {
            .admin-table {
                font-size: 12px;
            }
            .admin-table th,
            .admin-table td {
                padding: 10px;
            }
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>

    <div class="admin-header">
        <h1>Админ-панель</h1>
        <a href="admin_create_lot.php" class="btn-admin btn-create">+ Создать новый лот</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-stats">
        <div>Всего лотов: <span><?php echo $total; ?></span></div>
        <div>Активных: <span>
            <?php echo $pdo->query("SELECT COUNT(*) FROM auction_lots WHERE status = 'active'")->fetchColumn(); ?>
        </span></div>
        <div>Продано: <span>
            <?php echo $pdo->query("SELECT COUNT(*) FROM auction_lots WHERE status = 'sold'")->fetchColumn(); ?>
        </span></div>
        <div>Завершено: <span>
            <?php echo $pdo->query("SELECT COUNT(*) FROM auction_lots WHERE status = 'expired'")->fetchColumn(); ?>
        </span></div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Текущая цена</th>
                    <th>Статус</th>
                    <th>Продавец</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($lots) > 0): ?>
                    <?php foreach ($lots as $lot): ?>
                        <tr>
                            <td><?php echo $lot['id']; ?></td>
                            <td><?php echo htmlspecialchars(mb_substr($lot['title'], 0, 40)); ?></td>
                            <td><?php echo htmlspecialchars($lot['category_name'] ?? '-'); ?></td>
                            <td><?php echo number_format($lot['current_price'], 0, '', ' '); ?> ₽</td>
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
                            <td><?php echo htmlspecialchars($lot['seller_name'] ?? 'Неизвестен'); ?></td>
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
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            Нет лотов для отображения
                        </td>
                    </tr>
                <?php endif; ?>
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
</div>
</main>

<?php include 'footer.php'; ?>