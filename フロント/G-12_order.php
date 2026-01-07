<?php
// 1. セッションとDB接続
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php';

// 2. ログイン状態の確認
$customer_info = null;
$customer_id = 0;
if (isset($_SESSION['customer'])) {
    $customer_info = $_SESSION['customer'];
    $customer_id = $_SESSION['customer']['id'];
} else {
    $customer_info = ['name' => '（ゲスト）', 'address' => '（住所未登録）'];
}

// 3. カラー名マップ
$color_display_map = [
    'original' => 'オリジナル', '白色' => 'ホワイト', '青' => 'ブルー',
    'ゲーミング' => 'ゲーミング', '黄色' => 'イエロー', '赤' => 'レッド',
    '緑' => 'グリーン', 'ブラック' => 'ブラック', 'ピンク' => 'ピンク',
    'グレー' => 'グレー'
];

// 4. 購入モード判定：単品購入 or カート購入
$is_cart_purchase = isset($_GET['cart_items']) && is_array($_GET['cart_items']);

$cart_items = [];
$total_price = 0;

if ($is_cart_purchase) {
    // === カート購入モード ===
    $cart = $_SESSION['cart'] ?? [];
    
    foreach ($_GET['cart_items'] as $key) {
        if (!isset($cart[$key])) continue;
        
        list($product_id, $color_value) = explode('_', $key);
        $qty = $cart[$key];
        
        // 商品情報を取得
        $sql = "SELECT product_id, product_name, price, product_image, color, category_id 
                FROM product WHERE product_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) continue;
        
        // 画像パス調整
        $base_image = $product['product_image'] ?? '';
        if (!empty($base_image)) {
            $true_base_url = preg_replace('/-[^-]+$/u', '', $base_image);
            if ($color_value === 'original') {
                $image_to_display = $true_base_url;
            } else {
                $image_to_display = $true_base_url . '-' . $color_value;
            }
        } else {
            $image_to_display = '../img/no_image.jpg';
        }
        
        $cart_items[] = [
            'product_id' => $product_id,
            'product_name' => $product['product_name'],
            'price' => $product['price'],
            'qty' => $qty,
            'color' => $color_value,
            'color_display' => $color_display_map[$color_value] ?? $color_value,
            'image' => $image_to_display,
            'category_id' => $product['category_id']
        ];
        
        $total_price += $product['price'] * $qty;
    }
    
    if (empty($cart_items)) {
        echo "<p>カートが空です。</p>";
        exit;
    }
    
} else {
    // === 単品購入モード ===
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $color_value = isset($_GET['color']) ? htmlspecialchars($_GET['color']) : 'original';
    
    try {
        $sql = "SELECT product_name, price, product_image, color, category_id 
                FROM product WHERE product_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo '商品データ取得エラー: ' . $e->getMessage();
        exit;
    }
    
    if (!$product) {
        echo "<p>商品が見つかりません。</p>";
        exit;
    }
    
    // 画像パス調整
    $base_image = $product['product_image'] ?? '';
    if (!empty($base_image)) {
        $true_base_url = preg_replace('/-[^-]+$/u', '', $base_image);
        if ($color_value === 'original') {
            $image_to_display = $true_base_url;
        } else {
            $image_to_display = $true_base_url . '-' . $color_value;
        }
    } else {
        $image_to_display = '../img/no_image.jpg';
    }
    
    $cart_items[] = [
        'product_id' => $product_id,
        'product_name' => $product['product_name'],
        'price' => $product['price'],
        'qty' => 1,
        'color' => $color_value,
        'color_display' => $color_display_map[$color_value] ?? $color_value,
        'image' => $image_to_display,
        'category_id' => $product['category_id']
    ];
    
    $total_price = $product['price'];
}

// ================================================================
// ★★★ 既存のクーポンロジックをそのまま使用（変更なし） ★★★
// ================================================================
$best_coupon = null;
$discount_amount = 0;
$customer_coupon_id_to_use = 0;

// 最初の商品のカテゴリーIDを使用（複数商品の場合も最初の1つだけ）
$product_category_id = $cart_items[0]['category_id'];

// ログインしている場合のみクーポンを検索
if ($customer_id > 0) {
    $sql_coupon = "SELECT 
                        cc.customer_coupon_id, 
                        c.discount_rate
                   FROM customer_coupon cc
                   JOIN coupon c ON cc.coupon_id = c.coupon_id
                   WHERE cc.customer_id = :cid 
                     AND cc.applicable_category_id = :catid
                     AND cc.used_at IS NULL
                     AND c.expiration_date >= CURDATE()
                   ORDER BY c.discount_rate DESC
                   LIMIT 1";
                   
    $stmt_coupon = $pdo->prepare($sql_coupon);
    $stmt_coupon->execute([
        ':cid' => $customer_id,
        ':catid' => $product_category_id
    ]);
    $best_coupon = $stmt_coupon->fetch(PDO::FETCH_ASSOC);
}

// 割引額と最終合計額を計算
if ($best_coupon) {
    $discount_rate = $best_coupon['discount_rate'];
    $discount_amount = floor(($total_price * $discount_rate) / 100);
    $customer_coupon_id_to_use = $best_coupon['customer_coupon_id'];
}

$final_total_price = $total_price - $discount_amount;


// ================================================================
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
    
<div class="container">
    <p>注文内容</p>
    <hr>
    
    <!-- 商品リスト表示 -->
       <!-- 商品リスト表示 -->
    <?php foreach ($cart_items as $item): ?>
    <div class="product-section">
        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="商品画像" class="product-image">
        
        <div class="product-info">
            <label class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></label>

            <div class="product-color-row">
                <label class="product-color-label">商品カラー：</label>
                <label class="product-color"><?php echo htmlspecialchars($item['color_display']); ?></label>
            </div>

            <?php if ($item['qty'] > 1): ?>
            <div class="product-qty-row">
                <label>数量：<?php echo $item['qty']; ?>個</label>
            </div>
            <?php endif; ?>

            <!-- ★ 追加：商品ごとの小計 ★ -->
            <div class="product-price-row">
                <label>小計：￥<?php echo number_format($item['price'] * $item['qty']); ?></label>
            </div>

        </div>
    </div>
    <?php endforeach; ?>

    
    <div class="price-section">
        <p>商品の小計：<span class="price">￥<?php echo number_format($total_price); ?></span></p>
        <p>割引額：<span class="price-discount">-￥<?php echo number_format($discount_amount); ?></span></p>
        <p>ご請求額：<span class="price" id="total_price_display">￥<?php echo number_format($final_total_price); ?></span></p>
    </div>

    <hr>
    
    <form action="G-13_order-complete.php" method="POST">
        <!-- 商品情報をJSON形式で送信 -->
        <input type="hidden" name="cart_items_json" value="<?php echo htmlspecialchars(json_encode($cart_items)); ?>">
        
        <!-- クーポンIDは単一のまま（既存形式） -->
        <input type="hidden" name="customer_coupon_id" value="<?php echo $customer_coupon_id_to_use; ?>">
        
        <input type="hidden" name="total_amount" id="total_amount_hidden" value="<?php echo $final_total_price; ?>">

        <div class="delivery-section">
            <label>お届け先氏名：</label><br>
            <input type="text" name="name" class="input-text" value="<?php echo htmlspecialchars($customer_info['customer_name'] ?? ''); ?>" required><br>
            <label>お届け先住所：</label><br>
            <input type="text" name="address" class="input-text" value="<?php echo htmlspecialchars($customer_info['full_address'] ?? ''); ?>" required><br>
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