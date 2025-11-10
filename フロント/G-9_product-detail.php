<?php
// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== 商品ID取得 =====
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ===== 商品詳細を取得 =====
try {
    $sql = "SELECT product_name, price, product_image, product_id FROM product WHERE product_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '商品データ取得エラー: ' . $e->getMessage();
    exit;
}

// 商品が存在しない場合
if (!$product) {
    echo "<p>商品が見つかりません。</p>";
    exit;
}

// ===== 関連商品を3件取得 =====
try {
    $sql = "SELECT product_id, product_name, product_image FROM product WHERE product_id != :id LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $related_products = [];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> | 商品詳細</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <link rel="stylesheet" href="../css/G-9_product-detail.css">
</head>

<body>
    <?php require __DIR__ . '/../common/header.php'; ?>
    <?php
    $breadcrumbs = [
        ['name' => 'ホーム', 'url' => 'G-8_home.php'],
        ['name' => htmlspecialchars($product['product_name'])]
    ];
    require __DIR__ . '/../common/breadcrumb.php';
    ?>

<main class="product-detail">

    <!-- ===== 商品詳細 ===== -->
    <div class="product-main">
        <h2 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h2>

        <div class="product-image-area">
            <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>

        <div class="product-info">
            <p class="price">¥<?php echo number_format($product['price']); ?> <span>（税込み）</span></p>

            <!-- カラー選択 -->
            <div class="color-select">
                <label><input type="radio" name="color" value="red"> 赤</label>
                <label><input type="radio" name="color" value="blue"> 青</label>
                <label><input type="radio" name="color" value="normal" checked> ノーマル</label>
            </div>

            <!-- ボタン -->
            <div class="action-buttons">
                <form action="G-11_cart.php" method="POST" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                    <button type="submit" class="btn cart">カートに追加</button>
                </form>

                <button class="btn buy" onclick="location.href='G-12_order.php?id=<?php echo $product['product_id']; ?>'">購入</button>
                <button class="btn rental" onclick="location.href='G-14_rental.php?id=<?php echo $product['product_id']; ?>'">レンタル</button>
            </div>
        </div>
    </div>

    <!-- ===== 関連商品フッター ===== -->
    <footer class="related-footer">
        <h3>関連商品</h3>
        <div class="related-items">
            <?php foreach ($related_products as $r): ?>
                <a href="G-9_product-detail.php?id=<?php echo $r['product_id']; ?>" class="related-item">
                    <img src="<?php echo htmlspecialchars($r['product_image']); ?>" alt="<?php echo htmlspecialchars($r['product_name']); ?>">
                    <p><?php echo htmlspecialchars($r['product_name']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </footer>

</main>

</body>
</html>
