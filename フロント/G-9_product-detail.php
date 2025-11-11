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

// ▼▼▼ 修正点1：「表示名」=>「ファイル名」の辞書を定義 ▼▼▼
$color_file_map = [
    'オリジナル' => 'original', // 'original' は 'オリジナル'時のJS判定用
    'ホワイト'   => '白',         // ★「ホワイト」は「白」というファイル名
    'ブルー'     => '青',         // ★「ブルー」は「青」
    'イエロー'   => 'イエロー',   // (仮)
    'ブラック'   => 'ブラック',   // (仮)
    'ピンク'     => 'ピンク',     // (仮)
    'グレー'     => 'グレー',     // (仮)
    'グリーン'   => 'グリーン',   // (仮)
];
// (※もし 'ピンク' のファイル名が 'pink' なら、'ピンク' => 'pink' のように右側を修正してください)


// カテゴリごとのカラー設定
$category_colors_list = [
    'C01' => ['オリジナル', 'イエロー', 'ホワイト'],
    'C02' => ['オリジナル', 'ブルー', 'グリーン'],
    'C03' => ['オリジナル', 'ブルー', 'レッド'], // ※'レッド' がマップにないため注意
    'C04' => ['オリジナル', 'ホワイト'],
    'C05' => ['オリジナル', 'ピンク'],
    'C06' => ['オリジナル', 'グレー'],
    'C07' => ['オリジナル', 'ゲーミング'], // ※'ゲーミング' がマップにないため注意
    'C08' => ['オリジナル', 'ブルー'],
];

// 該当カテゴリのカラーを取得
$category_id = $product['category_id'] ?? 'C01';
$color_names_for_category = $category_colors_list[$category_id] ?? ['オリジナル'];

// 
// 最終的に $colors 配列を生成 (例: ['オリジナル' => 'original', 'ホワイト' => '白'])
// 
$colors = [];
foreach ($color_names_for_category as $display_name) {
    if (isset($color_file_map[$display_name])) {
        // マップに存在する (例: 'ホワイト' => '白')
        $colors[$display_name] = $color_file_map[$display_name];
    } else {
        // マップにない (例: 'レッド' や 'ゲーミング')
        $colors[$display_name] = $display_name; // とりあえず表示名と同じファイル名を使う
    }
}

// 'オリジナル' が使うファイル名 (例: 'original')
$original_color_value = $color_file_map['オリジナル'] ?? 'original';


// ▼▼▼ 修正点2：拡張子(.jpg) を追加するロジックを「削除」 ▼▼▼
$base_image_url_from_db = $product['product_image'] ?? '';
$js_base_url = '';

if (!empty($base_image_url_from_db)) {
    // ベースURL (例: .../カメラ1-白 -> .../カメラ1)
    // (例: .../カメラ1 -> .../カメラ1 のまま)
    $js_base_url = preg_replace('/-[^-]+$/u', '', $base_image_url_from_db);
}
// ▲▲▲ 修正点2 ここまで ▲▲▲


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
    // 500エラーのタイプミスを修正済み
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
        colorValue = selectedColorInput.value; // (例: '白' や '青' をG-12に渡す)
    }
    location.href = `${pageUrl}?id=${productId}&color=${encodeURIComponent(colorValue)}`;
}


// ▼▼▼ 修正点6：JavaScript (拡張子 .jpg を追加するロジックを削除) ▼▼▼
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. PHPから画像のベース情報を取得
    const trueBaseUrl = <?php echo json_encode($js_base_url); ?>; // (例: .../カメラ1)
    
    // 'original' (ファイル名) を持つラジオボタンの value を取得
    const originalColorValue = <?php echo json_encode($original_color_value); ?>; 

    // 2. 関連するHTML要素を取得
    const mainImage = document.getElementById('mainImage');
    const colorRadios = document.querySelectorAll('input[name="color"]');

    // 3. 全てのラジオボタンに「変更」イベント監視を追加
    colorRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            
            // 4. 選択されたラジオの 'data-color' 属性 (例: '白' or 'original') を取得
            const selectedColorName = this.getAttribute('data-color');

            if (selectedColorName === originalColorValue) {
                // 5a. 「オリジナル」が選ばれた場合 (data-color が 'original')
                // → ベースURL で「オリジナル画像」のURLを構築 (例: .../カメラ1)
                mainImage.src = trueBaseUrl;
            } else {
                // 5b. 「オリジナル」以外 (例: '白') が選ばれた場合
                // → ベースURL + 色名 で新しいURLを構築 (例: .../カメラ1-白)
                mainImage.src = trueBaseUrl + '-' + selectedColorName;
            }
        });
    });
});
// ▲▲▲ 修正点6 ここまで ▲▲▲
</script>

</body>
</html>