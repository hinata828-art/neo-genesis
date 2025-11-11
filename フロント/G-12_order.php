<?php
// 1. セッションとDB接続
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php';

// 2. ログイン状態の確認（セッションから顧客情報を取得）
$customer_info = null;
if (isset($_SESSION['customer'])) {
    // ログイン済みの場合、セッションから顧客情報を取得
    $customer_info = $_SESSION['customer'];
    
    // (もし住所情報が別テーブルなら、ここでDBから追加取得しても良い)
    // 例: $customer_info['name'] = $_SESSION['customer']['customer_name'];
    // 例: $customer_info['address'] = $_SESSION['customer']['address'];
} else {
    // ログインしていない場合（テスト用、実際はログインページにリダイレクト）
    // echo "ログインしていません。G-1_login.php にリダイレクトします。";
    // header('Location: G-1_login.php');
    // exit;
    
    // (テスト用にダミー情報を設定)
    $customer_info = ['customer_name' => '（ゲスト）', 'address' => '（住所未登録）'];
}


// 3. URLから商品IDとカラーを取得
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$color_value = isset($_GET['color']) ? htmlspecialchars($_GET['color']) : 'normal';

// 4. 商品IDを使ってDBから商品情報を取得
try {
    $sql = "SELECT product_name, price, product_image FROM product WHERE product_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '商品データ取得エラー: ' . $e->getMessage();
    exit;
}

// 5. 商品が存在しない場合
if (!$product) {
    echo "<p>商品が見つかりません。</p>";
    exit;
}

// 6. カラーの値を日本語に変換（任意）
$color_name = '';
switch ($color_value) {
    case 'red':
        $color_name = '赤';
        break;
    case 'blue':
        $color_name = '青';
        break;
    default:
        $color_name = 'ノーマル';
        break;
}

// 7. ご請求額を計算（小計と同じと仮定）
$total_price = $product['price'];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="../css/G-12_order.css"> 
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <title>注文情報入力</title>
</head>
<body>
    <?php require __DIR__ . '/../common/header.php'; ?>
    
    <?php
    // ★ パンくずリストを動的に修正
    $breadcrumbs = [
        ['name' => 'ホーム', 'url' => 'G-8_home.php'],
        ['name' => htmlspecialchars($product['product_name']), 'url' => 'G-9_product-detail.php?id=' . $product_id],
        ['name' => '注文情報入力']
    ];
    require __DIR__ . '/../common/breadcrumb.php';
    ?>
     
<div class="container">
    <p>注文内容</p>
    <hr>
    
    <div class="product-section">
        <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="商品画像" class="product-image">
        <div class="product-info">
            <label class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></label>
            <div class="product-color-row">
                <label class="product-color-label">商品カラー：</label>
                <label class="product-color"><?php echo $color_name; ?></label>
            </div>
        </div>
    </div>

    <div class="price-section">
            <p>商品の小計：<span class="price">￥<?php echo number_format($total_price); ?></span></p>
            <p>ご請求額：<span class="price">￥<?php echo number_format($total_price); ?></span></p>
    </div>

    <hr>
    
    <form action="G-13_order_complete.php" method="POST">

        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="color" value="<?php echo $color_value; ?>">
        <input type="hidden" name="total_amount" value="<?php echo $total_price; ?>">


        <div class="delivery-section">
            <label>お届け先氏名：</label><br>
            <input type="text" name="name" class="input-text" value="<?php echo htmlspecialchars($customer_info['customer_name'] ?? ''); ?>" required><br>
            <label>お届け先住所：</label><br>
            <input type="text" name="address" class="input-text" value="<?php echo htmlspecialchars($customer_info['address'] ?? ''); ?>" required><br>
        </div>

        <hr>

        <div class="payment-section">
            <p>お支払方法：</p><br>
            <div class="payment-box">
                <label><input type="radio" name="payment" value="conveni" checked>コンビニ支払い</label><br>
            </div>
            <div class="payment-box">
                <label><input type="radio" name="payment" value="credit">クレジットカード決済</label><br>
                <div class="credit-details">
                    </div>
            </div>
            <div class="payment-box">
                <label><input type="radio" name="payment" value="bank">銀行振込</label><br>
            </div>
        </div>
        
        <div class="option-section">
             </div>
        
        <button type="submit" class="confirm-button">購入を確定する</button>

    </form> </div>

</body>
</html>