<?php
session_start();
require __DIR__ . '/../common/db_connect.php';

// カート取得
$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total = 0;

// カート内の商品を1件ずつ処理
foreach ($cart as $key => $qty) {
    // 商品IDとカラーを分離（例： "23_red" → [23, "red"]）
    $parts = explode('_', $key, 2); // 2つに分割（カラー名に_が含まれる可能性対策）
    
    if (count($parts) !== 2) {
        continue; // 不正なキーはスキップ
    }
    
    list($product_id, $color) = $parts;
    $product_id = (int)$product_id; // 数値に変換
    
    if ($product_id <= 0) {
        continue; // 不正なIDはスキップ
    }

    // 商品情報を取得
    $sql = "SELECT product_id, product_name, price, product_image, maker
            FROM product WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($p) {
        $p['qty'] = (int)$qty;  // 数量（数値に変換）
        $p['color'] = $color;   // カラー
        $cart_items[] = $p;
        $total += $p['price'] * $p['qty'];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>カート | ニシムラOnline</title>
<link rel="stylesheet" href="../css/header.css">
<link rel="stylesheet" href="../css/breadcrumb.css">
<link rel="stylesheet" href="../css/G-11_cart.css">
</head>
<body>
<?php require __DIR__ . '/../common/header.php'; ?>

<?php
$breadcrumbs = [
    ['name' => 'ホーム', 'url' => 'G-8_home.php'],
    ['name' => 'カート']
];
require __DIR__ . '/../common/breadcrumb.php';
?>

<div class="cart-page-wrapper">

<!-- カート合計とレジボタン -->
<div class="cart-summary">
    <p class="total">小計：¥<?= number_format($total) ?></p>
    <?php if (!empty($cart_items)): ?>
        <a href="G-12_order.php" class="buy-btn">レジへ進む</a>
    <?php endif; ?>
</div>

<div class="cart">
<?php if (empty($cart_items)): ?>
    <p class="empty-message">カートに商品がありません。</p>
    <p><a href="G-8_home.php">商品を見る</a></p>
<?php else: ?>

<?php foreach ($cart_items as $item): ?>
<div class="item">

    <!-- 左：商品画像 -->
    <div class="item-left">
        <img src="<?= htmlspecialchars($item['product_image'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?>"
             class="product-img">
    </div>

    <!-- 右：商品情報 -->
    <div class="item-right">

        <!-- 商品名 -->
        <p class="name"><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') ?></p>

        <!-- 価格 -->
        <p class="price">¥<?= number_format($item['price']) ?></p>

        <!-- 数量 -->
        <p class="quantity">数量：<?= $item['qty'] ?></p>

        <!-- カラー -->
        <p class="color">カラー：<?= htmlspecialchars($item['color'], ENT_QUOTES, 'UTF-8') ?></p>

        <!-- メーカー -->
        <p class="maker">メーカー：<?= htmlspecialchars($item['maker'] ?? '不明', ENT_QUOTES, 'UTF-8') ?></p>

        <!-- 小計 -->
        <p class="subtotal">小計：¥<?= number_format($item['price'] * $item['qty']) ?></p>

        <!-- 削除 / 購入 ボタン -->
        <div class="buttons">
            <form action="G-11_delete-cart.php" method="POST" class="inline-form">
                <input type="hidden" name="key"
                       value="<?= htmlspecialchars($item['product_id'] . '_' . $item['color'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="delete-btn">削除</button>
            </form>

            <a href="G-12_order.php?id=<?= urlencode($item['product_id']) ?>&color=<?= urlencode($item['color']) ?>"
               class="buy-btn">購入</a>
        </div>
    </div>

</div>
<?php endforeach; ?>
   
<?php endif; ?>
</div>

</div>
</body>
</html>