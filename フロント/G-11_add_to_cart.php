<?php
session_start();

// 商品IDを受け取る
if (!isset($_POST['product_id'])) {
    header('Location: G-8_home.php');
    exit;
}

$product_id = (int)$_POST['product_id'];

// カートがまだない場合は作成
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// すでにカートにある商品は重複させない（1つだけ）
if (!in_array($product_id, $_SESSION['cart'], true)) {
    $_SESSION['cart'][] = $product_id;
}

// カートページに移動
header('Location: G-11_cart.php');
exit;
?>