<?php
require_once 'config.php';

if (!isLoggedIn()) {
    $_SESSION['bid_error'] = "Необходимо войти в аккаунт для ставки";
    header('Location: login.php');
    exit;
}

$lot_id = (int)$_POST['lot_id'];
$bid_amount = (int)$_POST['bid_amount'];

if (!$lot_id || !$bid_amount) {
    $_SESSION['bid_error'] = "Некорректные данные";
    header("Location: auction_lot.php?id=$lot_id");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmtLot = $pdo->prepare("SELECT * FROM auction_lots WHERE id = ? AND status = 'active'");
$stmtLot->execute([$lot_id]);
$lot = $stmtLot->fetch();

if (!$lot) {
    $_SESSION['bid_error'] = "Лот не найден или торги завершены";
    header("Location: auction_index.php");
    exit;
}

$end_time = new DateTime($lot['end_time']);
$now = new DateTime();
if ($now > $end_time) {
    $pdo->prepare("UPDATE auction_lots SET status = 'expired' WHERE id = ?")->execute([$lot_id]);
    $_SESSION['bid_error'] = "Время торгов истекло";
    header("Location: auction_lot.php?id=$lot_id");
    exit;
}

$stmtMax = $pdo->prepare("SELECT MAX(bid_amount) as max_bid FROM auction_bids WHERE lot_id = ?");
$stmtMax->execute([$lot_id]);
$maxBid = $stmtMax->fetch()['max_bid'];
$current_price = $maxBid ? $maxBid : $lot['current_price'];

$min_bid = $current_price + 100;
if ($bid_amount < $min_bid) {
    $_SESSION['bid_error'] = "Ставка должна быть не менее " . number_format($min_bid, 0, '', ' ');
    header("Location: auction_lot.php?id=$lot_id");
    exit;
}

try {
    $pdo->beginTransaction();
    
    $stmtInsert = $pdo->prepare("INSERT INTO auction_bids (lot_id, user_id, bid_amount) VALUES (?, ?, ?)");
    $stmtInsert->execute([$lot_id, $user_id, $bid_amount]);
    
    $stmtUpdate = $pdo->prepare("UPDATE auction_lots SET current_price = ? WHERE id = ?");
    $stmtUpdate->execute([$bid_amount, $lot_id]);
    
    $pdo->commit();
    
    $_SESSION['bid_success'] = "Ставка " . number_format($bid_amount, 0, '', ' ') . " ₽ принята!";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['bid_error'] = "Ошибка при сохранении ставки";
}

header("Location: auction_lot.php?id=$lot_id");
exit;