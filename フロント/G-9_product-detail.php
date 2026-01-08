<?php
// ★★★ セッション開始処理 ★★★
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ログイン状態を取得
$is_logged_in = isset($_SESSION['customer']['id']);
$customer_id = $is_logged_in ? $_SESSION['customer']['id'] : null;

// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== 商品ID取得 =====
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ===== 商品詳細とお気に入り状態を取得 =====
try {
    // 商品詳細取得
    $sql = "SELECT product_name, price, product_image, product_id, category_id, color, product_detail
            FROM product WHERE product_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // ★追加: お気に入り登録済みかどうかの判定
    $is_favorite = false;
    if ($is_logged_in && $product) {
        // テーブル名 `like` (バッククォート必須), カラム名 user_id
        $fav_sql = "SELECT 1 FROM `like` WHERE user_id = :u_id AND product_id = :p_id";
        $fav_stmt = $pdo->prepare($fav_sql);
        $fav_stmt->execute(['u_id' => $customer_id, 'p_id' => $product_id]);
        $is_favorite = (bool)$fav_stmt->fetch();
    }
} catch (PDOException $e) {
    echo 'データ取得エラー: ' . $e->getMessage();
    exit;
}

// 商品が存在しない場合
if (!$product) {
    echo "<p>商品が見つかりません。</p>";
    exit;
}

// 画像パス決定ロジック
$productImagePath = $product['product_image'] ?? '';
$imageUrl = '';
if (strpos($productImagePath, 'http') === 0) {
    $imageUrl = htmlspecialchars($productImagePath);
} else if ($productImagePath) {
    $imageUrl = '../img/' . htmlspecialchars($productImagePath);
} else {
    $imageUrl = 'images/noimage.png';
}

// カラー設定ロジック
$color_file_map = [
    'オリジナル' => 'original', 'ホワイト' => '白色', 'ブルー' => '青',
    'ゲーミング' => 'ゲーミング', 'イエロー' => '黄色', 'レッド' => '赤',
    'グリーン' => '緑', 'ブラック' => 'ブラック', 'ピンク' => 'ピンク', 'グレー' => 'グレー'
];
$category_colors_list = [
    'C01' => ['オリジナル', 'イエロー', 'ホワイト'], 'C02' => ['オリジナル', 'ブルー', 'グリーン'],
    'C03' => ['オリジナル', 'ブルー', 'レッド'], 'C04' => ['オリジナル', 'ホワイト'],
    'C05' => ['オリジナル', 'ピンク'], 'C06' => ['オリジナル', 'グレー'],
    'C07' => ['オリジナル', 'ゲーミング'], 'C08' => ['オリジナル', 'ブルー'],
];
$category_id = $product['category_id'] ?? 'C01';
$color_names_for_category = $category_colors_list[$category_id] ?? ['オリジナル'];
$colors = [];
foreach ($color_names_for_category as $display_name) {
    $colors[$display_name] = $color_file_map[$display_name] ?? $display_name;
}
$original_color_value = $color_file_map['オリジナル'] ?? 'original';

// JS用ベースURL
$js_base_url = (strpos($productImagePath, 'http') === 0) 
    ? preg_replace('/-[^-]+$/u', '', $productImagePath) 
    : '../img/' . $productImagePath;

