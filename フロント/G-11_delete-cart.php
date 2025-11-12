<?php
session_start();

if (isset($_POST['product_id']) && isset($_SESSION['cart'])) {
    $product_id = (int)$_POST['product_id'];
    unset($_SESSION['cart'][$product_id]); // 商品を完全削除
}

header('Location: G-11_cart.php');
exit;
?>
