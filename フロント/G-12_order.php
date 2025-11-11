<?php
session_start();
ini_set('display_errors', 0); // 本番ではエラー非表示
error_reporting(0);

// ===== データベース接続 =====
require '../common/db_connect.php';

// ログインチェック（本番では必須）
if (!isset($_SESSION['customer_id'])) {
    header('Location: /G-1_login-process.php');
    exit;
}
$customer_id = $_SESSION['customer_id'];

// 商品ID取得
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    header('Location: /error/invalid_product.php');
    exit;
}

// 商品情報取得
$stmt = $pdo->prepare("SELECT product_name, price, color FROM product WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) {
    header('Location: /error/product_not_found.php');
    exit;
}

// 顧客情報取得
$stmt = $pdo->prepare("SELECT customer_name FROM customer WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

// 住所情報取得
$stmt = $pdo->prepare("SELECT postal_code, prefecture, city, address_line FROM address WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$address = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <!-- スマホ拡大防止 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- 固有CSS -->
    <link rel="stylesheet" href="../css/G-12_order.css">

    <!-- 共通ヘッダーCSS -->
    <link rel="stylesheet" href="../css/header.css">
    <!-- パンくずCSS -->
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <title>注文情報入力</title>
</head>
<body>
    <!-- ▼ 共通ヘッダー -->
    <?php require __DIR__ . '/../common/header.php'; ?>
    <!-- ▲ 共通ヘッダー -->

    <!-- ▼ パンくずリスト -->
    <?php
    $breadcrumbs = [
        ['name' => '現在のページ']
    ];
    require __DIR__ . '/../common/breadcrumb.php';
    ?>
    <!-- ▲ パンくずリスト -->
     
<div class="container">
        <p>注文内容</p>
        <hr>
        <form method="post" action="G_13_order_complete.php">
            <div class="product-section">
                <img src="<?= htmlspecialchars($product['product_image']) ?>" alt="商品画像" class="product-image">

                <div class="product-info">
                    <label class="product-name"><?= htmlspecialchars($product['product_name']) ?></label>
                    <div class="product-color-row">
                        <label class="product-color-label">商品カラー：</label>
                        <label class="product-color"><?= htmlspecialchars($product['color']) ?></label>
                    </div>
                </div>
            </div>

            <div class="price-section">
                <p>商品の小計：<span class="price">￥<?= number_format($product['price']) ?></span></p>
                <p>ご請求額：<span class="price">￥<?= number_format($product['price']) ?></span></p>
            </div>

            <hr>

            <div class="delivery-section">
                <label>お届け先氏名：</label><br>
                <input type="text" name="name" class="input-text" value="<?= htmlspecialchars($customer['customer_name']) ?>" readonly><br>
                <label>お届け先住所：</label><br>
                <input type="text" name="address" class="input-text"
                    value="<?= htmlspecialchars($address['postal_code'] . ' ' . $address['prefecture'] . ' ' . $address['city'] . ' ' . $address['address_line']) ?>" readonly><br>
            </div>

            <hr>

            <div class="payment-section">
                <p>お支払方法：</p><br>
                <div class="payment-box">
                    <label><input type="radio" name="payment" value="conveni" required>コンビニ支払い</label><br>
                </div>
                <div class="payment-box">
                    <label><input type="radio" name="payment" value="credit">クレジットカード決済</label><br>
                    <div class="credit-details">
                        <label>カード名義：</label><br>
                        <input type="text" name="cardname" placeholder="YAMADA TAROU" class="input-text"><br>
                        <label>カード番号：</label><br>
                        <input type="text" name="cardnumber" placeholder="0000-0000-0000" class="input-text"><br>
                        <label>有効期限：</label><br>
                        <div class="expiry-row">
                            <input type="text" name="monthnumber" placeholder="月" class="input-small">
                            <label>/</label>
                            <input type="text" name="yearnumber" placeholder="年" class="input-small"><br>
                        </div>
                        <label>セキュリティコード：</label><br>
                        <input type="text" name="code" class="input-text"><br>
                    </div>
                </div>
                <div class="payment-box">
                    <label><input type="radio" name="payment" value="bank">銀行振込</label><br>
                </div>
            </div>

            <div class="option-section">
                <p>追加オプション</p>
                <label><input type="checkbox" name="compensation" value="compensation">補償サービス（+500円/月）</label><br>
                <label><input type="checkbox" name="delivery" value="delivery">配送・返却サービス（自宅集荷）</label><br>
            </div>

            <input type="hidden" name="product_id" value="<?= $product_id ?>">
            <button type="submit" class="confirm-button">購入を確定する</button>
        </form>
    </div>
</body>
</html>