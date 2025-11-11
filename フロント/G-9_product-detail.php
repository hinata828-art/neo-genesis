<?php
// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== 商品ID取得 =====
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ===== 商品詳細を取得 =====
try {
    // ★ product_description カラムも取得（G-12で使うため）
    // 7行目
$sql = "SELECT 
            product_name, 
            price, 
            product_image, 
            product_id, 
            category_id,  
            color,        
            product_description 
        FROM product 
        WHERE product_id = :id";
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

// ===== カテゴリごとのカラー設定 =====
$category_colors = [
    'C01' => ['オリジナル', 'イエロー', 'ホワイト'],
    'C02' => ['オリジナル', 'ブルー', 'グリーン'],
    'C03' => ['オリジナル', 'ブルー', 'レッド'],
    'C04' => ['オリジナル', 'ホワイト'],
    'C05' => ['オリジナル', 'ピンク'],
    'C06' => ['オリジナル', 'グレー'],
    'C07' => ['オリジナル', 'ゲーミング'],
    'C08' => ['オリジナル', 'ブルー'],
];

// 該当カテゴリのカラーを取得
$category_id = $product['category_id'] ?? 'C01';
$colors = $category_colors[$category_id] ?? ['オリジナル'];

// ===== 画像URLを整形 =====
// 現在のURLには「-色名」が末尾にある（例：https://aso2501223.chu.jp/AIimage/テレビ3-黄色）
// これを削除して“オリジナル”画像を生成
$image_url = $product['product_image'];
$original_image = preg_replace('/-[^-]+$/u', '', $image_url);

// 実際のDB上のオリジナルカラー（中身は例えば「ブラック」）
$original_color_value = $product['color'] ?? '不明';

// ===== 関連商品を3件取得 =====
try {
    $sql = "SELECT product_id, product_name, product_image 
            FROM product 
            WHERE product_id != :id 
            AND category_id = :cat 
            LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->bindValue(':cat', $category_id, PDO::PARAM_STR);
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

    <div class="product-main">
        <h2 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h2>

        <div class="product-image-area">
            <img id="mainImage"
                 src="<?php echo htmlspecialchars($original_image); ?>.jpg"
                 alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>

        <div class="product-info">
            <p class="price">¥<?php echo number_format($product['price']); ?> <span>（税込み）</span></p>

            <div class="color-select">
                <p class="color-label">カラーを選択：</p>
                <?php foreach ($colors as $i => $color): ?>
                    <label>
                        <input type="radio" 
                               name="color" 
                               value="<?php echo $color === 'オリジナル' ? htmlspecialchars($original_color_value) : htmlspecialchars($color); ?>"
                               data-color="<?php echo htmlspecialchars($color); ?>"
                               <?php if ($i === 0) echo 'checked'; ?>>
                        <?php echo htmlspecialchars($color); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="action-buttons">
                <form action="G-11_cart.php" method="POST" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                    <button type="submit" class="btn cart">カートに追加</button>
                </form>

                <button class="btn buy" onclick="goToOrder('G-12_order.php', <?php echo $product['product_id']; ?>)">購入</button>
                <button class="btn rental" onclick="goToOrder('G-14_rental.php', <?php echo $product['product_id']; ?>)">レンタル</button>
            </div>
        </div>
    </div>

    <footer class="related-footer">
         </footer>

</main>

<script>
function goToOrder(pageUrl, productId) {
    // 1. 選択されているカラーの input 要素を取得
    const selectedColorInput = document.querySelector('input[name="color"]:checked');
    
    // 2. その input の値（value）を取得
    let colorValue = 'normal'; // デフォルト値
    if (selectedColorInput) {
        colorValue = selectedColorInput.value;
    }
    
    // 3. IDとカラーをURLに付けてページ遷移
    location.href = `${pageUrl}?id=${productId}&color=${colorValue}`;
}
</script>

</body>
</html>