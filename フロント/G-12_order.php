<?php
// 1. セッションとDB接続
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php';

// 2. ログイン状態の確認（セッションから顧客情報を取得）
$customer_info = null;
if (isset($_SESSION['customer'])) {
    // ログイン済みの場合、セッションから顧客情報を取得
    $customer_info = $_SESSION['customer'];
} else {
    // (テスト用にダミー情報を設定)
    $customer_info = ['name' => '（ゲスト）', 'address' => '（住所未登録）'];
}


// 3. URLから商品IDとカラーを取得
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$color_value = isset($_GET['color']) ? htmlspecialchars($_GET['color']) : 'normal';

// 4. 商品IDを使ってDBから商品情報を取得
try {
    // ★ G-9 の時と同じように、product_image も取得
    $sql = "SELECT product_name, price, product_image FROM product WHERE product_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '商品データ取得エラー: ' . $e->getMessage();
    exit;
}

// 5. 商品が存在しない場合
if (!$product) {
    echo "<p>商品が見つかりません。</p>";
    exit;
}

// 6. カラー名を取得
$color_name = $color_value;

// 7. ご請求額を計算（小計）
$total_price = $product['price'];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="../css/G-12_order.css"> 
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <title>注文情報入力</title>
</head>
<body>
    <?php require __DIR__ . '/../common/header.php'; ?>
    
    <?php
    // パンくずリスト
    $breadcrumbs = [
        ['name' => 'ホーム', 'url' => 'G-8_home.php'],
        // ★ 商品名とG-9へのリンクを正しく表示 (G-12 PHPコードの40行目 $product['product_name'] が必要)
        ['name' => htmlspecialchars($product['product_name']), 'url' => 'G-9_product-detail.php?id=' . $product_id],
        ['name' => '注文情報入力']
    ];
    require __DIR__ . '/../common/breadcrumb.php';
    ?>
    
<div class="container">
    <p>注文内容</p>
    <hr>
    
    <div class="product-section">
        <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="商品画像" class="product-image">
        <div class="product-info">
            <label class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></label>
            <div class="product-color-row">
                <label class="product-color-label">商品カラー：</label>
                <label class="product-color"><?php echo htmlspecialchars($color_name); ?></label>
            </div>
        </div>
    </div>

    <div class="price-section">
            <p>商品の小計：<span class="price">￥<?php echo number_format($total_price); ?></span></p>
            <p>ご請求額：<span class="price" id="total_price_display">￥<?php echo number_format($total_price); ?></span></p>
    </div>

    <hr>
    
    <form action="G-13_order-complete.php" method="POST">

        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="color" value="<?php echo htmlspecialchars($color_value); ?>">
        
        <input type="hidden" name="total_amount" id="total_amount_hidden" value="<?php echo $total_price; ?>">

        <div class="delivery-section">
            <label>お届け先氏名：</label><br>
            <input type="text" name="name" class="input-text" value="<?php echo htmlspecialchars($customer_info['name'] ?? ''); ?>" required><br>
            <label>お届け先住所：</label><br>
            <input type="text" name="address" class="input-text" value="<?php echo htmlspecialchars($customer_info['address'] ?? ''); ?>" required><br>
        </div>

        <hr>

        <div class="payment-section">
            <p>お支払方法：</p><br>
            <div class="payment-box">
                <label><input type="radio" name="payment" value="conveni" checked>コンビニ支払い</label><br>
            </div>
            <div class="payment-box">
                <label><input type="radio" name="payment" value="credit">クレジットカード決済</label><br>
                
                <div class="credit-details">
                    <label>カード名義：</label><br>
                    <input type="text" name="cardname" placeholder="YAMADA TAROU" class="input-text"><br>
                    <label>カード番号：</label><br>
                    <input type="text" name="cardnumber" placeholder="0000-0000-0000" class="input-text"><br>
                    <label>有効期限：</label><br>
                    <div class="expiry-row">
                        <input type="text" name="monthnumber" placeholder="月" class="input-small">
                        <label>/</label>
                        <input type="text" name="yearnumber" placeholder="年" class="input-small"><br>
                    </div>
                    <label>セキュリティコード：</label><br>
                    <input type="text" name="code" class="input-text"><br>
                </div>
                </div>
            <div class="payment-box">
                <label><input type="radio" name="payment" value="bank">銀行振込</label><br>
            </div>
        </div>
        
        <div class="option-section">
            <p>追加オプション</p>
            <div class="option-box">
                <label>
                    <input type="checkbox" name="option_warranty" id="warranty_cb" value="500">
                    補償サービス（+500円/月で破損・水没も保証）
                </label>
            </div>
            <div class="option-box">
                <label>
                    <input type="checkbox" name="option_delivery" value="1">
                    配送・返却サービス（自宅集荷）
                </label>
            </div>
        </div>
        
        <button type="submit" class="confirm-button">購入を確定する</button>

    </form> 
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const warrantyCheckbox = document.getElementById('warranty_cb');
    const totalPriceDisplay = document.getElementById('total_price_display');
    const totalAmountHidden = document.getElementById('total_amount_hidden');
    const basePrice = parseInt(totalAmountHidden.value, 10);
    const warrantyPrice = parseInt(warrantyCheckbox.value, 10);

    warrantyCheckbox.addEventListener('change', function() {
        let newTotalPrice;
        if (this.checked) {
            newTotalPrice = basePrice + warrantyPrice;
        } else {
            newTotalPrice = basePrice;
        }
        totalPriceDisplay.innerText = '￥' + newTotalPrice.toLocaleString();
        totalAmountHidden.value = newTotalPrice;
    });
});
</script>

</body>
</html>