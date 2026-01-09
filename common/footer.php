<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
      <link rel="stylesheet" href="../css/footer.css">
</head>
<body>
  <footer class="footer-banner">
    <div class="footer-box" id="roulette-box" role="button" tabindex="0">
        レンタルで<br>お得な<br>ルーレット！！
    </div>
    <div class="footer-box" id="rental-ok-box" role="button" tabindex="0">
        レンタル<br>OK!!!
    </div>
    <div class="footer-box" id="easter-egg-btn" role="button" tabindex="0">
        今すぐ<br>チェック！
    </div>
    
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modal-title"></h3>
        <p id="modal-text">ここに詳細な情報が表示されます。</p>
    </div>
</div>

<div id="rain-container"></div>
<hr>
<div class="footer-menu">
    <nav class="footer-links">
        <a href="#">お問い合わせフォーム</a>
        <a href="#">よくある質問</a>
        <a href="#">会社概要</a>
        <a href="#">利用規約</a>
        <a href="#">プライバシーポリシー</a>
    </nav>

    
    <div class="admin-login">
        <a href="../バック/G-19_admin-login.php">管理者ログインはこちら</a>
    </div>

    <div class="copyright">
        © 2025 ニシムラ.online All Rights Reserved.
    </div>
</div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ===== 1. スライダー制御 =====
    const slider = document.getElementById('slider');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    function getScrollAmount() { return slider.clientWidth; }

    if(prevBtn && slider) {
        prevBtn.addEventListener('click', () => {
            const scrollAmount = getScrollAmount();
            slider.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
    }
    if(nextBtn && slider) {
        nextBtn.addEventListener('click', () => {
            const scrollAmount = getScrollAmount();
            slider.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }

    // ===== 2. モーダル制御 (既存 + 拡張) =====
    const modal = document.getElementById('myModal');
    const closeBtn = document.getElementsByClassName('close-btn')[0];
    const rouletteBox = document.getElementById('roulette-box');
    const rentalOkBox = document.getElementById('rental-ok-box');
    const modalTitle = document.getElementById('modal-title');
    const modalText = document.getElementById('modal-text');

    /**
     * モーダルを表示する関数
     * @param {string} title - タイトル
     * @param {string} text - 本文 (HTMLタグを含む場合は isHtml=true にする)
     * @param {boolean} isHtml - trueならinnerHTML、falseならtextContentを使用
     */
    function openModal(title, text, isHtml = false) {
        modalTitle.textContent = title;
        if (isHtml) {
            modalText.innerHTML = text; 
        } else {
            modalText.textContent = text;
        }
        modal.style.display = 'flex'; // CSSのflexと合わせて中央寄せ
    }

    // 閉じる処理
    closeBtn.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (event) => { if (event.target === modal) modal.style.display = 'none'; });

    // 既存ボタンのクリックイベント
    rouletteBox.addEventListener('click', () => {
        openModal(
            'レンタルでお得なルーレット！！',
            'レンタル商品をご利用いただくと、お得な特典が当たるルーレットに挑戦できます！詳細はキャンペーンページをご確認ください。'
        );
    });

    rentalOkBox.addEventListener('click', () => {
        openModal(
            'レンタルOK!!!',
            '当社の多くの商品がレンタル可能です！最新の家電をお気軽に、必要な期間だけご利用いただけます。レンタル可能な商品の一覧はこちら。'
        );
    });


    // ===== 3. ★★★ イースターエッグ機能 (新規追加) ★★★ =====
    
    const easterEggBtn = document.getElementById('easter-egg-btn'); 
    let rainContainer = document.getElementById('rain-container');
    
    // 念のためJSでもコンテナを作る (HTMLにあれば不要だが安全策)
    if (!rainContainer) {
        rainContainer = document.createElement('div');
        rainContainer.id = 'rain-container';
        document.body.appendChild(rainContainer);
    }

    let clickCount = 0;
    const requiredClicks = 10;
    
    // 落下させる画像 (パスが正しいか要確認)
    const itemImages = [
        '../img/tv.png', '../img/refrigerator.png', '../img/microwave.png', 
        '../img/camera.png', '../img/headphone.png', '../img/washing.png', 
        '../img/laptop.png', '../img/smartphone.png'
    ];

    // ▼ アイテム落下関数
    function dropItem() {
        const img = document.createElement('img');
        // ランダムに画像を選ぶ
        img.src = itemImages[Math.floor(Math.random() * itemImages.length)];
        img.className = 'falling-item'; // CSSでアニメーション定義
        
        // ランダムな位置 (横幅の0%〜90%)
        img.style.left = Math.random() * 90 + 'vw'; 
        // ランダムなサイズ (30px〜70px)
        const size = Math.random() * 40 + 30; 
        img.style.width = size + 'px';
        img.style.height = 'auto';
        
        // アニメーション速度 (1秒〜2秒)
        img.style.animationDuration = (Math.random() * 1 + 1) + 's'; 

        rainContainer.appendChild(img);

        // アニメーション終了後に要素を削除 (メモリ節約)
        img.addEventListener('animationend', () => {
            img.remove();
        });
    }

    // ▼ クリック時の処理
    if (easterEggBtn) {
        easterEggBtn.addEventListener('click', () => {
            clickCount++;
            dropItem(); // 画像を降らせる

            if (clickCount >= requiredClicks) {
                // 10回達成
                clickCount = 0;
                
                // サーバーへクーポン発行リクエスト
                fetch('G-18_easter-egg-process.php', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // 成功時: 既存のモーダルを使ってメッセージを表示
                        openModal(
            '🎉 よく見つけましたね！',
                `おめでとうございます！<br>
                全商品に使える <strong>${data.discount_rate}% 割引クーポン</strong> をゲットしました！<br><br>
                <a href="G-25_coupon-list.php" style="color:blue; text-decoration:underline;">
                クーポン一覧を確認する
                </a>`,
                true
                );

                    } else {
                        // 失敗時 (例: ログインしていない等)
                        openModal('残念...', 'クーポンの獲得に失敗しました: ' + data.message);
                          
  
                    }
                })
                .catch(err => {
                    console.error(err);
                    openModal('エラー', '通信エラーが発生しました。ログインしているか確認してください。');
                });
            }
        });
    }
});
</script>  

</body>
</html>