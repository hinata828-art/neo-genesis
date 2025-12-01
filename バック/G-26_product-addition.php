<?php
session_start();
require '../common/db_connect.php';

// カテゴリID → 名称
$categoryList = [
    "C01" => "テレビ",
    "C02" => "冷蔵庫",
    "C03" => "電子レンジ",
    "C04" => "カメラ",
    "C05" => "イヤホン",
    "C06" => "洗濯機",
    "C07" => "ノートPC",
    "C08" => "スマートフォン"
];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規商品追加</title>
    <link rel="stylesheet" href="../css/G-26_product-addition.css">
    <link rel="stylesheet" href="../css/staff_header.css">
</head>
<body>

<?php require_once __DIR__ . '/../common/staff_header.php'; ?>

<h2>新規商品追加</h2>

<div class="container">
<form method="POST" action="G-26_product-register.php" enctype="multipart/form-data">

    <div class="left-area">

        <label>商品名</label>
        <input type="text" name="product_name" required>

        <label>価格(税込)</label>
        <input type="number" name="price" min="0" required>

        <label>商品カテゴリー</label>
        <select name="category_id" required>
            <?php foreach ($categoryList as $key => $value): ?>
                <option value="<?= $key ?>"><?= $value ?></option>
            <?php endforeach; ?>
        </select>

        <label>メーカー</label>
        <input type="text" name="maker" required>

        <label>色</label>
        <input type="text" name="color" required>

        <label>JANコード（13桁）</label>
        <input type="text" name="jan_code" pattern="\d{13}" required>

        <label>在庫数</label>
        <input type="number" name="stock_quantity" min="0" required>

    </div>

    <div class="right-area">

        <label>商品画像（jpg/png）</label>
        <input type="file" name="product_image" accept="image/*" required>

        <label>商品説明</label>
        <textarea name="product_detail" required></textarea>

    </div>

    <div class="button-area">
        <button type="button" class="btn-cancel" onclick="location.href='G-22_product.php'">キャンセル</button>
        <button type="submit" class="btn">登録</button>
    </div>

</form>
</div>

</body>
</html>
