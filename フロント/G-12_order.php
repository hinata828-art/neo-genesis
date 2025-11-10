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
    <div class="product-section">
        <img src="imge/camera.png" alt="商品画像" class="product-image">
        <div class="product-info">
            <label class="product-name">kamera</label>
            <div class="product-color-row">
                <label class="product-color-label">商品カラー：</label>
                <label class="product-color">kuro</label>
            </div>
        </div>
    </div>

    <div class="price-section">
            <p>商品の小計：<span class="price">￥12300</span></p>
            <p>ご請求額：<span class="price">￥12300</span></p>
    </div>

    <hr>

    <div class="delivery-section">
        <label>お届け先氏名：</label><br>
        <input type="text" name="name" class="input-text"><br>
        <label>お届け先住所：</label><br>
        <input type="text" name="address" class="input-text"><br>
    </div>

    <hr>

    <div class="payment-section">
        <p>お支払方法：</p><br>
        <div class="payment-box">
            <label><input type="radio" name="payment" value="conveni">コンビニ支払い</label><br>
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

    <div class="option-section">
        <p>追加オプション</p>
        <label><input type="checkbox" name="compensation" value="compensation">補償サービス（+500円/月で破損・水没も補償）</label><br>
        <label><input type="checkbox" name="delivery" value="delivery">配送・返却サービス（自宅集荷）</label><br>
    </div>
</div>

    <a href="G_13_order_complete.php" class="confirm-button">購入を確定する</a>
</body>
</html>