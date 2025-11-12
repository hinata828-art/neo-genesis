<?php
session_start();

// 商品IDを受け取る
if (!isset($_POST['product_id'])) {
    header('Location: G-9_product-detail.php');
    exit;
}

$product_id = (int)$_POST['product_id'];

// カートがまだない場合は作成
if (!isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] = 1;
} else {
    $_SESSION['cart'][$product_id] += 1;
}


// すでにカートにある商品は重複させない（1つだけ）
if (!in_array($product_id, $_SESSION['cart'], true)) {
    $_SESSION['cart'][] = $product_id;
}

// カートページに移動
header('Location: G-11_cart.php');
exit;
?>