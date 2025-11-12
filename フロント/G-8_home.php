<?php
// データベース接続
require '../common/db_connect.php';

// ===== 商品データ取得 =====
try {
    // ランダムで8件取得
    $sql = "SELECT product_name, price, product_image, product_id 
            FROM product 
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
    require __DIR__ . '/../common/breadcrumb.php';
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
                    <div class="item">
                        <img src="<?php echo htmlspecialchars($p['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($p['product_name']); ?>">
                        <div class="item-info">
                            <p class="item-title">
                                <?php echo htmlspecialchars($p['product_name']); ?>
                            </p>
                            <p class="item-price">
                                ¥<?php echo number_format($p['price']); ?>
                            </p>
                            <!-- ★ ここだけリンクにする -->
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



    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.getElementById('slider');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        // ★ スクロール量の計算 (1ページ分 = スライダーの表示幅)
        function getScrollAmount() {
            return slider.clientWidth;
        }

        prevBtn.addEventListener('click', () => {
            const scrollAmount = getScrollAmount();
            slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });

        nextBtn.addEventListener('click', () => {
            const scrollAmount = getScrollAmount();
            slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    });
    </script>

    <section class="category-section">
        <div class="category-buttons">
            <a href="G-12_tv.php" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/tv.png" alt="テレビ">
                </div>
                <p>テレビ</p>
            </a>
            <a href="G-13_refrigerator.php" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/refrigerator.png" alt="冷蔵庫">
                </div>
                <p>冷蔵庫</p>
            </a>
            <a href="G-14_microwave.php" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/microwave.png" alt="電子レンジ">
                </div>
                <p>電子レンジ</p>
            </a>
            <a href="G-15_camera.php" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/camera.png" alt="カメラ">
                </div>
                <p>カメラ</p>
            </a>
            <a href="G-16_headphone.php" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/headphone.png" alt="ヘッドホン">
                </div>
                <p>ヘッドホン</p>
            </a>
            <a href="G-17_washing.php" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/washing.png" alt="洗濯機">
                </div>
                <p>洗濯機</p>
            </a>
            <a href="G-18_laptop.php" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/laptop.png" alt="ノートPC">
                </div>
                <p>ノートPC</p>
            </a>
            <a href="G-19_smartphone.php" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/smartphone.png" alt="スマートフォン">
                </div>
                <p>スマートフォン</p>
            </a>
        </div>
    </section>

</main>

<footer class="footer-banner">
    <div class="footer-box">レンタルで<br>お得な<br>ルーレット！！</div>
    <div class="footer-box">レンタル<br>OK!!!</div>
    <div class="footer-box">今すぐ<br>チェック！</div>
</footer>

</body>
</html>