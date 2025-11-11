<?php
session_start();

if (isset($_POST['product_id']) && isset($_SESSION['cart'])) {
    $product_id = (int)$_POST['product_id'];

    // 指定IDをカートから削除
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($id) use ($product_id) {
        return $id !== $product_id;
    });

    // 配列のキーを詰め直す
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// カートページに戻る
header('Location: G-11_cart.php');
exit;
?>
