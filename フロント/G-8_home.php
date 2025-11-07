<?php
// データベース接続
require '../common/db_connect.php';

// 必要に応じてデータ取得処理をここに書くことができます
// 例）$stmt = $pdo->query("SELECT * FROM rental LIMIT 5");
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
                <div class="item">
                    <img src="../img/sample1.jpg" alt="AQUAVIEW 55V型 4K 有機ELテレビ">
                    <div class="item-info">
                        <p class="item-title">AQUAVIEW 55V型<br>4K 有機ELテレビ</p>
                        <p class="item-price">¥128,000</p>
                        <button class="item-btn">詳細</button>
                    </div>
                </div>

                <div class="item">
                    <img src="../img/sample2.jpg" alt="COOLWAVE 400L 冷蔵庫">
                    <div class="item-info">
                        <p class="item-title">COOLWAVE 400L 冷蔵庫</p>
                        <p class="item-price">¥89,800</p>
                        <button class="item-btn">詳細</button>
                    </div>
                </div>

                <div class="item">
                    <img src="../img/sample3.jpg" alt="BREEZE 6kg 洗濯機">
                    <div class="item-info">
                        <p class="item-title">BREEZE 6kg 洗濯機</p>
                        <p class="item-price">¥49,800</p>
                        <button class="item-btn">詳細</button>
                    </div>
                </div>

                <div class="item">
                    <img src="../img/sample4.jpg" alt="SmartClean 掃除機">
                    <div class="item-info">
                        <p class="item-title">SmartClean 掃除機</p>
                        <p class="item-price">¥25,800</p>
                        <button class="item-btn">詳細</button>
                    </div>
                </div>
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

        prevBtn.addEventListener('click', () => {
            slider.scrollBy({ left: -300, behavior: 'smooth' });
        });
        nextBtn.addEventListener('click', () => {
            slider.scrollBy({ left: 300, behavior: 'smooth' });
        });
    </script>

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


</body>
</html>
