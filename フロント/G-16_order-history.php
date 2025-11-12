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
$transaction_id = $_GET['id'] ?? 0; // ★ transaction_id を初期化

try {
    // 5. URLから表示したい取引IDを取得
    if (empty($transaction_id)) {
        throw new Exception('取引IDが指定されていません。');
    }
    
    // 6. データベースから注文情報を取得
    $sql = "SELECT 
                t.transaction_date, 
                t.payment,
                t.delivery_status,
                t.total_amount,
                p.product_id,
                p.product_name, 
                p.product_image,
                p.price
            FROM transaction_table AS t
            JOIN transaction_detail AS d ON t.transaction_id = d.transaction_id
            JOIN product AS p ON d.product_id = p.product_id
            WHERE t.transaction_id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        throw new Exception('該当する注文が見つかりません。');
    }
    
    // 注文共通情報（日付、支払い方法など）を $products の最初の要素から取得
    $order_info = $products[0];
    
    // 日付フォーマットの整形
    $order_info['purchase_date_formatted'] = date('Y/m/d H:i', strtotime($order_info['transaction_date']));
    
    
    // ▼▼▼ ここから修正 ▼▼▼
    // 62行目あたり
    // 配達状況のテキストとCSSクラスを決定
    if ($order_info['delivery_status'] == '配達完了') {
         $order_info['delivery_status_text'] = date('Y/m/d', strtotime('+5 days', strtotime($order_info['transaction_date']))) . 'に配達済み';
         $order_info['status_class'] = 'status-delivered'; // (緑色)

    } else if ($order_info['delivery_status'] == 'キャンセル済み') {
         $order_info['delivery_status_text'] = 'この注文はキャンセル済みです';
         $order_info['status_class'] = 'status-cancelled'; // (★ 赤色)

    } else {
         $order_info['delivery_status_text'] = '配達ステータス: ' . htmlspecialchars($order_info['delivery_status']); // (例: 注文受付)
         $order_info['status_class'] = 'status-processing'; // (青色)
    }
    // ▲▲▲ 修正ここまで ▲▲▲


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
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-16_order-history.css">
</head>
<body>
    <?php require '../common/header.php'; // ヘッダーを読み込む ?>
    <div class="container">

        <header class="header">
        <a href="G-4_member-information.php"><img src="../img/modoru.png" alt="戻る" class="back-link"></a>
            <h1 class="header-title">購入履歴</h1>
            <span class="header-dummy"></span>
        </header>

        <main class="main-content">

            <?php if (!empty($error_message)): ?>
                <div class="error-box">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
                
            <?php elseif (!empty($products)): ?>
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
                            <a href="G-9_product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-detail">詳細</a>
                            <a href="G-10_cart-insert.php?id=<?php echo $product['product_id']; ?>" class="btn btn-purchase">再度購入</a>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="detail-section">
                    <h2 class="section-title">注文の詳細</h2>
                    <div class="detail-box">
                        <div class="detail-row">
                            <span class="detail-label">購入日時</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order_info['purchase_date_formatted']); ?></span>
                        </div>
                        </div>
                </section>

                <section class="detail-section">
                    <h2 class="section-title">お支払方法</h2>
                    <div class="detail-box">
                        <p class="payment-info"><?php echo htmlspecialchars($order_info['payment']); ?> <?php echo number_format($order_info['total_amount']); ?>yen</p>
                    </div>
                </section>
                
                <section class="delivery-status">
                    <p class="<?php echo htmlspecialchars($order_info['status_class']); ?>">
                        <?php echo htmlspecialchars($order_info['delivery_status_text']); ?>
                    </p>
                </section>
                <?php endif; ?>

        </main>

       <footer class="footer">
            <a href="#" id="open-cancel-modal" class="footer-link">購入キャンセルはコチラ</a>
        </footer>

    </div> <div id="cancel-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            
            <button id="close-modal" class="modal-close-btn">&times;</button>
            
            <div class="modal-icon">
                <img src="../img/alert.png" alt="" style="width: 60px; height: 60px;">
            </div>

            <h2>キャンセルしますか？</h2>
            
            <div class="modal-buttons">
                <a href="G_transaction-cancel.php?id=<?php echo htmlspecialchars($transaction_id); ?>" id="confirm-yes" class="btn btn-danger">はい</a>
                
                <button id="confirm-no" class="btn btn-secondary">いいえ</button>
            </div>
        </div>
    </div>
    
    <script>
    // ページのHTMLが読み込まれたら実行
    document.addEventListener('DOMContentLoaded', function() {
        
        // 必要な部品（HTML要素）を取得
        const modal = document.getElementById('cancel-modal');
        const openBtn = document.getElementById('open-cancel-modal');
        const closeBtn = document.getElementById('close-modal');
        const noBtn = document.getElementById('confirm-no');

        // 「購入キャンセルはコチラ」リンクがクリックされた時
        // ★ openBtn が null でないか（要素が存在するか）確認
        if (openBtn) {
            openBtn.addEventListener('click', function(e) {
                e.preventDefault(); // リンクのデフォルト動作（ページ遷移）を止める
                modal.style.display = 'flex'; // モーダルを表示する
            });
        }

        // 「いいえ」ボタンがクリックされた時
        if (noBtn) {
            noBtn.addEventListener('click', function() {
                modal.style.display = 'none'; // モーダルを非表示にする
            });
        }

        // 「×」ボタンがクリックされた時
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none'; // モーダルを非表示にする
            });
        }

        // モーダルの背景（黒い部分）がクリックされた時
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) { // クリックされたのが背景自身か確認
                    modal.style.display = 'none'; // モーダルを非表示にする
                }
            });
        }
    });
    </script>

</body>
</html>