<?php
// データベース接続
require '../common/db_connect.php';

// ===== 商品データ取得 =====
try {
    $sql = "SELECT product_name, price, product_image FROM product LIMIT 8";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

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
                                <button class="item-btn">詳細</button>
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
        const slider = document.getElementById('slider');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    // 商品1枚分の幅を動的に取得
    function getItemWidth() {
        const item = slider.querySelector('.item');
        return item ? item.offsetWidth + 20 : 300; // 20は隙間(gap)の調整
    }

    prevBtn.addEventListener('click', () => {
        const scrollAmount = getItemWidth();
        slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });

    nextBtn.addEventListener('click', () => {
        const scrollAmount = getItemWidth();
        slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });
    </script>

    <!-- ===== カテゴリボタンエリア ===== -->
    <section class="category-section">
        <div class="category-buttons">

            <a href="G-12_tv.php" class="category-item">
                <img src="../img/tv.png" alt="テレビ">
                <p>テレビ</p>
            </a>

            <a href="G-13_refrigerator.php" class="category-item">
                <img src="../img/refrigerator.png" alt="冷蔵庫">
                <p>冷蔵庫</p>
            </a>

            <a href="G-14_microwave.php" class="category-item">
                <img src="../img/microwave.png" alt="電子レンジ">
                <p>電子レンジ</p>
            </a>

            <a href="G-15_camera.php" class="category-item">
                <img src="../img/camera.png" alt="カメラ">
                <p>カメラ</p>
            </a>

            <a href="G-16_headphone.php" class="category-item">
                <img src="../img/headphone.png" alt="ヘッドホン">
                <p>ヘッドホン</p>
            </a>

            <a href="G-17_washing.php" class="category-item">
                <img src="../img/washing.png" alt="洗濯機">
                <p>洗濯機</p>
            </a>

            <a href="G-18_laptop.php" class="category-item">
                <img src="../img/laptop.png" alt="ノートPC">
                <p>ノートPC</p>
            </a>

            <a href="G-19_smartphone.php" class="category-item">
                <img src="../img/smartphone.png" alt="スマートフォン">
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
