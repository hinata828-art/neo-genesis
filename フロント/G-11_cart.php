<?php
session_start();

  require __DIR__ . '/../common/db-connect.php'; 
 

if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    $cart_items = [];
} else {
    $ids = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $sql = "SELECT product_id, product_name, price, product_image FROM product WHERE product_id IN ($ids)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($_SESSION['cart']);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$total = array_sum(array_column($cart_items, 'price'));
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>カート | ニシムラOnline</title>
 <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
<link rel="stylesheet" href="G-11_cart.css">

</head>
<body>
<?php require __DIR__ . '/../common/header.php'?>

<div class="cart">
    <?php if (empty($cart_items)): ?>
        <p>カートに商品がありません。</p>
    <?php else: ?>
        <p class="total">小計 ￥<?= number_format($total) ?></p>
        <button class="checkout-btn" onclick="location.href='G-12注文情報入力画面>レジに進む（<?= count($cart_items) ?>個の商品）</button>

        <?php foreach ($cart_items as $item): ?>
            <div class="item">
                <img src="../img/<?= htmlspecialchars($item['product_image']) ?>" 
                     alt="<?= htmlspecialchars($item['product_name']) ?>" 
                     class="product-img">

                <p class="name"><?= htmlspecialchars($item['product_name']) ?><br>
                    <span class="price">¥<?= number_format($item['price']) ?></span>
                </p>

                <div class="buttons">
                    <form action="G-11_delete_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <button type="submit" class="delete-btn">削除</button>
                    </form>
                    <form action="G-11_add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                        <button type="submit" class="buy-btn">買う</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
