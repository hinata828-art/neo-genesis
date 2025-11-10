<?php
// 1. セッションとエラー表示
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. DB接続
require '../common/db_connect.php'; // $pdo が定義されていると仮定

// 3. 顧客IDの取得（ログインセッションから）
// ★注意： 'customer' のキー名は、実際のログイン処理で保存したキー名に変更してください
if (isset($_SESSION['customer']['id'])) {
    $customer_id = $_SESSION['customer']['id'];
} else {
    // ログインしていない場合のテスト用（またはログインページへ強制送還）
    // $customer_id = 1; 
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
    // 5. SQL 1: 会員情報の取得 (customerテーブルから)
    // (※住所は address テーブルにあると仮定してLEFT JOIN)
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
    
    // 住所を結合 (addressテーブルにデータがない場合も考慮)
    $customer_info['full_address'] = ($customer_info['prefecture'] ?? '') . ($customer_info['city'] ?? '');
    if(empty($customer_info['full_address'])){
        $customer_info['full_address'] = '（住所未登録）';
    }

    // 電話番号が空の場合
    if(empty($customer_info['phone_number'])){
        $customer_info['phone_number'] = '（電話番号未登録）';
    }
    
    // 支払方法が空の場合
    if(empty($customer_info['payment_method'])){
        $customer_info['payment_method'] = '（支払方法未登録）';
    }

    
    // 6. SQL 2: 購入履歴の取得 (最新5件)
    // 6. SQL 2: 購入履歴の取得 (最新5件)
    $sql_purchase = "SELECT 
                        p.product_name, 
                        p.product_image, 
                        t.transaction_id AS tid -- ★ 'transaction_id' から 'tid' に別名を付けます
                    FROM transaction_table AS t
                    JOIN transaction_detail AS d ON t.transaction_id = d.transaction_id
                    JOIN product AS p ON d.product_id = p.product_id
                    WHERE t.customer_id = :id AND t.transaction_type = '購入'
                    ORDER BY t.transaction_date DESC
                    LIMIT 5";
    $stmt_purchase = $pdo->prepare($sql_purchase);
    $stmt_purchase->bindValue(':id', $customer_id, PDO::PARAM_INT);
    $stmt_purchase->execute();
    $purchase_history = $stmt_purchase->fetchAll(PDO::FETCH_ASSOC);

    // 7. SQL 3: レンタル履歴の取得 (最新5件)
    // 7. SQL 3: レンタル履歴の取得 (最新5件)
    $sql_rental = "SELECT 
                    p.product_name, 
                    p.product_image, 
                    t.transaction_id AS tid -- ★ こちらも 'tid' に別名を付けます
                FROM transaction_table AS t
                JOIN transaction_detail AS d ON t.transaction_id = d.transaction_id
                JOIN product AS p ON d.product_id = p.product_id
                WHERE t.customer_id = :id AND t.transaction_type = 'レンタル'
                ORDER BY t.transaction_date DESC
                LIMIT 5";
    $stmt_rental = $pdo->prepare($sql_rental);
    $stmt_rental->bindValue(':id', $customer_id, PDO::PARAM_INT);
    $stmt_rental->execute();
    $rental_history = $stmt_rental->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_message = $e->getMessage();
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
                    <?php foreach ($purchase_history as $item): ?>
                        <a href="G-16_order-history.php?id=<?php echo $item['tid']; ?>" class="history-item">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                        </a>
                    <?php endforeach; ?>
                    </div>
            </section>

            <section class="history-section">
                <h2 class="section-title">レンタル履歴</h2>
                <div class="history-items">
                    <?php foreach ($rental_history as $item): ?>
                        <a href="G-17_rental-history.php?id=<?php echo $item['tid']; ?>" class="history-item">
                            <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                        </a>
                    <?php endforeach; ?>
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
