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
$products = []; // 商品リスト用の配列
$order_info = null; // 注文・レンタル共通情報用
$error_message = '';
$is_cancellable = false; // ★【追加】キャンセル可能か判定するフラグ
$transaction_id = 0; // ★【追加】IDを保持する変数

try {
    // 5. URLから表示したい「取引ID」を取得
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id']; // ★ rental_id から変更
    
    // 6. データベースからレンタル情報を取得
    // ★★★ SQLクエリを「transaction_table(親)」と「rental(子)」のJOINに修正 ★★★
    $sql = "SELECT 
                t.transaction_date,    /* 取引日 (親テーブルから) */
                t.payment,             /* 支払い方法 (親テーブルから) */
                t.delivery_status,     /* ★配送状況 (親テーブルから) */
                t.total_amount,        /* 合計金額 (親テーブルから) */
                p.product_id,
                p.product_name, 
                p.product_image,
                p.price,
                r.rental_start,        /* レンタル開始日 (子テーブルから) */
                r.rental_end           /* レンタル終了日 (子テーブルから) */
            FROM transaction_table AS t
            JOIN rental AS r ON t.transaction_id = r.transaction_id /* ★修正★ */
            JOIN product AS p ON r.product_id = p.product_id       /* ★修正★ */
            WHERE t.transaction_id = :id"; 

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        throw new Exception('該当するレンタル履歴が見つかりません。');
    }
    
    // 注文共通情報を $products の最初の要素から取得
    $order_info = $products[0];
    
    // 日付フォーマットの整形 (DBのカラム名に合わせる)
    $order_info['start_date_formatted'] = date('Y/m/d H:i', strtotime($order_info['rental_start']));
    $order_info['return_date_formatted'] = date('Y/m/d', strtotime($order_info['rental_end']));
    
    // ★★★ 【修正】ステータスに応じて表示テキストと「キャンセル可否」を決定 ★★★
    switch ($order_info['delivery_status']) { 
        case '注文受付':
            $order_info['return_status_text'] = '発送準備中です';
            $is_cancellable = true; // ★「注文受付」の時だけキャンセル可能
            break;
        case 'レンタル中':
            $order_info['return_status_text'] = '返却予定日: ' . $order_info['return_date_formatted'];
            break;
        case '返却済み':
            $order_info['return_status_text'] = '返却完了済み';
            break;
        case 'キャンセル済み': // ★「キャンセル済み」の場合の表示を追加
            $order_info['return_status_text'] = 'この取引はキャンセルされました';
            break;
        default:
            $order_info['return_status_text'] = 'ステータス: ' . htmlspecialchars($order_info['delivery_status']);
            break;
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
    <title>レンタル履歴詳細</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-16_order-history.css"> 
</head>
<body>
    <?php require '../common/header.php'; // ヘッダーを読み込む ?>
    <div class="container">

        <header class="header">
        <a href="G-4_member-information.php"><img src="../img/modoru.png" alt="戻る" class="back-link"></a>
            <h1 class="header-title">レンタル履歴</h1>
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
                            <p class="product-price">単価: ¥<?php echo number_format($product['price']); ?></p> 
                        </div>
                        <div class="button-group">
                            <a href="G-5_product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-detail">詳細</a>
                            <a href="G-10_cart-insert.php?id=<?php echo $product['product_id']; ?>" class="btn btn-purchase">再度レンタル</a>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="detail-section">
                    <h2 class="section-title">レンタル期間</h2>
                    <div class="detail-box">
                        <div class="detail-row">
                            <span class="detail-label">レンタル開始日時</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order_info['start_date_formatted']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">返却予定日</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order_info['return_date_formatted']); ?></span>
                        </div>
                    </div>
                </section>

                <section class="detail-section">
                    <h2 class="section-title">お支払情報</h2>
                    <div class="detail-box">
                        <p class="payment-info">
                            <?php echo htmlspecialchars($order_info['payment']); ?> | 
                            合計金額: ¥<?php echo number_format($order_info['total_amount']); ?>
                        </p>
                    </div>
                </section>
                
                <section class="delivery-status">
                    <p><?php echo htmlspecialchars($order_info['return_status_text']); ?></p>
                </section>

            <?php endif; ?>

        </main>

       <footer class="footer">
            <?php if ($is_cancellable): ?>
                <a href="#" id="open-cancel-modal" class="footer-link">レンタルキャンセルはコチラ</a>
            <?php else: ?>
                <?php endif; ?>
       </footer>

    </div> 
    
    <div id="cancel-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            
            <button id="close-modal" class="modal-close-btn">&times;</button>
            
            <div class="modal-icon">
                <img src="../img/alert.png" alt="" style="width: 60px; height: 60px;">
            </div>

            <h2>レンタルをキャンセルしますか？</h2>
            
            <div class="modal-buttons">
                <a href="G-17_rental-cancel.php?id=<?php echo htmlspecialchars($transaction_id); ?>" id="confirm-yes" class="btn btn-danger">はい</a>
                
                <button id="confirm-no" class="btn btn-secondary">いいえ</button>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // ★ フッターのリンクが存在する場合のみ、イベントリスナーを登録
        const openBtn = document.getElementById('open-cancel-modal');
        if (openBtn) {
            const modal = document.getElementById('cancel-modal');
            const closeBtn = document.getElementById('close-modal');
            const noBtn = document.getElementById('confirm-no');

            openBtn.addEventListener('click', function(e) {
                e.preventDefault(); 
                modal.style.display = 'flex'; 
            });

            noBtn.addEventListener('click', function() {
                modal.style.display = 'none'; 
            });

            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none'; 
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) { 
                    modal.style.display = 'none'; 
                }
            });
        }
    });
    </script>

</body>
</html>