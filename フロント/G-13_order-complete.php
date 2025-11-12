<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php'; 

// === 処理の前に：G-12からのPOSTデータを受け取る ===

// G-12のフォームから送られた情報をPOSTで受け取ります
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('不正なアクセスです。');
}

// 顧客IDをセッションから取得 (G-1のログイン処理に合わせて 'id' を使用)
$customer_id = $_SESSION['customer']['id'] ?? null; 
if ($customer_id === null) {
    // もし 'id' キーまで無い場合は、本当にログインしていないかセッションが壊れている
    exit('ログイン情報（顧客ID）がセッションに見つかりません。');
}

// G-12から送られてきた商品ID、合計金額、支払い方法
$product_id = $_POST['product_id'] ?? 0;
$total_amount = $_POST['total_amount'] ?? 0;
$payment_method = $_POST['payment'] ?? '不明';
$color_value = $_POST['color'] ?? '不明'; 

// オプション（チェックされていれば '500' や '1' が入る）
$option_warranty = $_POST['option_warranty'] ?? null;
$option_delivery = $_POST['option_delivery'] ?? null;


// === DB接続と初期化 ===
$connect = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';
$order_info = [
    'transaction_id' => '---',
    'total_amount' => $total_amount, // POSTされた金額を先に入れる
    'payment' => $payment_method,   // POSTされた支払い方法を先に入れる
    'delivery_days' => '---'
];


try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // === データベース書き込み処理 ===
    
    $pdo->beginTransaction();

    // 1. transaction_table への INSERT
    $sql_transaction = "INSERT INTO transaction_table 
                            (customer_id, transaction_type, transaction_date, payment, delivery_status, total_amount)
                        VALUES
                            (?, '購入', NOW(), ?, '注文受付', ?)";
    
    $stmt_transaction = $pdo->prepare($sql_transaction);
    $stmt_transaction->execute([
        $customer_id,
        $payment_method,
        $total_amount
    ]);

    // 2. 今 INSERT した取引の「transaction_id」を取得
    $new_transaction_id = $pdo->lastInsertId();
    $order_info['transaction_id'] = $new_transaction_id; // 表示用に保存

    // 3. transaction_detail への INSERT
    $sql_detail = "INSERT INTO transaction_detail 
                       (transaction_id, product_id, quantity)
                   VALUES
                       (?, ?, 1)";
    
    $stmt_detail = $pdo->prepare($sql_detail);
    $stmt_detail->execute([
        $new_transaction_id,
        $product_id
    ]);

    // 4. すべて成功したら、トランザクションを「コミット」（確定）
    $pdo->commit();

    // === 配送日数SELECT処理 ===
    // 5. 今 INSERT した $new_transaction_id を使って、配送日数を取得
    
    $sql_delivery = "
        SELECT c.delivery_days
        FROM transaction_detail td
        JOIN product p ON td.product_id = p.product_id
        JOIN category c ON p.category_id = c.category_id
        WHERE td.transaction_id = ?
        LIMIT 1
    ";

    $stmt_delivery = $pdo->prepare($sql_delivery);
    $stmt_delivery->execute([$new_transaction_id]); // ◀ 取得したてのIDを使う
    $delivery = $stmt_delivery->fetch(PDO::FETCH_ASSOC);

    if ($delivery) {
        $order_info['delivery_days'] = $delivery['delivery_days'];
    } else {
        $order_info['delivery_days'] = '配送情報未設定';
    }


} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $order_info['delivery_days'] = '注文処理エラー: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>購入完了</title>
    <link rel="stylesheet" href="../css/G-13_order-complet.css">
</head>
<body>

    <img src="../img/NishimuraOnline.png" alt="ニシムラOnline" class="completion-logo">

    <div class="message-area">
        ご購入ありがとうございました！
    </div>

    <div class="delivery-date">
        配送予定日数 : 
        <span>
            <?php
            // エラーや未設定の場合は「日後」の文字を表示しないように制御
            if (is_numeric($order_info['delivery_days'])) {
                echo htmlspecialchars($order_info['delivery_days']) . '日後';
            } else {
                echo htmlspecialchars($order_info['delivery_days']);
            }
            ?>
        </span>
    </div>

    <a href="G-8_home.php" class="home-button">ホーム画面へ戻る</a>

</body>
</html>