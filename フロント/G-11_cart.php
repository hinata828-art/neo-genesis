<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require __DIR__ . '/../common/db_connect.php';
?>
<?php
session_start();
require __DIR__ . '/../common/db_connect.php';

// カート取得
$cart = $_SESSION['cart'] ?? [];
$cart_items = [];

$total = 0;

// カート内の商品を1件ずつ処理
foreach ($cart as $key => $qty) {

    // ★ 商品IDとカラーを分離（例： "23_red" → 23, red）
    list($product_id, $color) = explode('_', $key);

    // ★ maker を追加して取得
    $sql = "SELECT product_id, product_name, price, product_image, maker
            FROM product WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($p) {
        $p['qty'] = $qty;      // 数量
        $p['color'] = $color;  // ★ カラー
        $cart_items[] = $p;

        $total += $p['price'] * $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
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

<!-- ▼ カート合計とレジボタン -->
<div class="cart-summary">
    <p class="total">小計：￥<?= number_format($total) ?></p>
    <a href="G-12_order.php" class="buy-btn">レジへ進む</a>
</div>

<div class="cart">
<?php if (empty($cart_items)): ?>
    <p>カートに商品がありません。</p>
<?php else: ?>

<?php foreach ($cart_items as $item): ?>
<div class="item">

    <!-- 左：商品画像 -->
    <div class="item-left">
        <img src="<?= htmlspecialchars($item['product_image']) ?>"
             alt="<?= htmlspecialchars($item['product_name']) ?>"
             class="product-img">
    </div>

    <!-- 右：商品情報 -->
    <div class="item-right">

        <!-- 商品名 -->
        <p class="name"><?= htmlspecialchars($item['product_name']) ?></p>

        <!-- 価格 -->
        <p class="price">¥<?= number_format($item['price']) ?></p>

        <!-- カラー -->
        <p class="color">カラー：<?= htmlspecialchars($item['color']) ?></p>

        <!-- メーカー -->
        <p class="maker">メーカー：<?= htmlspecialchars($item['maker'] ?? '不明') ?></p>

        <!-- 削除 / 購入 ボタン -->
        <div class="buttons">
            <form action="G-11_delete-cart.php" method="POST">
                <input type="hidden" name="key"
                       value="<?= $item['product_id'] . '_' . $item['color'] ?>">
                <button type="submit" class="delete-btn">削除</button>
            </form>

            <a href="G-12_order.php?id=<?= $item['product_id'] ?>&color=<?= $item['color'] ?>"
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

