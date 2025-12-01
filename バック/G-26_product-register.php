<?php
session_start();
require '../common/db_connect.php';

// --- 画像アップロード処理 ---
if (!empty($_FILES['product_image']['name'])) {

    $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $newFilename = 'img_' . uniqid() . '.' . $ext;

    // imgフォルダ（バックと同階層）
    $uploadPath = __DIR__ . '/../img/' . $newFilename;

    if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
        exit("画像の保存に失敗しました。");
    }

} else {
    exit("画像が選択されていません。");
}

// --- 商品情報をDBに登録 ---
$sql = "INSERT INTO product 
(product_name, price, category_id, maker, color, jan_code, stock_quantity, product_detail, product_image)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $_POST['product_name'],
    $_POST['price'],
    $_POST['category_id'],
    $_POST['maker'],
    $_POST['color'],
    $_POST['jan_code'],
    $_POST['stock_quantity'],
    $_POST['product_detail'],
    $newFilename
]);

echo "<script>
alert('商品を登録しました');
location.href='G-22_product.php';
</script>";
