<?php
// ★★★ 修正: session_start() を追加 ★★★
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ----------------------------------------------------

// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== 商品データ取得（オリジナルカラーのみ） =====
try {
    $sql = "SELECT product_name, price, product_image, product_id 
            FROM product 
            WHERE color = 'オリジナル'
            ORDER BY RAND()
            LIMIT 8";
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    echo '商品データ取得エラー: ' . $e->getMessage();
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>トップページ</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <link rel="stylesheet" href="../css/G-8_home.css">
</head>

<body>
    <?php require_once __DIR__ . '/../common/header.php'; ?>
    <?php
    $breadcrumbs = [
        ['name' => '現在のページ']
    ];
    /* require __DIR__ . '/../common/breadcrumb.php'; */
    ?>

<main>

    <section class="pickapp">
        <div class="pickapp-label">
            <h2>おすすめ商品！！！</h2>
        </div>

        <div class="slider-container">
            <button class="slider-btn left" id="prevBtn">&#10094;</button>

            <div class="pickapp-items" id="slider">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <?php
                            // ★★★ 追記: 画像のパスを動的に決定するロジック ★★★
                            $productImagePath = $p['product_image'];
                            $imageUrl = '';
                            
                            // 値が「http」で始まる場合はDB直接保存のURLと判断
                            if (strpos($productImagePath, 'http') === 0) {
                                $imageUrl = htmlspecialchars($productImagePath);
                            } else if ($productImagePath) {
                                // それ以外の場合はサーバーフォルダ保存のファイル名と判断し、パスを結合
                                $imageUrl = '../img/' . htmlspecialchars($productImagePath);
                            } else {
                                // 画像データがない場合のデフォルト画像 (必要に応じて設定)
                                $imageUrl = 'path/to/default_image.png'; 
                            }
                            // ★★★ 追記ここまで ★★★
                        ?>
                        <div class="item">
                            <img src="<?php echo $imageUrl; ?>" 
                                 alt="<?php echo htmlspecialchars($p['product_name']); ?>">
                            <div class="item-info">
                                <p class="item-title"><?php echo htmlspecialchars($p['product_name']); ?></p>
                                <p class="item-price">¥<?php echo number_format($p['price']); ?></p>
                                <a href="G-9_product-detail.php?id=<?php echo $p['product_id']; ?>" class="item-btn">
                                    詳細を見る
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>現在、おすすめ商品はありません。</p>
                <?php endif; ?>
            </div>

            <button class="slider-btn right" id="nextBtn">&#10095;</button>
        </div>
    </section>

    <section class="category-section">
        <div class="category-buttons">
            <a href="G-10_product-list.php?category=C01" class="category-item">
                <div class="category-icon-circle"><img src="../img/tv.png" alt="テレビ"></div>
                <p>テレビ</p>
            </a>
            <a href="G-10_product-list.php?category=C02" class="category-item">
                <div class="category-icon-circle"><img src="../img/refrigerator.png" alt="冷蔵庫"></div>
                <p>冷蔵庫</p>
            </a>
            <a href="G-10_product-list.php?category=C03" class="category-item">
                <div class="category-icon-circle"><img src="../img/microwave.png" alt="電子レンジ"></div>
                <p>電子レンジ</p>
            </a>
            <a href="G-10_product-list.php?category=C04" class="category-item">
                <div class="category-icon-circle"><img src="../img/camera.png" alt="カメラ"></div>
                <p>カメラ</p>
            </a>
            <a href="G-10_product-list.php?category=C05" class="category-item">
                <div class="category-icon-circle"><img src="../img/headphone.png" alt="ヘッドホン"></div>
                <p>ヘッドホン</p>
            </a>
            <a href="G-10_product-list.php?category=C06" class="category-item">
                <div class="category-icon-circle"><img src="../img/washing.png" alt="洗濯機"></div>
                <p>洗濯機</p>
            </a>
            <a href="G-10_product-list.php?category=C07" class="category-item">
                <div class="category-icon-circle"><img src="../img/laptop.png" alt="ノートPC"></div>
                <p>ノートPC</p>
            </a>
            <a href="G-10_product-list.php?category=C08" class="category-item">
                <div class="category-icon-circle"><img src="../img/smartphone.png" alt="スマートフォン"></div>
                <p>スマートフォン</p>
            </a>
        </div>
    </section>

</main>


</body>
</html>