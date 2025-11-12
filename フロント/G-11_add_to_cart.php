<?php
session_start();

// 商品IDを受け取る
if (!isset($_POST['product_id'])) {
    header('Location: G-9_product-detail.php');
    exit;
}

$product_id = (int)$_POST['product_id'];

// カートがまだない場合は作成
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 商品ごとに数量を管理
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += 1; // 同じ商品なら数量を増やす
} else {
    $_SESSION['cart'][$product_id] = 1;  // 初めてなら数量1
}

// カートページへ移動
header('Location: G-11_cart.php');
exit;
?>
