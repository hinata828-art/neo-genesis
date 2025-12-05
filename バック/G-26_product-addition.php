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

        <label>JANコード（自動生成）</label>
        <p id="jan_preview">カテゴリーとメーカー選択後に自動生成されます</p>

        <script>
        document.querySelector('select[name="category_id"]').addEventListener('change', updateJanPreview);
        document.querySelector('input[name="maker"]').addEventListener('input', updateJanPreview);

        function updateJanPreview() {
            const cat = document.querySelector('select[name="category_id"]').value;
            const maker = document.querySelector('input[name="maker"]').value;

            let catSuffix = cat.slice(-2);
            let makerCode1 = '000', makerCode2 = '0000';

            switch (maker) {
                case '外山ファクトリー':
                    makerCode1 = '827'; makerCode2 = '0827'; break;
                case '七味産業':
                    makerCode1 = '823'; makerCode2 = '0823'; break;
                case 'ツルヒドラッグ':
                    makerCode1 = '121'; makerCode2 = '0121'; break;
                case '晃輝工業':
                    makerCode1 = '090'; makerCode2 = '0222'; break;
                case 'ニシムラエレクトロニクス':
                    makerCode1 = '128'; makerCode2 = '0828'; break;
            }

            // 商品IDは登録後に決まるのでここでは「XXXX」で仮表示
            document.getElementById('jan_preview').textContent =
                catSuffix + makerCode1 + makerCode2 + 'XXXX';
        }
        </script>

    </div>

    <div class="right-area">

        <label>商品画像（jpg/png）</label>
        <input type="file" name="product_image" accept="image/*" required>

        <label>商品説明</label>
        <textarea name="product_detail" required></textarea>

    </div>

    <div class="button-area">
        <button type="button" class="btn-cancel" onclick="location.href='G-22_product.php'">キャンセル</button>
        <button type="submit" class="btn-submit">登録</button>
    </div>

</form>
</div>

</body>
</html>
