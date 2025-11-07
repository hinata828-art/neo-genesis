<?php
// 1. セッションを開始
session_start();

// 2. デバッグ（エラー表示）設定 (開発中のみ)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. 共通のデータベース接続ファイルを読み込む
require '../common/db_connect.php'; // $pdo 変数がここで作成されると仮定

// 4. 表示するデータを初期化
$products = []; // ★商品リスト用の配列に変更
$order_info = null; // 注文共通情報用
$error_message = '';

try {
    // 5. URLから表示したい取引IDを取得
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];
    
    // 6. データベースから注文情報を取得
    // (SQLを修正：商品情報(p)と取引情報(t)を両方取得)
    $sql = "SELECT 
                t.transaction_date, 
                t.payment,
                t.delivery_status,
                t.total_amount,
                p.product_id, /* ★追加 */
                p.product_name, 
                p.product_image,
                p.price
            FROM transaction_table AS t
            JOIN transaction_detail AS d ON t.transaction_id = d.transaction_id
            JOIN product AS p ON d.product_id = p.product_id
            WHERE t.transaction_id = :id";
            /* ★ LIMIT 1 を削除 ★ */

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // ★ fetchAll に変更し、すべての商品を取得
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        throw new Exception('該当する注文が見つかりません。');
    }
    
    // 注文共通情報（日付、支払い方法など）を $products の最初の要素から取得
    $order_info = $products[0];
    
    // 日付フォーマットの整形
    $order_info['purchase_date_formatted'] = date('Y/m/d H:i', strtotime($order_info['transaction_date']));
    
    // 配達状況のテキスト（例）
    if ($order_info['delivery_status'] == '配達完了') {
         $order_info['delivery_status_text'] = date('Y/m/d', strtotime('+5 days', strtotime($order_info['transaction_date']))) . 'に配達済み';
    } else {
         $order_info['delivery_status_text'] = '配達ステータス: ' . htmlspecialchars($order_info['delivery_status']);
    }


} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご購入履歴</title>
    <!-- 外部CSSファイル（G-16_order-history.css）を読み込む -->
    <link rel="stylesheet" href="../css/G-16_order-history.css">
</head>
<body>
    <?php require '../common/header.php'; // ヘッダーを読み込む ?>
    <div class="container">
        <!-- 1. ヘッダー -->
        <header class="header">
            <a href="G-4_member-information.php" class="back-link">&lt;</a> 
            <h1 class="header-title">ご購入履歴</h1>
            <span class="header-dummy"></span>
        </header>

        <!-- メインコンテンツ -->
        <main class="main-content">

            <?php if (isset($error_message)): ?>
                <!-- エラー表示 -->
                <div class="error-box">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
                
            <?php elseif (!empty($products)): ?>
                <!-- 正常表示 -->
                
                <!-- 2. 商品カード (★foreachでループ処理に変更★) -->
                <?php foreach ($products as $product): ?>
                    <section class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="商品画像" class="product-image">
                        </div>
                        <div class="product-info">
                            <h2 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                            <p class="product-price">¥<?php echo number_format($product['price']); ?></p>
                        </div>
                        <div class="button-group">
                            <a href="G-5_product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-detail">詳細</a>
                            <a href="G-10_cart-insert.php?id=<?php echo $product['product_id']; ?>" class="btn btn-purchase">再度購入</a>
                        </div>
                    </section>
                <?php endforeach; ?>

                <!-- 3. ご注文の詳細 (共通情報を表示) -->
                <section class="detail-section">
                    <h2 class="section-title">ご注文の詳細</h2>
                    <div class="detail-box">
                        <div class="detail-row">
                            <span class="detail-label">ご購入日時</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order_info['purchase_date_formatted']); ?></span>
                        </div>
                        <!-- 「商品名」は複数あるため、ここでは非表示にするか、代表商品名を表示します -->
                    </div>
                </section>

                <!-- 4. お支払方法 (共通情報を表示) -->
                <section class="detail-section">
                    <h2 class="section-title">お支払方法</h2>
                    <div class="detail-box">
                        <p class="payment-info"><?php echo htmlspecialchars($order_info['payment']); ?> <?php echo number_format($order_info['total_amount']); ?>yen</p>
                    </div>
                </section>
                
                <!-- 5. 配送状況 (共通情報を表示) -->
                <section class="delivery-status">
                    <p><?php echo htmlspecialchars($order_info['delivery_status_text']); ?></p>
                </section>

            <?php endif; ?>

        </main>

        <!-- 6. フッターリンク -->
        <footer class="footer">
            <a href="#" class="footer-link">購入キャンセルはコチラ</a>
        </footer>

    </div>

</body>
</html>