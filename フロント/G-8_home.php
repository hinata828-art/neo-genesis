<?php
// データベース接続
require '../common/db_connect.php';

// ===== 商品データ取得 =====
try {
    // おすすめ商品を8件取得
    $sql = "SELECT product_name, price, product_image, product_id FROM product LIMIT 8";
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
    <!-- スマホ拡大防止 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>トップページ</title>

    <!-- 共通ヘッダーCSS -->
    <link rel="stylesheet" href="../css/header.css">
    <!-- パンくずCSS -->
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <!-- ページ固有CSS -->
    <link rel="stylesheet" href="../css/G-8_home.css">
</head>

<body>
    <!-- ▼ 共通ヘッダー -->
    <?php require __DIR__ . '/../common/header.php'; ?>
    <!-- ▲ 共通ヘッダー -->

    <!-- ▼ パンくずリスト -->
    <?php
    $breadcrumbs = [
        ['name' => '現在のページ']
    ];
    require __DIR__ . '/../common/breadcrumb.php';
    ?>
    <!-- ▲ パンくずリスト -->

<main>

    <!-- ===== おすすめ商品セクション（DB連動） ===== -->
    <section class="pickapp">
        <div class="pickapp-label">
            <h2>おすすめ商品！！！</h2>
        </div>

        <!-- スライダー全体 -->
        <div class="slider-container">
            <!-- 左ボタン -->
            <button class="slider-btn left" id="prevBtn">&#10094;</button>

            <!-- スライダー内容 -->
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
                                <!-- ★詳細ボタンに商品IDへのリンクを追加 (仮) -->
                                <a href="G-5_product-detail.php?id=<?php echo $p['product_id']; ?>" class="item-btn">詳細</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>現在、おすすめ商品はありません。</p>
                <?php endif; ?>
            </div>

            <!-- 右ボタン -->
            <button class="slider-btn right" id="nextBtn">&#10095;</button>
        </div>
    </section>

    <!-- スライダー操作スクリプト -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.getElementById('slider');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        // 商品1枚分の幅を動的に取得
        function getItemWidth() {
            const item = slider.querySelector('.item');
            if (!item) return 300; // アイテムがない場合のデフォルト値
            
            // CSSのgap (20px) を考慮
            const itemStyle = window.getComputedStyle(item);
            const itemMargin = parseFloat(itemStyle.marginRight) || 0;
            const itemWidth = item.offsetWidth;
            
            // pickapp-itemsのgap (20px) を取得
            const sliderStyle = window.getComputedStyle(slider);
            const gap = parseFloat(sliderStyle.gap) || 20;

            return itemWidth + gap;
        }

        prevBtn.addEventListener('click', () => {
            const scrollAmount = getItemWidth();
            slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });

        nextBtn.addEventListener('click', () => {
            const scrollAmount = getItemWidth();
            slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    });
    </script>

    <!-- ===== ★修正：カテゴリボタンエリア (HTML構造変更) ===== -->
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
