<?php
// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== 商品ID取得 =====
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ===== 商品詳細を取得 =====
try {
    // 必要なカラムを全て取得
    $sql = "SELECT 
                product_name, 
                price, 
                product_image, 
                product_id, 
                category_id,
                color,
                product_detail
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

// 実際のDB上のオリジナルカラー（中身は例えば「ブラック」）
$original_color_value = $product['color'] ?? '不明';


// ▼▼▼ 修正点1：画像切り替えJS用の「ベースURL」と「拡張子」をPHPで生成 ▼▼▼
$base_image_url_from_db = $product['product_image'] ?? '';
$js_base_url = '';  // JSに渡す「.../カメラ1」のようなベースURL
$js_extension = ''; // JSに渡す「.jpg」のような拡張子

if (!empty($base_image_url_from_db)) {
    // 1. 拡張子を取得 (例: .jpg)
    $js_extension = substr($base_image_url_from_db, strrpos($base_image_url_from_db, '.')); 
    
    // 2. 拡張子を除いたURLを取得 (例: .../カメラ1 or .../カメラ1-白)
    $url_without_extension = substr($base_image_url_from_db, 0, strrpos($base_image_url_from_db, '.'));
    
    // 3. もし色名が付いていたら削除 (例: .../カメラ1-白 -> .../カメラ1)
    $js_base_url = preg_replace('/-[^-]+$/u', '', $url_without_extension);
}
// ▲▲▲ 修正点1 ここまで ▲▲▲


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
    $related_products = $fetchAll($stmt->(PDO::FETCH_ASSOC));
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
                 src="<?php echo htmlspecialchars($product['product_image'] ?? ''); ?>"
                 alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>

        <div class="product-info">
            <p class="price">¥<?php echo number_format($product['price']); ?> <span>（税込み）</span></p>

            <div class="product-description">
                <p><?php echo nl2br(htmlspecialchars($product['product_detail'] ?? '')); ?></p>
            </div>
            
            <form action="G-11_cart.php" method="POST" class="product-actions-form">

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
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                    
                    <button type="submit" class="btn cart">カートに追加</button>
                    
                    <button type="button" class="btn buy" onclick="goToOrder('G-12_order.php', <?php echo $product['product_id']; ?>)">購入</button>
                    <button type="button" class="btn rental" onclick="goToOrder('G-14_rental.php', <?php echo $product['product_id']; ?>)">レンタル</button>
                </div>

            </form>

        </div>
    </div>

    <footer class="related-footer">
        <h3>関連商品</h3>
        <div class="related-items">
            <?php if (empty($related_products)): ?>
                <p>関連商品はありません。</p>
            <?php else: ?>
                <?php foreach ($related_products as $related): ?>
                    <a href="G-9_product-detail.php?id=<?php echo $related['product_id']; ?>" class="related-item">
                        <img src="<?php echo htmlspecialchars($related['product_image'] ?? ''); ?>" 
                             alt="<?php echo htmlspecialchars($related['product_name']); ?>">
                        <p><?php echo htmlspecialchars($related['product_name']); ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </footer>

</main>

<script>
// G-12 / G-14 へのページ遷移（変更なし）
function goToOrder(pageUrl, productId) {
    const selectedColorInput = document.querySelector('.product-actions-form input[name="color"]:checked');
    let colorValue = 'normal'; 
    if (selectedColorInput) {
        colorValue = selectedColorInput.value;
    }
    location.href = `${pageUrl}?id=${productId}&color=${encodeURIComponent(colorValue)}`;
}


// ▼▼▼ 修正点2：G-9 画像切り替え用JavaScript (ここから追加) ▼▼▼
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. PHPから画像のベース情報を取得
    const trueBaseUrl = <?php echo json_encode($js_base_url); ?>;
    const extension = <?php echo json_encode($js_extension); ?>;
    const originalDbImage = <?php echo json_encode($product['product_image']); ?>;

    // 2. 関連するHTML要素を取得
    const mainImage = document.getElementById('mainImage');
    const colorRadios = document.querySelectorAll('input[name="color"]');

    // 3. 全てのラジオボタンに「変更」イベント監視を追加
    colorRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            
            // 4. 選択されたラジオの 'data-color' 属性 (例: 'イエロー' or 'オリジナル') を取得
            const selectedColorName = this.getAttribute('data-color');

            if (selectedColorName === 'オリジナル') {
                // 5a. 「オリジナル」が選ばれた場合
                // → DBに登録されている元の画像URL ( .../camera1.jpg など) に戻す
                mainImage.src = originalDbImage;
            } else {
                // 5b. 「オリジナル」以外 (例: 'イエロー') が選ばれた場合
                // → ベースURL + 色名 + 拡張子 で新しいURLを構築 (例: .../camera1-イエロー.jpg)
                mainImage.src = trueBaseUrl + '-' + selectedColorName + extension;
            }
        });
    });
});
// ▲▲▲ 修正点2 ここまで ▲▲▲
</script>

</body>
</html>