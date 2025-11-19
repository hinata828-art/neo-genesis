<?php
// ★★★ 修正: session_start() を追加します ★★★
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
    /*
    require __DIR__ . '/../common/breadcrumb.php';
    */
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

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.getElementById('slider');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

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
            <a href="G-10_product-list.php?category=C01" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/tv.png" alt="テレビ">
                </div>
                <p>テレビ</p>
            </a>
            <a href="G-10_product-list.php?category=C02" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/refrigerator.png" alt="冷蔵庫">
                </div>
                <p>冷蔵庫</p>
            </a>
            <a href="G-10_product-list.php?category=C03" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/microwave.png" alt="電子レンジ">
                </div>
                <p>電子レンジ</p>
            </a>
            <a href="G-10_product-list.php?category=C04" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/camera.png" alt="カメラ">
                </div>
                <p>カメラ</p>
            </a>
            <a href="G-10_product-list.php?category=C05" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/headphone.png" alt="ヘッドホン">
                </div>
                <p>ヘッドホン</p>
            </a>
            <a href="G-10_product-list.php?category=C06" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/washing.png" alt="洗濯機">
                </div>
                <p>洗濯機</p>
            </a>
            <a href="G-10_product-list.php?category=C07" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/laptop.png" alt="ノートPC">
                </div>
                <p>ノートPC</p>
            </a>
            <a href="G-10_product-list.php?category=C08" class="category-item">
                <div class="category-icon-circle">
                    <img src="../img/smartphone.png" alt="スマートフォン">
                </div>
                <p>スマートフォン</p>
            </a>
        </div>
    </section>

</main>

<footer class="footer-banner">
    <div class="footer-box" id="roulette-box" role="button" tabindex="0">
        レンタルで<br>お得な<br>ルーレット！！
    </div>
    <div class="footer-box" id="rental-ok-box" role="button" tabindex="0">
        レンタル<br>OK!!!
    </div>
    <div class="footer-box">
        今すぐ<br>チェック！
    </div>
</footer>

<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modal-title"></h3>
        <p id="modal-text">ここに詳細な情報が表示されます。</p>
        </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // スライダー制御のスクリプトは既に記述済みとして、モーダル関連のスクリプトを記述

    const modal = document.getElementById('myModal');
    const closeBtn = document.getElementsByClassName('close-btn')[0];
    const rouletteBox = document.getElementById('roulette-box');
    const rentalOkBox = document.getElementById('rental-ok-box');
    const modalTitle = document.getElementById('modal-title');
    const modalText = document.getElementById('modal-text');

    /**
     * モーダルを表示する関数
     * @param {string} title - モーダルに表示するタイトル
     * @param {string} text - モーダルに表示する本文
     */
    function openModal(title, text) {
        modalTitle.textContent = title;
        modalText.textContent = text;
        modal.style.display = 'flex'; // CSSでflexを使うことで中央寄せを容易にする
    }

    // --- イベントリスナーの設定 ---

    // 1. ルーレットのボックスクリック
    rouletteBox.addEventListener('click', () => {
        openModal(
            'レンタルでお得なルーレット！！',
            'レンタル商品をご利用いただくと、お得な特典が当たるルーレットに挑戦できます！詳細はキャンペーンページをご確認ください。'
        );
    });

    // 2. レンタルOKのボックスクリック
    rentalOkBox.addEventListener('click', () => {
        openModal(
            'レンタルOK!!!',
            '当社の多くの商品がレンタル可能です！最新の家電をお気軽に、必要な期間だけご利用いただけます。レンタル可能な商品の一覧はこちら。'
        );
    });

    // 3. 閉じるボタンクリックでモーダルを閉じる
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // 4. モーダルの背景（モーダルコンテンツの外側）クリックでモーダルを閉じる
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // 5. キーボード操作（Enter/Space）でもモーダルが開くようにする (アクセシビリティ対応)
    function addKeyboardModalOpen(element, callback) {
        element.addEventListener('keydown', (event) => {
            // Enterキー(keyCode 13) または Spaceキー(keyCode 32)
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault(); // スクロールなどを防ぐ
                callback();
            }
        });
    }

    addKeyboardModalOpen(rouletteBox, () => {
        openModal(
            'レンタルでお得なルーレット！！',
            'レンタル商品をご利用いただくと、お得な特典が当たるルーレットに挑戦できます！詳細はキャンペーンページをご確認ください。'
        );
    });

    addKeyboardModalOpen(rentalOkBox, () => {
        openModal(
            'レンタルOK!!!',
            '当社の多くの商品がレンタル可能です！最新の家電をお気軽に、必要な期間だけご利用いただけます。レンタル可能な商品の一覧はこちら。'
        );
    });
});
</script>

</body>
</html>