<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php';

/* =========================
   1. POSTチェック
========================= */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('不正なアクセスです。');
}

$customer_id = $_SESSION['customer']['id'] ?? null;
if ($customer_id === null) {
    exit('ログイン情報がありません。');
}

/* =========================
   2. POSTデータ取得
========================= */
$json_items = $_POST['cart_items_json'] ?? '';
$items = json_decode($json_items, true);

if (empty($items) || !is_array($items)) {
    exit('購入商品が存在しません。');
}

$total_amount = $_POST['total_amount'] ?? 0;
$payment_method = $_POST['payment'] ?? '不明';
$customer_coupon_id = $_POST['customer_coupon_id'] ?? 0;

/* =========================
   3. DB接続
========================= */
$connect = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';

$order_info = [
    'transaction_id' => '---',
    'delivery_days'  => '---'
];

try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    /* =========================
       4. transaction_table
    ========================= */
    $sql_transaction = "
        INSERT INTO transaction_table
        (customer_id, transaction_type, transaction_date, payment, delivery_status, total_amount)
        VALUES (?, '購入', NOW(), ?, '注文受付', ?)
    ";
    $stmt_transaction = $pdo->prepare($sql_transaction);
    $stmt_transaction->execute([
        $customer_id,
        $payment_method,
        $total_amount
    ]);

    $transaction_id = $pdo->lastInsertId();
    $order_info['transaction_id'] = $transaction_id;

    /* =========================
       5. transaction_detail（複数商品）
    ========================= */
    $sql_detail = "
        INSERT INTO transaction_detail
        (transaction_id, product_id, quantity)
        VALUES (?, ?, ?)
    ";
    $stmt_detail = $pdo->prepare($sql_detail);

    foreach ($items as $item) {
        $stmt_detail->execute([
            $transaction_id,
            $item['product_id'],
            $item['qty']
        ]);
    }

    /* =========================
       6. クーポン使用処理
    ========================= */
    if ($customer_coupon_id > 0) {
        $sql_coupon = "
            UPDATE customer_coupon
            SET used_at = NOW()
            WHERE customer_coupon_id = ?
              AND customer_id = ?
              AND used_at IS NULL
        ";
        $stmt_coupon = $pdo->prepare($sql_coupon);
        $stmt_coupon->execute([
            $customer_coupon_id,
            $customer_id
        ]);
    }

    $pdo->commit();
    unset($_SESSION['cart']);

    /* =========================
       7. 配送日数取得
    ========================= */
    $sql_delivery = "
        SELECT MAX(c.delivery_days) AS delivery_days
        FROM transaction_detail td
        JOIN product p ON td.product_id = p.product_id
        JOIN category c ON p.category_id = c.category_id
        WHERE td.transaction_id = ?
    ";
    $stmt_delivery = $pdo->prepare($sql_delivery);
    $stmt_delivery->execute([$transaction_id]);
    $delivery = $stmt_delivery->fetch(PDO::FETCH_ASSOC);

    $order_info['delivery_days'] = $delivery['delivery_days'] ?? '未定';

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    exit('注文処理エラー：' . $e->getMessage());
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

    <img src="../img/NishimuraOnline.png" alt="ニシムラOnline" class="completion-logo">

    <div class="message-area">
        ご購入ありがとうございました！
    </div>

    <div class="order-summary">
        <p><strong>配送予定日数：</strong>
        <?php
            if (is_numeric($order_info['delivery_days'])) {
                echo htmlspecialchars($order_info['delivery_days']) . '日後に発送予定';
            } else {
                echo htmlspecialchars($order_info['delivery_days']);
            }
        ?>
        </p>

        <?php if ($customer_coupon_id > 0): ?>
            <p><strong>クーポン：</strong>割引を適用しました。</p>
        <?php endif; ?>
    </div>

    <?php if ($order_info['transaction_id'] !== '---'): ?>
        <a href="G-16_order-history.php?id=<?php echo htmlspecialchars($order_info['transaction_id']); ?>" class="detail-button">注文詳細を見る</a>
    <?php endif; ?>

    <a href="G-8_home.php" class="home-button">ホーム画面へ戻る</a>

</body>
</html>
