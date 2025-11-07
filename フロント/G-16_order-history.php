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
$order = null; // データが存在しない場合は null のまま

try {
    // 5. URLから表示したい取引IDを取得 (例: purchase_detail.php?id=1)
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];
    
    // 6. データベースから注文情報を取得
    // 必要なテーブルをJOINする
    $sql = "SELECT 
                t.transaction_date, 
                t.payment,
                t.delivery_status,
                t.total_amount,
                p.product_name, 
                p.product_image,
                p.price
            FROM transaction_table AS t
            JOIN transaction_detail AS d ON t.transaction_id = d.transaction_id
            JOIN product AS p ON d.product_id = p.product_id
            WHERE t.transaction_id = :id
            LIMIT 1"; // 1注文に複数商品がある場合、代表の1件を表示

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('該当する注文が見つかりません。');
    }
    
    // 日付フォーマットの整形 (例: 2025-11-05 -> 2025/11/05)
    $order['purchase_date_formatted'] = date('Y/m/d H:i', strtotime($order['transaction_date']));
    
    // 配達状況のテキスト（例）
    // (DBの delivery_status の値によって分岐させる)
    if ($order['delivery_status'] == '配達完了') {
         $order['delivery_status_text'] = date('Y/m/d', strtotime('+5 days', strtotime($order['transaction_date']))) . 'に配達済み';
    } else {
         $order['delivery_status_text'] = '配達ステータス: ' . htmlspecialchars($order['delivery_status']);
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
    <title>ご購入履歴２</title>
    <!-- 外部CSSファイル（G-16_order-history.css）を読み込む -->
    <link rel="stylesheet" href="../css/G-16_order-history.css">
</head>
<body>
    <?php require '../common/header.php'; // ★ヘッダーは通常ここに配置します ?>
    <div class="container">
        <!-- 1. ヘッダー -->
        <header class="header">
            <a href="G-4_member-information.php" class="back-link">&lt;</a> <!-- 履歴一覧に戻る（仮） -->
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
            <?php elseif ($order): ?>
                <!-- 正常表示 -->
                
                <!-- 2. 商品カード (上部) -->
                <section class="product-card">
                    <div class="product-image-container">
                        <img src="<?php echo htmlspecialchars($order['product_image']); ?>" alt="商品画像" class="product-image">
                    </div>
                    <div class="product-info">
                        <h2 class="product-name"><?php echo htmlspecialchars($order['product_name']); ?></h2>
                        <p class="product-price">¥<?php echo number_format($order['price']); ?></p>
                    </div>
                    <div class="button-group">
                        <button class="btn btn-detail">詳細</button>
                        <button class="btn btn-purchase">再度購入</button>
                    </div>
                </section>

                <!-- 3. ご注文の詳細 -->
                <section class="detail-section">
                    <h2 class="section-title">ご注文の詳細</h2>
                    <div class="detail-box">
                        <div class="detail-row">
                            <span class="detail-label">ご購入日時</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['purchase_date_formatted']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">商品名</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['product_name']); ?></span>
                        </div>
                    </div>
                </section>

                <!-- 4. お支払方法 -->
                <section class="detail-section">
                    <h2 class="section-title">お支払方法</h2>
                    <div class="detail-box">
                        <p class="payment-info"><?php echo htmlspecialchars($order['payment']); ?> <?php echo number_format($order['total_amount']); ?>yen</p>
                    </div>
                </section>
                
                <!-- 5. 配送状況 -->
                <section class="delivery-status">
                    <p><?php echo htmlspecialchars($order['delivery_status_text']); ?></p>
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