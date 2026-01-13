<?php
// 1. セッションとエラー表示
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. DB接続
require '../common/db_connect.php';

// 3. 顧客IDの取得
if (!isset($_SESSION['customer']['id'])) {
    echo "ログインしていません。";
    exit;
}
$customer_id = $_SESSION['customer']['id'];

// 4. 初期化
$customer_info = null;
$purchase_history = [];
$rental_history = [];
$error_message = '';

try {
    // ===============================
    // 会員情報取得
    // ===============================
    $sql_customer = "
        SELECT c.*, a.prefecture, a.city, a.address_line
        FROM customer c
        LEFT JOIN address a ON c.customer_id = a.customer_id
        WHERE c.customer_id = :id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql_customer);
    $stmt->execute([':id' => $customer_id]);
    $customer_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer_info) {
        throw new Exception('顧客情報が見つかりません。');
    }

    $customer_info['full_address'] =
        ($customer_info['prefecture'] ?? '') . 
        ($customer_info['city'] ?? '') . 
        ($customer_info['address_line'] ?? '');
    if (!$customer_info['full_address']) $customer_info['full_address'] = '（住所未登録）';
    if (!$customer_info['phone_number']) $customer_info['phone_number'] = '（電話番号未登録）';
    if (!$customer_info['payment_method']) $customer_info['payment_method'] = '（支払方法未登録）';

    // ===============================
    // 購入履歴（最新5注文）
    // ===============================
    $sql_purchase = "
        SELECT
            t.transaction_id AS tid,
            t.delivery_status,
            t.transaction_date,
            p.product_name,
            p.product_image
        FROM transaction_table t
        JOIN transaction_detail d ON t.transaction_id = d.transaction_id
        JOIN product p ON d.product_id = p.product_id
        WHERE t.customer_id = :id
          AND t.transaction_type = '購入'
        GROUP BY t.transaction_id
        ORDER BY t.transaction_date DESC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($sql_purchase);
    $stmt->execute([':id' => $customer_id]);
    $purchase_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===============================
    // レンタル履歴（最新5注文）
    // ===============================
    $sql_rental = "
        SELECT
            t.transaction_id AS tid,
            t.delivery_status,
            t.transaction_date,
            p.product_name,
            p.product_image
        FROM transaction_table t
        JOIN rental r ON t.transaction_id = r.transaction_id
        JOIN product p ON r.product_id = p.product_id
        WHERE t.customer_id = :id
          AND t.transaction_type = 'レンタル'
        GROUP BY t.transaction_id
        ORDER BY t.transaction_date DESC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($sql_rental);
    $stmt->execute([':id' => $customer_id]);
    $rental_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// ステータスCSS用
function getStatusClass($status) {
    if ($status === 'キャンセル済み') return 'status-cancelled';
    if ($status === '配達完了' || $status === '返却済み') return 'status-delivered';
    return 'status-processing';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="../css/G-4_member-information.css">
</head>
<body>

<div class="container">

<header class="header">
    <a href="G-8_home.php" class="back-link"><img src="../img/modoru.png" alt="戻る"></a>
    <h1 class="header-title">マイページ画面</h1>
    <span class="header-dummy"></span>
</header>

<main class="main-content">

<?php if ($error_message): ?>
    <div class="error-box"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<!-- 購入履歴 -->
<section class="history-section">
    <h2 class="section-title">購入履歴</h2>
    <div class="history-items">
        <?php if (empty($purchase_history)): ?>
            <p class="no-history">購入履歴はありません。</p>
        <?php else: ?>
            <?php foreach ($purchase_history as $item): ?>
                <a href="G-16_order-history.php?id=<?php echo $item['tid']; ?>" class="history-item">
                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>">
                    <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                    <p class="history-status <?php echo getStatusClass($item['delivery_status']); ?>">
                        <?php echo htmlspecialchars($item['delivery_status']); ?>
                    </p>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- レンタル履歴 -->
<section class="history-section">
    <h2 class="section-title">レンタル履歴</h2>
    <div class="history-items">
        <?php if (empty($rental_history)): ?>
            <p class="no-history">レンタル履歴はありません。</p>
        <?php else: ?>
            <?php foreach ($rental_history as $item): ?>
                <a href="G-17_rental-history.php?id=<?php echo $item['tid']; ?>" class="history-item">
                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>">
                    <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                    <p class="history-status <?php echo getStatusClass($item['delivery_status']); ?>">
                        <?php echo htmlspecialchars($item['delivery_status']); ?>
                    </p>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- 会員情報 -->
<?php if ($customer_info): ?>

<section class="info-section">
    <h2 class="section-title">会員情報</h2>

    <div class="info-group">
        <label>お名前</label>
        <input type="text" value="<?php echo htmlspecialchars($customer_info['customer_name']); ?>" readonly>
    </div>
    <div class="info-group">
        <label>ご住所</label>
        <input type="text" value="<?php echo htmlspecialchars($customer_info['full_address']); ?>" readonly>
    </div>
    <div class="info-group">
        <label>電話番号</label>
        <input type="text" value="<?php echo htmlspecialchars($customer_info['phone_number']); ?>" readonly>
    </div>
    <div class="info-group">
        <label>メール</label>
        <input type="text" value="<?php echo htmlspecialchars($customer_info['email']); ?>" readonly>
    </div>
    <div class="info-group">
        <label>お支払方法</label>
        <input type="text" value="<?php echo htmlspecialchars($customer_info['payment_method']); ?>" readonly>
    </div>

    <a href="G-5_member-change.php" class="btn btn-edit">会員情報変更画面へ</a>
    <?php $_SESSION['customer_info'] = $customer_info; ?>

</section>
<?php endif; ?>


</main>
</div>
</body>
</html>
