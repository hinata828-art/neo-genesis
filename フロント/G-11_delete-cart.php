<?php
session_start();

if (!isset($_POST['key'])) {
    header('Location: G-11_cart.php');
    exit;
}

$key = $_POST['key'];

unset($_SESSION['cart'][$key]);

header('Location: G-11_cart.php');
exit;
?>

