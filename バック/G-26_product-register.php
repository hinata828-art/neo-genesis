<?php
session_start();
require '../common/db_connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 画像アップロード先（バック → img）
$targetDir = __DIR__ . '/../img/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$filename = null;

// ------------------------------
// 画像アップロード処理
// ------------------------------
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {

    $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . time() . '.' . $ext;
    $targetPath = $targetDir . $filename;

    if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
        die("画像の保存に失敗しました");
    }

} else {
    die("画像が送信されていません。");
}

// DB に保存する画像パス（ブラウザ表示用：バック → img）
$dbPath = '../img/' . $filename;


// ------------------------------
// 商品仮登録（JANコードは後で付与）
// ------------------------------
$sql = "INSERT INTO product 
        (product_name, price, category_id, maker, color, product_detail, product_image, stock_quantity)
        VALUES 
        (:product_name, :price, :category_id, :maker, :color, :product_detail, :product_image, :stock_quantity)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':product_name'   => $_POST['product_name'],
    ':price'          => $_POST['price'],
    ':category_id'    => $_POST['category_id'],
    ':maker'          => $_POST['maker'],
    ':color'          => $_POST['color'],
    ':product_detail' => $_POST['product_detail'],
    ':product_image'  => $dbPath,
    ':stock_quantity' => $_POST['stock_quantity']
]);

// 登録した商品ID
$product_id = $pdo->lastInsertId();


// ------------------------------
// JANコード生成
// ------------------------------
$categorySuffix = substr($_POST['category_id'], -2); 

$maker = $_POST['maker'];
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
    default:
        $makerCode1 = '000'; $makerCode2 = '0000';
}

$productIdPadded = str_pad($product_id, 4, '0', STR_PAD_LEFT);
$jan_code = $categorySuffix . $makerCode1 . $makerCode2 . $productIdPadded;


// JANコード更新
$updateSql = "UPDATE product SET jan_code = :jan_code WHERE product_id = :id";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->execute([
    ':jan_code' => $jan_code,
    ':id'       => $product_id
]);


// 完了後
echo "<script>
        alert('商品を登録しました。JANコード: {$jan_code}');
        location.href='G-22_product.php';
      </script>";
exit;

