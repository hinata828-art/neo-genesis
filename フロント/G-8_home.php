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
    <div class="footer-box" id="roulette-box" role="button" tabindex="0">レンタルで<br>お得な<br>ルーレット！！</div>
    <div class="footer-box" id="rental-ok-box" role="button" tabindex="0">レンタル<br>OK!!!</div>
    
    <div class="footer-box" id="easter-egg-btn" role="button" tabindex="0">
        今すぐ<br>チェック！
    </div>
</footer>

<div id="rain-container"></div>

<div id="coupon-modal" class="custom-modal-overlay">
    <div class="custom-modal-content">
        <span class="custom-close-btn">&times;</span>
        <div class="alert-icon">🎁</div>
        <h3 id="coupon-title"></h3>
        <p id="coupon-message"></p>
        <a href="G-25_coupon-list.php" class="btn btn-coupon-list">クーポン一覧へ</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('slider');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    // イースターエッグ関連の要素
    const easterEggBtn = document.getElementById('easter-egg-btn');
    const rainContainer = document.getElementById('rain-container');
    const couponModal = document.getElementById('coupon-modal');
    const couponTitle = document.getElementById('coupon-title');
    const couponMessage = document.getElementById('coupon-message');
    const closeCouponModalBtn = document.querySelector('.custom-close-btn');

    let clickCount = 0;
    const requiredClicks = 10;
    
    // 落下させる画像リスト (カテゴリ画像を使用)
    const itemImages = [
        '../img/tv.png', '../img/refrigerator.png', '../img/microwave.png', 
        '../img/camera.png', '../img/headphone.png', '../img/washing.png', 
        '../img/laptop.png', '../img/smartphone.png'
    ];

    // --- スライダー制御ロジック (既存) ---
    function getScrollAmount() { return slider.clientWidth; }
    prevBtn.addEventListener('click', () => {
        const scrollAmount = getScrollAmount();
        slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });
    nextBtn.addEventListener('click', () => {
        const scrollAmount = getScrollAmount();
        slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });


    // --- ★★★ イースターエッグロジック ★★★ ---

    // 1. 家電をランダムに落下させる関数
    function dropItem() {
        const randomImage = itemImages[Math.floor(Math.random() * itemImages.length)];
        const item = document.createElement('img');
        item.src = randomImage;
        item.className = 'falling-item';
        
        // 画面のどこか上部からランダムにスタート
        item.style.left = `${Math.random() * 95}vw`;
        item.style.animationDuration = `${Math.random() * 1.5 + 0.5}s`; // 0.5s〜2.0s
        rainContainer.appendChild(item);

        // 落下アニメーションが終わったら削除
        item.addEventListener('animationend', () => {
            item.remove();
        });
    }

    // 2. 10回クリック判定と処理
    easterEggBtn.addEventListener('click', () => {
        clickCount++;
        dropItem(); // クリックごとに家電を落下させる

        if (clickCount >= requiredClicks) {
            // 10回達成
            clickCount = 0; // カウントをリセット
            
            // サーバーにクーポン生成を要求
            fetchCouponAndDisplay();
            
        } else {
            // 進行中のメッセージ表示 (デバッグ用。必要に応じて削除可)
            resultMessage.textContent = `あと ${requiredClicks - clickCount} 回のクリックで特典！`;
        }
    });

    // 3. サーバーへリクエストを送り、クーポン情報を取得・表示
    function fetchCouponAndDisplay() {
        resultMessage.textContent = '特典生成中...';

        fetch('G-8_easter-egg-process.php', { method: 'POST' })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                couponTitle.textContent = '🎉 よく見つけましたね！';
                couponMessage.innerHTML = `
                    おめでとうございます！<br>
                    全商品に使える **${data.discount_rate}% 割引クーポン** をゲットしました！
                `;
                couponModal.style.display = 'flex';
                resultMessage.textContent = '特典をゲットしました！';
            } else {
                alert(`特典獲得エラー: ${data.message}`);
                resultMessage.textContent = '特典獲得に失敗しました。';
            }
        })
        .catch(error => {
            alert(`通信エラーが発生しました: ${error.message}`);
            resultMessage.textContent = 'エラーが発生しました。';
        });
    }

    // モーダルを閉じる処理
    closeCouponModalBtn.addEventListener('click', () => {
        couponModal.style.display = 'none';
    });
});
</script>

</body>
</html>