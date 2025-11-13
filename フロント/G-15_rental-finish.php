<?php
// 1. セッションとDB接続
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php'; 

// === 1. G-14からのPOSTデータを受け取る ===

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('不正なアクセスです。');
}

// 顧客ID (G-1のログイン処理に合わせて 'id' を使用)
$customer_id = $_SESSION['customer']['id'] ?? null; 
if ($customer_id === null) {
    exit('ログイン情報（顧客ID）がセッションに見つかりません。');
}

// G-14から送られてきた情報
$base_product_id = $_POST['product_id'] ?? 0;   // (例: 14)
$selected_color_file_name = $_POST['color'] ?? 'original'; // (例: '白色' や 'original')
$total_amount = $_POST['total_amount'] ?? 0; // G-14のJSが計算した合計金額
$payment_method = $_POST['payment'] ?? '不明';
$rental_term = $_POST['rental_term'] ?? '1month'; // (例: '1month')

// オプション
$option_warranty = $_POST['option_warranty'] ?? null; // (必須の '500')
$option_delivery = $_POST['option_delivery'] ?? null;
$option_buy = $_POST['option_buy'] ?? null;


// === 2. G-12/G-13と同じ「逆引き辞書」を定義 ===
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

// 選択された色の「表示名」(DBのcolorカラムと一致する名前) (例: '白色' -> 'ホワイト')
$selected_color_display_name = $color_display_map[$selected_color_file_name] ?? $selected_color_file_name;


// === 3. DB接続と初期化 ===
$connect = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';
$order_info = [
    'transaction_id' => '---',
    'total_amount' => $total_amount,
    'payment' => $payment_method,
    'delivery_days' => '---' // G-13同様、配送日数を表示
];
$final_product_id = $base_product_id; // ひとまずベースIDをセット


try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // === 4. ▼▼▼ 正しい商品IDを検索する (G-13と同じロジック) ▼▼▼ ===

    if ($selected_color_display_name !== 'オリジナル') {
        // (A) 色が選択された場合 (例: 'ホワイト')
        $sql_get_name = "SELECT product_name FROM product WHERE product_id = ?";
        $stmt_get_name = $pdo->prepare($sql_get_name);
        $stmt_get_name->execute([$base_product_id]);
        $base_product = $stmt_get_name->fetch(PDO::FETCH_ASSOC);
        
        if ($base_product) {
            $base_product_name = $base_product['product_name'];

            $sql_find_variant = "SELECT product_id FROM product WHERE product_name = ? AND color = ?";
            $stmt_find_variant = $pdo->prepare($sql_find_variant);
            $stmt_find_variant->execute([$base_product_name, $selected_color_display_name]);
            $variant_product = $stmt_find_variant->fetch(PDO::FETCH_ASSOC);

            if ($variant_product) {
                $final_product_id = $variant_product['product_id'];
            }
        }
    }
    // === ▲▲▲ 商品ID検索ロジックここまで ▲▲▲ ===


    // === 5. データベース書き込み処理 ===
    
    $pdo->beginTransaction();

    // 1. transaction_table への INSERT
    $sql_transaction = "INSERT INTO transaction_table 
                            (customer_id, transaction_type, transaction_date, payment, delivery_status, total_amount)
                        VALUES
                            (?, 'レンタル', NOW(), ?, '注文受付', ?)"; // ★ transaction_type を「レンタル」に変更
    
    $stmt_transaction = $pdo->prepare($sql_transaction);
    $stmt_transaction->execute([
        $customer_id,
        $payment_method,
        $total_amount
    ]);

    // 2. 今 INSERT した取引の「transaction_id」を取得
    $new_transaction_id = $pdo->lastInsertId();
    $order_info['transaction_id'] = $new_transaction_id;

    // 3. ▼▼▼ rental テーブル への INSERT (G-13との最大の違い) ▼▼▼
    // (G-4.php のSQLを参考に、rental テーブルにINSERTすると仮定)
    $sql_rental_detail = "INSERT INTO rental 
                            (transaction_id, product_id, rental_term, quantity)
                          VALUES
                            (?, ?, ?, 1)"; // (quantity=1 と仮定)
    
    $stmt_rental_detail = $pdo->prepare($sql_rental_detail);
    $stmt_rental_detail->execute([
        $new_transaction_id,
        $final_product_id, // ★色を反映した商品ID
        $rental_term       // ★G-14からPOSTされたレンタル期間
    ]);
    // ▲▲▲ 修正ここまで ▲▲▲

    // 4. すべて成功したら、トランザクションを「コミット」（確定）
    $pdo->commit();

    // === 6. 配送日数SELECT処理 (G-13と同じ) ===
    // (JOINするテーブルを rental -> product に変更)
    $sql_delivery = "
        SELECT c.delivery_days
        FROM rental r
        JOIN product p ON r.product_id = p.product_id
        JOIN category c ON p.category_id = c.category_id
        WHERE r.transaction_id = ?
        LIMIT 1
    ";
    $stmt_delivery = $pdo->prepare($sql_delivery);
    $stmt_delivery->execute([$new_transaction_id]);
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
<html lang="ja"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>レンタル完了</title>
    <link rel="stylesheet" href="../css/G-13_order-complete.css">
</head>
<body>

    <img src="../img/NishimuraOnline.png" alt="ニシムラOnline" class="completion-logo">

    <div class="message-area">
        レンタルのお申し込みありがとうございました！
    </div>

    <div class="order-summary">
        <p><strong>配送予定日数：</strong>
        <?php
            // エラーや未設定の場合は「日後」の文字を表示しないように制御
            if (is_numeric($order_info['delivery_days'])) {
                echo htmlspecialchars($order_info['delivery_days']) . '日後に発送予定';
            } else {
                echo htmlspecialchars($order_info['delivery_days']);
            }
        ?>
        </p>
    </div>

    <?php if ($order_info['transaction_id'] !== '---'): ?>
        <a href="G-17_rental-history.php?id=<?php echo htmlspecialchars($order_info['transaction_id']); ?>" class="detail-button">注文詳細を見る</a>
    <?php endif; ?>

    <a href="G-8_home.php" class="home-button">ホーム画面へ戻る</a>

</body>
</html>