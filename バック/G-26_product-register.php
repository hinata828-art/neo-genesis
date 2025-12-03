<?php
session_start();
require '../common/db_connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 画像アップロード先
$targetDir = __DIR__ . '/../img/';   // front → img の相対パス

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$filename = null;

// 画像が送信されている場合
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {

    $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . time() . '.' . $ext;

    $targetPath = $targetDir . $filename;

    if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
        die("画像の保存に失敗しました");
    }
}

// DB保存
$sql = "INSERT INTO product (product_name, price, category_id, maker, color, jan_code, stock_quantity, product_detail, product_image)
        VALUES (:product_name, :price, :category_id, :maker, :color, :jan_code, :stock_quantity, :product_detail, :product_image)";
$stmt = $pdo->prepare($sql);

$stmt->bindValue(':product_name', $_POST['product_name']);
$stmt->bindValue(':price', $_POST['price']);
$stmt->bindValue(':category_id', $_POST['category_id']);
$stmt->bindValue(':maker', $_POST['maker']);
$stmt->bindValue(':color', $_POST['color']);
$stmt->bindValue(':jan_code', $_POST['jan_code']);
$stmt->bindValue(':stock_quantity', $_POST['stock_quantity']);
$stmt->bindValue(':product_detail', $_POST['product_detail']);
$stmt->bindValue(':product_image', $filename);

$stmt->execute();

// 完了後アラート + 画面遷移
echo "<script>
        alert('商品を登録しました');
        location.href='G-22_product.php';
      </script>";
exit;
