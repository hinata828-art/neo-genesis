<?php
session_start();
require __DIR__ . '/../common/db_connect.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    $cart_items = [];
} else {
    $ids = implode(',', array_fill(0, count($cart), '?'));
    $sql = "SELECT product_id, product_name, price, product_image FROM product WHERE product_id IN ($ids)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $cart[$item['product_id']];
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


<div class="item">
    <div class="item-left">
        <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="product-img">
    </div>
    <div class="item-right">
        <p class="name"><?= htmlspecialchars($item['product_name']) ?></p>
        <p class="price">¥<?= number_format($item['price']) ?></p>

        <div class="buttons">
            <form action="G-11_delete_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <button type="submit" class="delete-btn">削除</button>
            </form>

            <form action="G-12_order.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <button type="submit" class="buy-btn">購入</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
