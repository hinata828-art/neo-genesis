<?php
session_start();

// 商品IDを受け取る
if (!isset($_POST['product_id'],$_POST['color'])) {
    header('Location: G-9_product-detail.php');
    exit;
}

$product_id = (int)$_POST['product_id'];
$color = $_POST['color'];
// カートがまだない場合は作成
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_key = $product_id . '_' . $color;
// 商品ごとに数量を管理
if (isset($_SESSION['cart'][$cart_key])) {
    $_SESSION['cart'][$cart_key] += 1; // 同じ商品なら数量を増やす
} else {
    $_SESSION['cart'][$cart_key] = 1;  // 初めてなら数量1
}

// カートページへ移動
header('Location: G-11_cart.php');
exit;
?>
