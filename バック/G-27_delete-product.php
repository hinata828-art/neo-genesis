<?php
require '../common/db_connect.php';

$id = $_GET['id'] ?? '';

if ($id !== '') {
    $stmt = $pdo->prepare("DELETE FROM product WHERE product_id = :id");
    $stmt->execute([':id' => $id]);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>商品削除完了</title>
  <style>
    body {
      margin: 0;
      font-family: 'Helvetica', sans-serif;
      background-color: #f9f9f9;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .center-box {
      text-align: center;
      background: #fff;
      padding: 2em;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .center-box img {
    
      height: 80px;
      margin-bottom: 1em;
    }
    .center-box p {
      font-size: 1.2em;
      margin-bottom: 1.5em;
      color: #333;
    }
    .back-btn {
      background-color: #30cfcf;
      color: #fff;
      border: none;
      padding: 0.6em 1.2em;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
    }
    .back-btn:hover {
      background-color: #26b5b5;
    }
  </style>
</head>
<body>
  <div class="center-box">
    <!-- 警告アイコン画像 -->
    <img src="img/alert.png" alt="削除完了アイコン">
    
    <p>商品の削除が完了しました。</p>
    <a href="G-22_product.php" class="back-btn">商品管理画面に戻る</a>
  </div>
</body>
</html>
