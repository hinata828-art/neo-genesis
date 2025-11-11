<?php
session_start();
require 'db-connect.php';

// ログイン確認（必要ならコメントを外す）
/*
if (!isset($_SESSION['customer']['customer_id'])) {
    exit('ログイン情報が確認できません。');
}
*/
$customer_id = $_SESSION['customer']['customer_id'];

$connect = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';

// 初期値（取得失敗時用）
$order_info = [
    'transaction_id' => '---',
    'total_amount' => '---',
    'payment' => '---',
    'delivery_status' => '---'
];

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT transaction_id, total_amount, payment, delivery_status
            FROM transaction
            WHERE customer_id = ?
            ORDER BY transaction_id DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $order_info = $result;
    } else {
        $order_info['delivery_status'] = '購入履歴がありません';
    }

} catch (PDOException $e) {
    $order_info['delivery_status'] = 'データ取得エラー';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>購入完了</title>
    <link rel="stylesheet" href="../css/G-13_order-complete.css">
</head>
<body>

    <img src="img/NishimuraOnline.png" alt="ニシムラOnline" class="logo-image">

    <div class="message-area">
        ご購入ありがとうございました！
    </div>

    <div class="order-summary">
        <p><strong>注文番号：</strong> <?= htmlspecialchars($order_info['transaction_id']) ?></p>
        <p><strong>合計金額：</strong> ¥<?= is_numeric($order_info['total_amount']) ? number_format($order_info['total_amount']) : $order_info['total_amount'] ?></p>
        <p><strong>支払方法：</strong> <?= htmlspecialchars($order_info['payment']) ?></p>
        <p><strong>配送状況：</strong> <?= htmlspecialchars($order_info['delivery_status']) ?></p>
    </div>

    <a href="index.php" class="home-button">ホーム画面へ戻る</a>

</body>
</html>