// 関連商品取得
try {
    $sql = "SELECT product_id, product_name, product_image FROM product 
            WHERE product_id != :id AND category_id = :cat AND color = 'オリジナル' LIMIT 3";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $product_id, 'cat' => $category_id]);
    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $related_products = []; }
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
    <style>
        .product-title-container { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 10px; }
        .product-title { margin-bottom: 0; flex: 1; }
        .btn-favorite { background: none; border: 2px solid #ccc; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; font-size: 20px; color: #ccc; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; outline: none; }
        .btn-favorite.active { color: #ff4757; border-color: #ff4757; background-color: #fff1f2; }
        .btn-favorite:hover { transform: scale(1.1); }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../common/header.php'; ?>
    <?php
    $breadcrumbs = [
        ['name' => 'ホーム', 'url' => 'G-8_home.php'],
        ['name' => htmlspecialchars($product['product_name'])]
    ];
    //require __DIR__ . '/../common/breadcrumb.php';
    ?>

<main class="product-detail">

    <div class="product-main">
        <div class="product-title-container">
            <h2 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h2>
            <button type="button" id="favoriteBtn" class="btn-favorite <?php echo $is_favorite ? 'active' : ''; ?>" 
                    onclick="toggleFavorite(<?php echo $product['product_id']; ?>)">
                ♥
            </button>
        </div>

        <div class="product-image-area">
            <img id="mainImage"
                    src="<?php echo $imageUrl; ?>"
                    alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>

        <div class="product-info">
            <p class="price">¥<?php echo number_format($product['price']); ?> <span>（税込み）</span></p>

            <div class="product-description">
                <p><?php echo nl2br(htmlspecialchars($product['product_detail'] ?? '')); ?></p>
            </div>
            
            <form action="G-11_add_to_cart.php" method="POST" class="product-actions-form">

                <div class="color-select">
                    <p class="color-label">カラーを選択：</p>
                    <?php $i = 0; foreach ($colors as $display_name => $file_name): ?>
                        <label>
                            <input type="radio" name="color" value="<?php echo htmlspecialchars($file_name); ?>"
                                   data-color="<?php echo htmlspecialchars($file_name); ?>"
                                   <?php if ($i === 0) echo 'checked'; ?>>
                            <?php echo htmlspecialchars($display_name); ?>
                        </label>
                    <?php $i++; endforeach; ?>
                </div>

                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
                <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                
                <div class="action-buttons">
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
                    <?php
                        $relatedImagePath = $related['product_image'] ?? '';
                        $relatedImageUrl = (strpos($relatedImagePath, 'http') === 0) ? htmlspecialchars($relatedImagePath) : '../img/' . htmlspecialchars($relatedImagePath);
                    ?>
                    <a href="G-9_product-detail.php?id=<?php echo $related['product_id']; ?>" class="related-item">
                        <img src="<?php echo $relatedImageUrl; ?>" alt="<?php echo htmlspecialchars($related['product_name']); ?>">
                        <p><?php echo htmlspecialchars($related['product_name']); ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </footer>
</main>

<script>
const IS_LOGGED_IN = <?php echo json_encode($is_logged_in); ?>;
const LOGIN_PAGE_URL = 'G-1_customer-form.php';

// ★ 追加: お気に入り切り替え関数 ★
function toggleFavorite(productId) {
    if (!IS_LOGGED_IN) {
        alert("お気に入り登録にはログインが必要です。");
        location.href = LOGIN_PAGE_URL;
        return;
    }

    const btn = document.getElementById('favoriteBtn');
    
    // G-9_favorite.php へ非同期通信
    fetch('G-9_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'added') {
            btn.classList.add('active'); // 赤くする
        } else if (data.status === 'removed') {
            btn.classList.remove('active'); // グレーに戻す
        }
    })
    .catch(error => console.error('Error:', error));
}

// 既存の購入・レンタル用関数
function goToOrder(pageUrl, productId) {
    if (!IS_LOGGED_IN) {
        alert("購入・レンタルにはログインが必要です。");
        location.href = LOGIN_PAGE_URL;
        return;
    }
    const selectedColorInput = document.querySelector('.product-actions-form input[name="color"]:checked');
    let colorValue = selectedColorInput ? selectedColorInput.value : 'normal';
    location.href = `${pageUrl}?id=${productId}&color=${encodeURIComponent(colorValue)}`;
}

// 画像切り替えロジック
document.addEventListener('DOMContentLoaded', function() {
    const trueBaseUrl = <?php echo json_encode($js_base_url); ?>; 
    const originalColorValue = <?php echo json_encode($original_color_value); ?>; 
    const mainImage = document.getElementById('mainImage');
    const colorRadios = document.querySelectorAll('input[name="color"]');

    colorRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            const selectedColorName = this.value;
            if (selectedColorName === originalColorValue) {
                mainImage.src = trueBaseUrl;
            } else {
                mainImage.src = trueBaseUrl + '-' + selectedColorName;
            }
        });
    });
});
</script>

</body>
</html>