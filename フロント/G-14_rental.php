<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <!-- スマホ拡大防止 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- 固有CSS -->
    <link rel="stylesheet" href="G-14_rental.css">

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
        <p>レンタル申し込み</p>
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

        <div class="rental-section">
            <label>レンタル期間</label>
            <select>
                <option>1週間</option>
                <option>2週間</option>
                <option>1ヶ月</option>
                <option>3ヶ月</option>
                <option>6ヶ月</option>
                <option>1年</option>
            </select>
        </div>

        <div class="price-section">
                <label>レンタル料金</label><br>
                    <label>小計：<span class="price">￥12300</span></label>
                    <label>オプション代：<span class="price">￥500</span></label>
                    <label>ご請求額：<span class="price">￥12800</span></label>
        </div>

        <div class="option-section">
            <p>追加オプション</p>
            <label><input type="checkbox" name="delivery" value="delivery">配送・返却サービス（自宅集荷）</label>
            <label><input type="checkbox" name="buyoption" value="buyoption">購入オプション（レンタル料金を購入代金に充当）</label>
            <label><input type="checkbox" name="compensation" value="compensation" disabled="disabled">補償サービス（+500円/月で破損・水没も補償）</label>
                <span>補償サービスは必須です</span>

        </div>
        
        <a href="G_15_order_complete.php" class="confirm-button">レンタルを確定する</a>

    </div>
</body>
</html>