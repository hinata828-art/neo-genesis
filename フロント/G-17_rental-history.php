<?php
// 1. セッションを開始
session_start();

// 2. エラー表示設定
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. 共通DB接続
require '../common/db_connect.php';

// 4. データ初期化
$products = [];
$order_info = null;
$error_message = '';
$is_cancellable = false; 
$transaction_id = 0; 
$show_roulette_button = false;

try {
    // 5. ID取得
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];
    
    // 6. データ取得
    $sql = "SELECT 
                t.transaction_date, 
                t.payment,
                t.delivery_status,
                t.total_amount,
                p.product_id,
                p.product_name, 
                p.product_image,
                p.price,
                r.rental_start,
                r.rental_end,
                r.coupon_claimed 
            FROM transaction_table AS t
            JOIN rental AS r ON t.transaction_id = r.transaction_id
            JOIN product AS p ON r.product_id = p.product_id
            WHERE t.transaction_id = :id"; 

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        throw new Exception('該当するレンタル履歴が見つかりません。');
    }
    
    $order_info = $products[0];
    
    $order_info['start_date_formatted'] = date('Y/m/d H:i', strtotime($order_info['rental_start']));
    $order_info['return_date_formatted'] = date('Y/m/d', strtotime($order_info['rental_end']));
    
    switch ($order_info['delivery_status']) { 
        case '注文受付':
            $order_info['return_status_text'] = '発送準備中です';
            $is_cancellable = true;
            break;
        case 'レンタル中':
            $order_info['return_status_text'] = '返却予定日: ' . $order_info['return_date_formatted'];
            break;
        case '返却済み':
            $order_info['return_status_text'] = '返却完了済み';
            if ($order_info['coupon_claimed'] == 0) {
                $show_roulette_button = true;
            }
            break;
        case 'キャンセル済み':
            $order_info['return_status_text'] = 'この取引はキャンセルされました';
            break;
        default:
            $order_info['return_status_text'] = 'ステータス: ' . htmlspecialchars($order_info['delivery_status']);
            break;
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
}

function getStatusClass($status) {
    if ($status == 'キャンセル済み') return 'status-cancelled';
    if ($status == '配達完了' || $status == '返却済み') return 'status-delivered';
    return 'status-processing'; 
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レンタル履歴詳細</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-17_rental-history.css"> 
</head>
<body>
    <?php require '../common/header.php'; ?>
    
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

                            <!-- 延長ボタン -->
                            <a href="G-17_rental_sub.php?id=<?php echo $transaction_id; ?>" class="btn btn-purchase">
                                レンタル期間を延長する
                            </a>

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
                    <p class="<?php echo htmlspecialchars(getStatusClass($order_info['delivery_status'])); ?>">
                        <?php echo htmlspecialchars($order_info['return_status_text']); ?>
                    </p>
                </section>

            <?php endif; ?>

        </main>

    </div> 

</body>
</html>
