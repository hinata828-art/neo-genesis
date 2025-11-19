<?php
// ★★★ 修正点 1: セッション開始処理の追加 ★★★
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ログイン状態を取得
$is_logged_in = isset($_SESSION['customer']['id']);
// ------------------------------------

// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== 商品ID取得 =====
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ===== 商品詳細を取得 =====
try {
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

// ▼▼▼ カラーマップ定義 (変更なし) ▼▼▼
$color_file_map = [
    'オリジナル' => 'original',
    'ホワイト'  => '白色',
    'ブルー'    => '青',
    'ゲーミング' => 'ゲーミング',
    'イエロー'  => '黄色',
    'レッド'    => '赤',
    'グリーン'  => '緑',
    'ブラック'  => 'ブラック',
    'ピンク'    => 'ピンク',
    'グレー'    => 'グレー'
];

// カテゴリごとのカラー設定 (変更なし)
$category_colors_list = [
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
$color_names_for_category = $category_colors_list[$category_id] ?? ['オリジナル'];

// 最終的に $colors 配列を生成
$colors = [];
foreach ($color_names_for_category as $display_name) {
    if (isset($color_file_map[$display_name])) {
        $colors[$display_name] = $color_file_map[$display_name];
    } else {
        $colors[$display_name] = $display_name;
    }
}

// 'オリジナル' が使うファイル名
$original_color_value = $color_file_map['オリジナル'] ?? 'original';


// ▼▼▼ 画像URL生成ロジック (変更なし) ▼▼▼
$base_image_url_from_db = $product['product_image'] ?? '';
$js_base_url = '';

if (!empty($base_image_url_from_db)) {
    $js_base_url = preg_replace('/-[^-]+$/u', '', $base_image_url_from_db);
}


// ===== 関連商品を3件取得 (変更なし) =====
try {
    $sql = "SELECT product_id, product_name, product_image 
            FROM product 
            WHERE product_id != :id 
            AND category_id = :cat 
            AND color = 'オリジナル'
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
    //require __DIR__ . '/../common/breadcrumb.php';
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
            
            <form action="G-11_add_to_cart.php" method="POST" class="product-actions-form">

                <div class="color-select">
                    <p class="color-label">カラーを選択：</p>
                    
                    <?php $i = 0; ?>
                    <?php foreach ($colors as $display_name => $file_name): ?>
                        <label>
                            <input type="radio" 
                                        name="color" 
                                        value="<?php echo htmlspecialchars($file_name); ?>"
                                        data-color="<?php echo htmlspecialchars($file_name); ?>"
                                        <?php if ($i === 0) echo 'checked'; ?>>
                            
                            <?php echo htmlspecialchars($display_name); ?>
                        </label>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                </div>

            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
            <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
            
            <div class="action-buttons">
                <button type="submit" class="btn cart"> カートに追加</button>
                
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
// ★★★ 修正点 2: ログインチェックとページ遷移を統合 ★★★
const IS_LOGGED_IN = <?php echo json_encode($is_logged_in); ?>;
const LOGIN_PAGE_URL = 'G-1_login.php'; // 実際のログインページに合わせてください

function goToOrder(pageUrl, productId) {
    
    if (!IS_LOGGED_IN) {
        // ログインしていない場合、アラートを出してログイン画面へ遷移
        alert("購入・レンタルにはログインが必要です。");
        location.href = LOGIN_PAGE_URL; 
        return; 
    }
    
    // ログイン済みの場合、通常通り処理を続行
    const selectedColorInput = document.querySelector('.product-actions-form input[name="color"]:checked');
    let colorValue = 'normal'; 
    if (selectedColorInput) {
        // data-colorではなく、フォーム送信用の value を取得
        colorValue = selectedColorInput.value; 
    }
    
    location.href = `${pageUrl}?id=${productId}&color=${encodeURIComponent(colorValue)}`;
}


// ▼▼▼ JavaScript (画像切り替えロジックは変更なし) ▼▼▼
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. PHPから画像のベース情報を取得
    const trueBaseUrl = <?php echo json_encode($js_base_url); ?>; 
    // 'original' (ファイル名) を持つラジオボタンの value を取得
    const originalColorValue = <?php echo json_encode($original_color_value); ?>; 

    // 2. 関連するHTML要素を取得
    const mainImage = document.getElementById('mainImage');
    const colorRadios = document.querySelectorAll('input[name="color"]');

    // 3. 全てのラジオボタンに「変更」イベント監視を追加
    colorRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            // 4. 選択されたラジオの value (ファイル名) を取得
            const selectedColorName = this.value; // value属性はファイル名 ('白色', 'original'など)が入っている

            if (selectedColorName === originalColorValue) {
                // 5a. 「オリジナル」が選ばれた場合
                mainImage.src = trueBaseUrl;
            } else {
                // 5b. 「オリジナル」以外 (例: '白色') が選ばれた場合
                mainImage.src = trueBaseUrl + '-' + selectedColorName;
            }
        });
    });
});
// ▲▲▲ ここまで ▲▲▲
</script>

</body>
</html>