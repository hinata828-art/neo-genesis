<?php
session_start();
require '../common/db_connect.php';

// ----------------------------------------------------
// 入力データ取得
// ----------------------------------------------------
$product_name   = $_POST['product_name'];
$price          = $_POST['price'];
$category_id    = $_POST['category_id'];
$maker          = $_POST['maker'];
$color          = $_POST['color'];
$product_detail = $_POST['product_detail'];

// ----------------------------------------------------
// JANコード生成（addition と同じ仕様）
// ----------------------------------------------------
$catSuffix = substr($category_id, -2);

$makerCode1 = '000';
$makerCode2 = '0000';

switch ($maker) {
    case '外山ファクトリー':
        $makerCode1 = '827'; $makerCode2 = '0827'; break;
    case '七味産業':
        $makerCode1 = '823'; $makerCode2 = '0823'; break;
    case 'ツルヒドラッグ':
        $makerCode1 = '121'; $makerCode2 = '0121'; break;
    case '晃輝工業':
        $makerCode1 = '090'; $makerCode2 = '0222'; break;
    case 'ニシムラエレクトロニクス':
        $makerCode1 = '128'; $makerCode2 = '0828'; break;
}

$jan = $catSuffix . $makerCode1 . $makerCode2 . sprintf("%04d", rand(0, 9999));

// ----------------------------------------------------
// 画像アップロード処理
// ----------------------------------------------------

$uploadDir = '../product_img/';  // ← フォルダ構成に合わせて修正済み

// ファイル名は JANコード + 拡張子 にする
$ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
$filename = $jan . "." . $ext;
$uploadPath = $uploadDir . $filename;

// 画像保存
if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
    echo "画像のアップロードに失敗しました。";
    exit;
}

// ----------------------------------------------------
// DB登録（ファイル名のみ保存）
// ----------------------------------------------------
$sql = "INSERT INTO product (
            product_name, price, category_id, maker, color,
            jan_code, product_detail, product_img
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $product_name,
    $price,
    $category_id,
    $maker,
    $color,
    $jan,
    $product_detail,
    $filename    // ← フルパスではなくファイル名のみ
]);

// ----------------------------------------------------
// 完了後、一覧へ
// ----------------------------------------------------
header("Location: G-22_product.php?msg=added");
exit;
?>
