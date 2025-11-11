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
$products = []; // レンタル商品リスト用の配列
$rental_info = null; // レンタル共通情報用
$error_message = '';

try {
    // 5. URLから表示したいレンタルIDを取得（取引IDからレンタルIDに変更）
    if (!isset($_GET['id'])) {
        throw new Exception('レンタルIDが指定されていません。');
    }
    $rental_id = $_GET['id'];
    
    // 6. データベースからレンタル情報を取得
    // テーブル名とカラム名をレンタル用に変更
    $sql = "SELECT 
                r.rental_start_date,    /* レンタル開始日 */
                r.return_status,        /* 返却ステータス */
                r.total_amount,         /* 合計金額（レンタル料） */
                r.payment,              /* 支払い方法 */
                r.return_date_scheduled, /* 返却予定日を仮定 */
                p.product_id,           /* 商品ID */
                p.product_name,         /* 商品名 */
                p.product_image,        /* 商品画像 */
                p.price                 /* 商品価格（参考情報） */
            FROM rental_table AS r
            JOIN rental_detail AS d ON r.rental_id = d.rental_id
            JOIN product AS p ON d.product_id = p.product_id
            WHERE r.rental_id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $rental_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // すべてのレンタル商品を取得
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        throw new Exception('該当するレンタル履歴が見つかりません。');
    }
    
    // レンタル共通情報（開始日、ステータスなど）を $products の最初の要素から取得
    $rental_info = $products[0];
    
    // 日付フォーマットの整形
    $rental_info['start_date_formatted'] = date('Y/m/d H:i', strtotime($rental_info['rental_start_date']));
    $rental_info['return_date_formatted'] = date('Y/m/d', strtotime($rental_info['return_date_scheduled']));
    
    // 返却状況のテキストを整形
    switch ($rental_info['return_status']) {
        case 'レンタル中':
            $rental_info['return_status_text'] = '返却予定日: ' . $rental_info['return_date_formatted'];
            break;
        case '返却済み':
            $rental_info['return_status_text'] = '返却完了済み';
            break;
        default:
            $rental_info['return_status_text'] = 'ステータス: ' . htmlspecialchars($rental_info['return_status']);
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
                            <span class="detail-value"><?php echo htmlspecialchars($rental_info['start_date_formatted']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">返却予定日</span>
                            <span class="detail-value"><?php echo htmlspecialchars($rental_info['return_date_formatted']); ?></span>
                        </div>
                    </div>
                </section>

                <section class="detail-section">
                    <h2 class="section-title">お支払情報</h2>
                    <div class="detail-box">
                        <p class="payment-info">
                            <?php echo htmlspecialchars($rental_info['payment']); ?> | 
                            合計金額: ¥<?php echo number_format($rental_info['total_amount']); ?>
                        </p>
                    </div>
                </section>
                
                <section class="delivery-status">
                    <p><?php echo htmlspecialchars($rental_info['return_status_text']); ?></p>
                </section>

            <?php endif; ?>

        </main>

       <footer class="footer">
            <a href="#" id="open-cancel-modal" class="footer-link">レンタルキャンセルはコチラ</a>
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
                <a href="G-17_rental-cancel.php?id=<?php echo htmlspecialchars($rental_id); ?>" id="confirm-yes" class="btn btn-danger">はい</a>
                
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

        // 「レンタルキャンセルはコチラ」リンクがクリックされた時
        openBtn.addEventListener('click', function(e) {
            e.preventDefault(); // リンクのデフォルト動作（ページ遷移）を止める
            modal.style.display = 'flex'; // モーダルを表示する
        });

        // 「いいえ」ボタンがクリックされた時
        noBtn.addEventListener('click', function() {
            modal.style.display = 'none'; // モーダルを非表示にする
        });

        // 「×」ボタンがクリックされた時
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none'; // モーダルを非表示にする
        });

        // モーダルの背景（黒い部分）がクリックされた時
        modal.addEventListener('click', function(e) {
            if (e.target === modal) { // クリックされたのが背景自身か確認
                modal.style.display = 'none'; // モーダルを非表示にする
            }
        });

        // 「はい」ボタンは、通常のリンクとして動作し、
        // G-17_rental-cancel.php にページ遷移します。
    });
    </script>

</body>
</html>