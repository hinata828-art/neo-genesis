<?php
session_start();

  require __DIR__ . '/../common/header.php'; 
  require __DIR__ . '/../common/db-connect.php'; 
 

// ログインチェック
if (!isset($_SESSION['customer_id'])) {
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];

// データベース接続（サンプル）
// $conn = new mysqli($servername, $username, $password, $dbname);

// 削除メッセージの表示
$message = '';
if (isset($_GET['deleted']) && $_GET['deleted'] == 'success') {
    $message = 'カートから商品を削除しました。';
}

//カート内の商品データを取得（customer_idに紐づく商品）
// 実際のデータベースクエリ:
$sql = "SELECT p.product_id, p.product_name, p.price, p.product_image 
       FROM product p 
       INNER JOIN cart c ON p.product_id = c.product_id 
       WHERE c.customer_id = ?";
 $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $customer_id);
 $stmt->execute();
 $result = $stmt->get_result();
 $cart_items = $result->fetch_all(MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ショッピングカート</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>



    <?php
    if ($message) {
    ?>
        <div class="message success-message">
            <?php
            echo htmlspecialchars($message);
            ?>
        </div>
    <?php
    }
    ?>

    <?php
    if (empty($cart_items)) {
    ?>
        <div class="empty-cart">
            <p>カートに商品がありません</p>
        </div>
    <?php
    } else {
        foreach ($cart_items as $item) {
    ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                     class="product-image">
                
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                    <p class="product-price">¥<?php echo number_format($item['price']); ?></p>
                    
                    <div class="button-group">
                        <form method="POST" action="delete_from_cart.php" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <button type="submit" class="btn btn-delete">削除</button>
                        </form>
                        
                        <form method="POST" action="purchase.php" style="display: inline;">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <button type="submit" class="btn btn-buy">買う</button>
                        </form>
                    </div>
                </div>
            </div>
    <?php
        }
    }
    ?>
</div>

</body>
</html>