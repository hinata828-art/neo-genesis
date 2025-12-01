<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../common/db_connect.php';
<?php
session_start();
require '../common/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 画像アップロード処理
    $imageName = "";
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {

        $uploadDir = __DIR__ . '/../img/'; // ← バックとimgは同じ階層

        // ディレクトリが存在しない場合（念のため）
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $imageName = 'img_' . uniqid() . '.' . $ext;

        $uploadFile = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadFile)) {
            die("画像保存失敗: 保存先 → " . $uploadFile);
        }
    } else {
        die("画像アップロードに失敗しました");
    }

    // 商品情報登録
    $sql = "INSERT INTO product (product_name, price, category_id, maker, color, jan_code, stock_quantity, product_image, product_detail)
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
        $imageName, // ← 保存したファイル名をDBへ
        $_POST['product_detail']
    ]);

    echo "<script>alert('商品登録が完了しました'); location.href='G-22_product.php';</script>";
    exit;
}
?>
