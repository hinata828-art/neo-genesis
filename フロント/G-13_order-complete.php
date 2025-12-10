<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('不正なアクセスです。');
}

// 顧客ID
$customer_id = $_SESSION['customer']['id'] ?? null; 
if ($customer_id === null) {
    exit('ログイン情報（顧客ID）がセッションに見つかりません。');
}

// === G-12から渡された商品一覧(JSON) ===
$json_items = $_POST['cart_items_json'] ?? '';
$items = json_decode($json_items, true);

// 最初の商品
if (!empty($items) && is_array($items)) {
    $first_item = $items[0];
    $base_product_id = $first_item['product_id'] ?? 0;
    $selected_color_file_name = $first_item['color'] ?? 'original';
} else {
    $base_product_id = 0;
    $selected_color_file_name = 'original';
}

$total_amount = $_POST['total_amount'] ?? 0;
$payment_method = $_POST['payment'] ?? '不明';

$option_warranty = $_POST['option_warranty'] ?? null;
$option_delivery = $_POST['option_delivery'] ?? null;

$customer_coupon_id = $_POST['customer_coupon_id'] ?? 0;


// === カラー表示マップ（G-12と同じ）===
$color_display_map = [
    'original' => 'オリジナル',
    '白色'     => 'ホワイト',
    '青'       => 'ブルー',
    'ゲーミング' => 'ゲーミング',
    '黄色'     => 'イエロー',
    '赤'       => 'レッド',
    '緑'       => 'グリーン',
    'ブラック'   => 'ブラック',
    'ピンク'     => 'ピンク',
    'グレー'     => 'グレー'
];

$selected_color_display_name = $color_display_map[$selected_color_file_name]
                              ?? $selected_color_file_name;


$order_info = [
    'transaction_id' => '---',
    'total_amount' => $total_amount,
    'payment' => $payment_method,
    'delivery_days' => '---'
];

$final_product_id = $base_product_id; 


try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 色バリエーション商品を取得
    if ($selected_color_display_name !== 'オリジナル') {

        $sql_get_name = "SELECT product_name FROM product WHERE product_id = ?";
        $stmt_get_name = $pdo->prepare($sql_get_name);
        $stmt_get_name->execute([$base_product_id]);
        $base_product = $stmt_get_name->fetch(PDO::FETCH_ASSOC);

        if ($base_product) {
            $base_product_name = $base_product['product_name'];

            $sql_find_variant = "SELECT product_id FROM product 
                                 WHERE product_name = ? AND color = ?";
            $stmt_find_variant = $pdo->prepare($sql_find_variant);
            $stmt_find_variant->execute([$base_product_name, $selected_color_display_name]);
            $variant_product = $stmt_find_variant->fetch(PDO::FETCH_ASSOC);

            if ($variant_product) {
                $final_product_id = $variant_product['product_id'];
            }
        }
    }


    // === 購入処理開始 ===
    $pdo->beginTransaction();

    // 1. transaction_table
    $sql_transaction = "INSERT INTO transaction_table 
                          (customer_id, transaction_type, transaction_date, payment, delivery_status, total_amount)
                        VALUES
                          (?, '購入', NOW(), ?, '注文受付', ?)";

    $stmt_transaction = $pdo->prepare($sql_transaction);
    $stmt_transaction->execute([$customer_id, $payment_method, $total_amount]);

    $new_transaction_id = $pdo->lastInsertId();
    $order_info['transaction_id'] = $new_transaction_id;


    // 2. transaction_detail へ商品をすべて登録
    $sql_detail = "INSERT INTO transaction_detail 
                      (transaction_id, product_id, quantity)
                   VALUES (?, ?, ?)";

    $stmt_detail = $pdo->prepare($sql_detail);

    foreach ($items as $one) {
        $stmt_detail->execute([
            $new_transaction_id,
            $one['product_id'],   // こちらは色バリエーションも含まれたID
            $one['qty'] ?? 1
        ]);
    }


    // 3. クーポン使用済みに
    if ($customer_coupon_id > 0) {
        $sql_coupon_update = "UPDATE customer_coupon 
                              SET used_at = NOW()
                              WHERE customer_coupon_id = :ccid
                                AND customer_id = :cid
                                AND used_at IS NULL";

        $stmt_coupon = $pdo->prepare($sql_coupon_update);
        $stmt_coupon->execute([
            ':ccid' => $customer_coupon_id,
            ':cid'  => $customer_id
        ]);
    }


    // ★★★ ここが重要！購入完了後にカートを空にする ★★★
    unset($_SESSION['cart']);

    $pdo->commit();

    // 配送日数取得
    $sql_delivery = "
        SELECT c.delivery_days
        FROM transaction_detail td
        JOIN product p ON td.product_id = p.product_id
        JOIN category c ON p.category_id = c.category_id
        WHERE td.transaction_id = ?
        LIMIT 1";
    $stmt_delivery = $pdo->prepare($sql_delivery);
    $stmt_delivery->execute([$new_transaction_id]);
    $delivery = $stmt_delivery->fetch(PDO::FETCH_ASSOC);

    $order_info['delivery_days'] = $delivery['delivery_days'] ?? '配送情報未設定';

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $order_info['delivery_days'] = '注文処理エラー: ' . $e->getMessage();
}
?>

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
