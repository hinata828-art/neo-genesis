<?php
session_start();
require '../common/db_connect.php';

// 必須チェック
if (
    !isset($_POST['product_name'], $_POST['price'], $_POST['category_id'],
            $_POST['maker'], $_POST['color'], $_POST['stock_quantity'],
            $_POST['product_detail'], $_POST['jan_code'])
) {
    echo "入力エラーが発生しました。";
    exit;
}

$product_name = $_POST['product_name'];
$price = intval($_POST['price']);
$category_id = $_POST['category_id'];
$maker = $_POST['maker'];
$color = $_POST['color'];
$stock_quantity = intval($_POST['stock_quantity']);
$product_detail = $_POST['product_detail'];
$jan_code = $_POST['jan_code'];

// ---------- 画像アップロード処理 ----------
if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
    echo "画像アップロードに失敗しました。";
    exit;
}

// 保存先フォルダ（G-26 と同階層の img）
$uploadDir = '../img/';

// ファイル名（重複対策）
$ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
$filename = 'product_' . time() . '.' . $ext;
$savePath = $uploadDir . $filename;

// DB にはブラウザ参照用パスを保存
$dbImagePath = 'img/' . $filename;

// 実際に保存
if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $savePath)) {
    echo "画像保存に失敗しました。";
    exit;
}

// ---------- INSERT 実行 ----------
$sql = "
    INSERT INTO product
    (product_name, price, category_id, color, maker, product_detail,
     stock_quantity, jan_code, product_image)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $product_name,
    $price,
    $category_id,
    $color,
    $maker,
    $product_detail,
    $stock_quantity,
    $jan_code,
    $dbImagePath
]);

echo "<script>
        alert('新規商品を登録しました。');
        window.location.href='G-22_product.php';
      </script>";
exit;
?>
