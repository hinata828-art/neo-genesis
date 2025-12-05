<?php
// 1. セッションとDB接続
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    // ゲスト購入を許可する場合
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
// G-11から渡される cart_items は配列であると想定
$is_cart_purchase = isset($_GET['cart_items']) && is_array($_GET['cart_items']);

$cart_items = [];
$total_price = 0;

if ($is_cart_purchase) {
    // === カート購入モード (G-11からの遷移) ===
    $cart = $_SESSION['cart'] ?? [];
    
    foreach ($_GET['cart_items'] as $key) {
        // カートセッションにその商品が含まれているか確認
        if (!isset($cart[$key])) continue; 

        $qty = $cart[$key];
        list($product_id, $color_value) = explode('_', $key);
        
        $sql = "SELECT product_id, product_name, price, product_image, color FROM product WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($p) {
            $item = $p;
            $item['quantity'] = $qty;
            $item['color'] = $color_value;
            
            // ★★★ 画像パス決定ロジックの統合 ★★★
            $productImagePath = $p['product_image'];

            // 1. カラーバリアントの適用
            if ($color_value !== 'original' && $color_value !== $p['color']) {
                $info = pathinfo($productImagePath);
                if (!empty($info['extension'])) {
                    // 拡張子がある場合: filename-color.ext
                    $productImagePath = $info['filename'] . '-' . $color_value . '.' . $info['extension'];
                } else {
                    // 拡張子がない場合: filename-color
                    $productImagePath .= '-' . $color_value;
                }
            }
            
            // 2. パス/URL形式の決定
            $imageUrl = '';
            if (strpos($productImagePath, 'http') === 0) {
                $imageUrl = $productImagePath; // 完全なURL
            } else if ($productImagePath) {
                // G-12からの相対パスは '../img/'
                $imageUrl = '../img/' . $productImagePath; // サーバーフォルダ内のファイル
            } else {
                $imageUrl = '../images/noimage.png'; // 画像なし
            }

            $item['product_image'] = $imageUrl; 
            // ★★★ ロジック統合ここまで ★★★

            $cart_items[$key] = $item;
            $total_price += $item['price'] * $item['quantity'];
        }
    }
} else if (isset($_GET['id']) && isset($_GET['color'])) {
    // === 単品購入モード (G-11_cart.phpから個別購入ボタン経由) ===
    $product_id = intval($_GET['id']);
    $color_value = $_GET['color'];
    $qty = 1; // 単品購入時は数量1

    $sql = "SELECT product_id, product_name, price, product_image, color FROM product WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($p) {
        $item = $p;
        $item['quantity'] = $qty;
        $item['color'] = $color_value;
        
        // ★★★ 画像パス決定ロジックの統合 ★★★
        $productImagePath = $p['product_image'];

        // 1. カラーバリアントの適用
        if ($color_value !== 'original' && $color_value !== $p['color']) {
            $info = pathinfo($productImagePath);
            if (!empty($info['extension'])) {
                $productImagePath = $info['filename'] . '-' . $color_value . '.' . $info['extension'];
            } else {
                $productImagePath .= '-' . $color_value;
            }
        }
        
        // 2. パス/URL形式の決定
        $imageUrl = '';
        if (strpos($productImagePath, 'http') === 0) {
            $imageUrl = $productImagePath; // 完全なURL
        } else if ($productImagePath) {
            $imageUrl = '../img/' . $productImagePath; // サーバーフォルダ内のファイル
        } else {
            $imageUrl = '../images/noimage.png'; // 画像なし
        }

        $item['product_image'] = $imageUrl; 
        // ★★★ ロジック統合ここまで ★★★

        $cart_items[] = $item; // 単品購入でも配列として扱う
        $total_price += $item['price'] * $item['quantity'];
    }
}


// 合計金額の計算
$base_total_price = $total_price;
$display_total_price = $base_total_price; 

if (empty($cart_items)) {
    echo "<p>購入対象の商品がありません。</p>";
    exit;
}

// 6. HTML開始
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>注文確認</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <link rel="stylesheet" href="../css/G-12_order.css">
</head>

<body>
    <?php require_once __DIR__ . '/../common/header.php'; ?>
    <?php
    $breadcrumbs = [
        ['name' => 'ホーム', 'url' => 'G-8_home.php'],
        ['name' => 'カート', 'url' => 'G-11_cart.php'],
        ['name' => '注文確認']
    ];
    //require __DIR__ . '/../common/breadcrumb.php';
    ?>

