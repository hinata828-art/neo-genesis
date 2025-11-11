<?php
session_start();
require '../common/db_connect.php'; 
// ログイン確認（必要ならコメントを外す）
// if (!isset($_SESSION['customer']['customer_id'])) {
//     exit('ログイン情報が確認できません。');
// }

$customer_id = $_SESSION['customer']['customer_id'] ?? 1; // テスト用

$connect = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';

$order_info = [
    'transaction_id' => '---',
    'total_amount' => '---',
    'payment' => '---',
    'delivery_days' => '---'
];

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ▼ 最新の取引IDを取得
    $sql_latest = "SELECT transaction_id, total_amount, payment 
                   FROM transaction_table 
                   WHERE customer_id = ?
                   ORDER BY transaction_id DESC
                   LIMIT 1";
    $stmt_latest = $pdo->prepare($sql_latest);
    $stmt_latest->execute([$customer_id]);
    $latest = $stmt_latest->fetch(PDO::FETCH_ASSOC);

    if ($latest) {
        $order_info = $latest;

        // ▼ category から delivery_days を取得
        $sql_delivery = "
            SELECT c.delivery_days
            FROM transaction_detail td
            JOIN product p ON td.product_id = p.product_id
            JOIN category c ON p.category_id = c.category_id
            WHERE td.transaction_id = ?
            LIMIT 1
        ";

        $stmt_delivery = $pdo->prepare($sql_delivery);
        $stmt_delivery->execute([$order_info['transaction_id']]);
        $delivery = $stmt_delivery->fetch(PDO::FETCH_ASSOC);

        if ($delivery) {
            $order_info['delivery_days'] = $delivery['delivery_days'];
        } else {
            $order_info['delivery_days'] = '配送情報未設定';
        }

    } else {
        $order_info['delivery_days'] = '購入履歴がありません';
    }

} catch (PDOException $e) {
    $order_info['delivery_days'] = 'データ取得エラー: ' . $e->getMessage();
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

    <img src="../img/NishimuraOnline.png" alt="ニシムラOnline" class="logo-image">

    <div class="message-area">
        ご購入ありがとうございました！
    </div>

    <div class="order-summary">
        <p><strong>配送予定日数：</strong>
        <?= htmlspecialchars($order_info['delivery_days']) ?>日後に発送予定</p>
    </div>

    <a href="G-8_home.php" class="home-button">ホーム画面へ戻る</a>

</body>
</html>
