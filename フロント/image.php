<?php session_start(); ?>
<?php require ' common/db-connect.php'; ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>画像練習</title>
</head>
<body>
    <?php
    $connect = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
    $product_id = 2; // ★表示したい商品のIDを設定★

    $product_name = '商品名未取得';
    $product_image_url = 'placeholder.png'; // 画像が見つからない場合の代替画像

    try {
        $pdo = new PDO($connect, USER, PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // product_idが2の商品の名前と画像URLを取得
        $sql = "SELECT product_name, product_image FROM product WHERE product_id = :product_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $product_name = $result['product_name'];
            $product_image_url = $result['product_image'];
        }

    } catch (PDOException $e) {
        echo "DB接続またはクエリ実行エラー: " . $e->getMessage();
    }
?>
</body>
</html>