<div class="container"> <p>注文確認</p> <form method="POST" action="G-13_order-completion.php">
        
        <div class="product-list-section">
            <h3>注文商品</h3>
            <?php foreach ($cart_items as $key => $item): ?>
            <div class="order-item">
                <div class="item-img-box">
                    <img src="<?= htmlspecialchars($item['product_image']) ?>" 
                         alt="<?= htmlspecialchars($item['product_name']) ?>" 
                         class="product-img">
                </div>
                <div class="item-details">
                    <p class="product-name"><?= htmlspecialchars($item['product_name']) ?></p>
                    <p class="product-color">カラー: <?= htmlspecialchars($color_display_map[$item['color']] ?? $item['color']) ?></p>
                    <p class="product-qty">数量: <?= htmlspecialchars($item['quantity']) ?>点</p>
                    <p class="product-price">単価: ￥<?= number_format($item['price']) ?></p>
                </div>
                <div class="item-subtotal">
                    ￥<?= number_format($item['price'] * $item['quantity']) ?>
                </div>
                <input type="hidden" name="items[<?= $key ?>][id]" value="<?= $item['product_id'] ?>">
                <input type="hidden" name="items[<?= $key ?>][color]" value="<?= $item['color'] ?>">
                <input type="hidden" name="items[<?= $key ?>][qty]" value="<?= $item['quantity'] ?>">
            </div>
            <?php endforeach; ?>
        </div>
        <hr>

        <div class="info-section">
            <p>お届け先情報</p>
            <p>氏名: <?= htmlspecialchars($customer_info['name']) ?></p>
            <p>住所: <?= htmlspecialchars($customer_info['address']) ?></p>
            <?php if ($customer_id === 0): ?>
                <p class="guest-note">※ゲスト購入のため、次の画面で配送先を入力してください。</p>
            <?php endif; ?>
        </div>
        <hr>
        
        <div class="payment-section">
            <p>お支払い方法</p>
            <div class="payment-box">
                <label>
                    <input type="radio" name="payment_method" value="credit_card" checked>
                    クレジットカード
                </label>
            </div>
            <div class="payment-box">
                <label>
                    <input type="radio" name="payment_method" value="bank_transfer">
                    銀行振込
                </label>
            </div>
        </div>
        <hr>
        
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
                    <input type="checkbox" name="option_delivery" value="1000"> 配送・返却サービス（自宅集荷）
                </label>
            </div>
        </div>
        <hr>

        <div class="total-section">
            <p>小計 <span class="price">￥<?= number_format($base_total_price) ?></span></p>
            <p>送料 <span class="price">￥0</span></p>
            <p class="total-price">
                合計
                <span id="total_price_display">￥<?= number_format($display_total_price) ?></span>
            </p>
            <input type="hidden" name="base_total_price" id="base_total_price_hidden" value="<?= $base_total_price ?>">
            <input type="hidden" name="total_amount" id="total_amount_hidden" value="<?= $display_total_price ?>">
        </div>
        
        <button type="submit" class="confirm-button">購入を確定する</button>
    </form> 
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const warrantyCheckbox = document.getElementById('warranty_cb');
    const totalPriceDisplay = document.getElementById('total_price_display');
    const totalAmountHidden = document.getElementById('total_amount_hidden');
    
    // ベース価格（オプション除く）を取得
    const basePrice = parseInt(document.getElementById('base_total_price_hidden').value, 10);
    
    // オプションの価格を取得（補償サービスのみを例として使用）
    const warrantyPrice = parseInt(warrantyCheckbox.value, 10);
    // 配送オプションも考慮する場合、別途ロジックが必要だが、ここでは簡略化

    warrantyCheckbox.addEventListener('change', function() {
        let newTotalPrice = basePrice;
        
        if (this.checked) {
            newTotalPrice += warrantyPrice;
        } 
        
        // 表示更新
        totalPriceDisplay.innerText = '￥' + newTotalPrice.toLocaleString();
        
        // フォーム送信用の隠しフィールドを更新
        totalAmountHidden.value = newTotalPrice;
    });
});
</script>

</body>
</html>