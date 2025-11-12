<?php
// 1. セッションとエラー表示
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. DB接続
require '../common/db_connect.php'; // $pdo が定義されていると仮定

// 3. 顧客IDの取得（ログインセッションから）
if (isset($_SESSION['customer']['id'])) {
    $customer_id = $_SESSION['customer']['id'];
} else {
    // ログインしていない場合
    echo "ログインしていません。";
    // header('Location: G-1_login.php'); // ログインページへ
    exit;
}

// 4. データの初期化
$customer_info = null;
$purchase_history = [];
$rental_history = [];
$error_message = '';

try {
    // 5. SQL 1: 会員情報の取得 (変更なし)
    $sql_customer = "SELECT c.*, a.prefecture, a.city 
                       FROM customer AS c
                       LEFT JOIN address AS a ON c.customer_id = a.customer_id
                       WHERE c.customer_id = :id
                       LIMIT 1";
    $stmt_customer = $pdo->prepare($sql_customer);
    $stmt_customer->bindValue(':id', $customer_id, PDO::PARAM_INT);
    $stmt_customer->execute();
    $customer_info = $stmt_customer->fetch(PDO::FETCH_ASSOC);

    if (!$customer_info) {
        throw new Exception('顧客情報が見つかりません。');
    }
    
    // (会員情報の null チェック ... 元のコードのまま)
    $customer_info['full_address'] = ($customer_info['prefecture'] ?? '') . ($customer_info['city'] ?? '');
    if(empty($customer_info['full_address'])){ $customer_info['full_address'] = '（住所未登録）'; }
    if(empty($customer_info['phone_number'])){ $customer_info['phone_number'] = '（電話番号未登録）'; }
    if(empty($customer_info['payment_method'])){ $customer_info['payment_method'] = '（支払方法未登録）'; }

    
    // 6. SQL 2: 購入履歴の取得 (最新5件)
    // ▼▼▼ 修正点 1: t.delivery_status を SELECT に追加 ▼▼▼
    $sql_purchase = "SELECT p.product_name, p.product_image, t.transaction_id AS tid, t.delivery_status
                       FROM transaction_table AS t
                       JOIN transaction_detail AS d ON t.transaction_id = d.transaction_id
                       JOIN product AS p ON d.product_id = p.product_id
                       WHERE t.customer_id = :id AND t.transaction_type = '購入'
                       ORDER BY t.transaction_date DESC
                       LIMIT 5"; // 最新5件のみ表示
    $stmt_purchase = $pdo->prepare($sql_purchase);
    $stmt_purchase->bindValue(':id', $customer_id, PDO::PARAM_INT);
    $stmt_purchase->execute();
    $purchase_history = $stmt_purchase->fetchAll(PDO::FETCH_ASSOC);

    // 7. SQL 3: レンタル履歴の取得 (最新5件)
    // ▼▼▼ 修正点 2: t.delivery_status を SELECT に追加 ▼▼▼
    $sql_rental = "SELECT p.product_name, p.product_image, t.transaction_id AS tid, t.delivery_status
                       FROM transaction_table AS t
                       JOIN rental AS r ON t.transaction_id = r.transaction_id
                       JOIN product AS p ON r.product_id = p.product_id
                       WHERE t.customer_id = :id AND t.transaction_type = 'レンタル'
                       ORDER BY t.transaction_date DESC
                       LIMIT 5"; // 最新5件のみ表示
    $stmt_rental = $pdo->prepare($sql_rental);
    $stmt_rental->bindValue(':id', $customer_id, PDO::PARAM_INT);
    $stmt_rental->execute();
    $rental_history = $stmt_rental->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// ▼▼▼ 修正点 3: G-16と同じヘルパー関数を追加 ▼▼▼
function getStatusClass($status) {
    if ($status == 'キャンセル済み') return 'status-cancelled';
    if ($status == '配達完了' || $status == '返却済み') return 'status-delivered';
    return 'status-processing'; // 注文受付、レンタル中 など
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-4_member-information.css">
</head>
<body>
    
    <?php require '../common/header.php'; ?><hr>

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

            <section class="history-section">
                <h2 class="section-title">購入履歴</h2>
                <div class="history-items">
                    <?php if (empty($purchase_history)): ?>
                        <p class="no-history">購入履歴はありません。</p>
                    <?php else: ?>
                        <?php foreach ($purchase_history as $item): ?>
                            <?php
                                // ▼▼▼ 修正点 4: ステータスに応じてCSSクラスを準備 ▼▼▼
                                $status_class = getStatusClass($item['delivery_status']);
                                $status_text = htmlspecialchars($item['delivery_status']);
                            ?>
                            <a href="G-16_order-history.php?id=<?php echo $item['tid']; ?>" class="history-item">
                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                                <p class="history-status <?php echo $status_class; ?>"><?php echo $status_text; ?></p>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="history-section">
                <h2 class="section-title">レンタル履歴</h2>
                <div class="history-items">
                    <?php if (empty($rental_history)): ?>
                        <p class="no-history">レンタル履歴はありません。</p>
                    <?php else: ?>
                        <?php foreach ($rental_history as $item): ?>
                            <?php
                                // ▼▼▼ 修正点 5: ステータスに応じてCSSクラスを準備 ▼▼▼
                                $status_class = getStatusClass($item['delivery_status']);
                                $status_text = htmlspecialchars($item['delivery_status']);
                            ?>
                            <a href="G-17_rental-history.php?id=<?php echo $item['tid']; ?>" class="history-item">
                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                                <p class="history-status <?php echo $status_class; ?>"><?php echo $status_text; ?></p>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($customer_info): ?>
            <section class="info-section">
                <h2 class="section-title">会員情報</h2>
                <div class="info-group">
                    <label for="name">お名前</label>
                    <input type="text" id="name" value="<?php echo htmlspecialchars($customer_info['customer_name']); ?>" readonly>
                </div>
                <div class="info-group">
                    <label for="address">ご住所</label>
                    <input type="text" id="address" value="<?php echo htmlspecialchars($customer_info['full_address']); ?>" readonly>
                </div>
                <div class="info-group">
                    <label for="phone">電話番号</label>
                    <input type="text" id="phone" value="<?php echo htmlspecialchars($customer_info['phone_number']); ?>" readonly>
                </div>
                <div class="info-group">
                    <label for="email">e-mail</label>
                    <input type="text" id="email" value="<?php echo htmlspecialchars($customer_info['email']); ?>" readonly>
                </div>
                <div class="info-group">
                    <label for="payment">お支払方法</label>
                    <input type="text" id="payment" value="<?php echo htmlspecialchars($customer_info['payment_method']); ?>" readonly>
                </div>

                <a href="G-5_member-change.php" class="btn btn-edit">会員情報変更画面へ</a>
            </section>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>