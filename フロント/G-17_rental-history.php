<?php
// 1. セッションを開始
session_start();

// 2. デバッグ（エラー表示）設定 (開発中のみ)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. 共通のデータベース接続ファイルを読み込む
require '../common/db_connect.php'; // $pdo 変数がここで作成されると仮定

// 4. 表示するデータを初期化
$products = []; // 商品リスト用の配列
$order_info = null; // 注文・レンタル共通情報用
$error_message = '';
$is_cancellable = false; // ★【追加】キャンセル可能か判定するフラグ
$transaction_id = 0; // ★【追加】IDを保持する変数

// ▼▼▼ ルーレット用に追加 ▼▼▼
$show_roulette = false; // ルーレットを表示するか
$prizes_for_js = [];    // ルーレットの景品リスト (JS用)
// ▲▲▲ ここまで ▲▲▲

try {
    // 5. URLから表示したい「取引ID」を取得
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];
    
    // 6. データベースからレンタル情報を取得
    // ▼▼▼ r.coupon_claimed を SELECT に追加 ▼▼▼
    $sql = "SELECT 
                t.transaction_date, 
                t.payment,
                t.delivery_status,
                t.total_amount,
                p.product_id,
                p.product_name, 
                p.product_image,
                p.price,
                r.rental_start,
                r.rental_end,
                r.coupon_claimed  /* ★ ルーレット回数制限に使う */
            FROM transaction_table AS t
            JOIN rental AS r ON t.transaction_id = r.transaction_id
            JOIN product AS p ON r.product_id = p.product_id
            WHERE t.transaction_id = :id"; 

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        throw new Exception('該当するレンタル履歴が見つかりません。');
    }
    
    // 注文共通情報を $products の最初の要素から取得
    $order_info = $products[0];
    
    // 日付フォーマットの整形
    $order_info['start_date_formatted'] = date('Y/m/d H:i', strtotime($order_info['rental_start']));
    $order_info['return_date_formatted'] = date('Y/m/d', strtotime($order_info['rental_end']));
    
    // ステータスに応じて表示テキストと「キャンセル可否」を決定
    switch ($order_info['delivery_status']) { 
        case '注文受付':
            $order_info['return_status_text'] = '発送準備中です';
            $is_cancellable = true;
            break;
        case 'レンタル中':
            $order_info['return_status_text'] = '返却予定日: ' . $order_info['return_date_formatted'];
            break;
        case '返却済み':
            $order_info['return_status_text'] = '返却完了済み';
            
            // ▼▼▼ ルーレット表示判定 ▼▼▼
            if ($order_info['coupon_claimed'] == 0) {
                // 「返却済み」かつ「未抽選」の場合
                $show_roulette = true;
                
                // 景品リストをDBから取得 (ID 2〜7)
                $sql_prizes = "SELECT coupon_name FROM coupon 
                               WHERE coupon_id IN (2, 3, 4, 5, 6, 7)
                               ORDER BY discount_rate ASC";
                
                $stmt_prizes = $pdo->prepare($sql_prizes);
                $stmt_prizes->execute();
                
                // JSの sectors 配列用に、景品名だけの配列も作る
                $prizes_for_js = $stmt_prizes->fetchAll(PDO::FETCH_COLUMN, 0);
            }
            // ▲▲▲ ルーレット判定ここまで ▲▲▲
            break;

        case 'キャンセル済み':
            $order_info['return_status_text'] = 'この取引はキャンセルされました';
            break;
        default:
            $order_info['return_status_text'] = 'ステータス: ' . htmlspecialchars($order_info['delivery_status']);
            break;
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// G-16/G-4 と同じステータス色分け関数
function getStatusClass($status) {
    if ($status == 'キャンセル済み') return 'status-cancelled';
    if ($status == '配達完了' || $status == '返却済み') return 'status-delivered';
    return 'status-processing'; // 注文受付、レンタル中 など
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レンタル履歴詳細</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-16_order-history.css"> 
    
    <style>
        /* (デモコードのCSSをベースに、スマホ対応を強化) */
        #roulette-container {
            margin: 30px auto;
            text-align: center;
            padding: 16px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        #roulette {
            position: relative;
            margin: 20px auto 50px auto; /* 矢印の分、下に余白 */
        }
        #pointer {
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-top: 40px solid red;
            position: absolute;
            top: -40px; /* canvasの上端に合わせる */
            left: calc(50% - 20px);
            z-index: 10; /* canvasより手前 */
        }
        #canvas {
            display: block;
            margin: 0 auto;
            /* JSでサイズが設定される */
        }
        #spin {
            margin-top: 20px;
            padding: 12px 24px;
            background-color: #28a745;
            color: white;
            border: none;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #spin:hover {
            background-color: #218838;
        }
        #spin:disabled {
            background-color: #999;
            cursor: not-allowed;
        }
        #result {
            margin-top: 20px;
            font-size: 1.2em;
            font-weight: bold;
            color: #d9534f;
        }
    </style>
    </head>
<body>
    <?php require '../common/header.php'; // ヘッダーを読み込む ?>
    <div class="container">

        <header class="header">
        <a href="G-4_member-information.php"><img src="../img/modoru.png" alt="戻る" class="back-link"></a>
            <h1 class="header-title">レンタル履歴</h1>
            <span class="header-dummy"></span>
        </header>

        <main class="main-content">

            <?php if (!empty($error_message)): ?>
                <div class="error-box">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
                
            <?php elseif (!empty($products)): ?>
                
                <?php foreach ($products as $product): ?>
                    <section class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="商品画像" class="product-image">
                        </div>
                        <div class="product-info">
                            <h2 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                            <p class="product-price">単価: ¥<?php echo number_format($product['price']); ?></p> 
                        </div>
                        <div class="button-group">
                            <a href="G-9_product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-detail">詳細</a>
                            <a href="G-14_rental.php?id=<?php echo $product['product_id']; ?>" class="btn btn-purchase">再度レンタル</a>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="detail-section">
                    <h2 class="section-title">レンタル期間</h2>
                    <div class="detail-box">
                        <div class="detail-row">
                            <span class="detail-label">レンタル開始日時</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order_info['start_date_formatted']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">返却予定日</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order_info['return_date_formatted']); ?></span>
                        </div>
                    </div>
                </section>

                <section class="detail-section">
                    <h2 class="section-title">お支払情報</h2>
                    <div class="detail-box">
                        <p class="payment-info">
                            <?php echo htmlspecialchars($order_info['payment']); ?> | 
                            合計金額: ¥<?php echo number_format($order_info['total_amount']); ?>
                        </p>
                    </div>
                </section>
                
                <section class="delivery-status">
                    <p class="<?php echo htmlspecialchars(getStatusClass($order_info['delivery_status'])); ?>">
                        <?php echo htmlspecialchars($order_info['return_status_text']); ?>
                    </p>
                </section>

                <?php if ($show_roulette && !empty($prizes_for_js)): ?>
                <section id="roulette-container">
                    <h2 class="section-title">返却ありがとうルーレット！</h2>
                    <p>次回使える購入クーポンが当たります！</p>
                    
                    <div id="roulette">
                        <div id="pointer"></div>
                        <canvas id="canvas"></canvas>
                    </div>

                    <button id="spin">スピンする</button>
                    <p id="result"></p>
                </section>
                <?php endif; ?>
                <?php endif; ?>

        </main>

       <footer class="footer">
            <?php if ($is_cancellable): ?>
                <a href="#" id="open-cancel-modal" class="footer-link">レンタルキャンセルはコチラ</a>
            <?php endif; ?>
       </footer>

    </div> 
    
    <div id="cancel-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            
            <button id="close-modal" class="modal-close-btn">&times;</button>
            
            <div class="modal-icon">
                <img src="../img/alert.png" alt="" style="width: 60px, height: 60px;">
            </div>

            <h2>レンタルをキャンセルしますか？</h2>
            
            <div class="modal-buttons">
                <a href="G_transaction-cancel.php?id=<?php echo htmlspecialchars($transaction_id); ?>" id="confirm-yes" class="btn btn-danger">はい</a>
                
                <button id="confirm-no" class="btn btn-secondary">いいえ</button>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const openBtn = document.getElementById('open-cancel-modal');
        if (openBtn) {
            const modal = document.getElementById('cancel-modal');
            const closeBtn = document.getElementById('close-modal');
            const noBtn = document.getElementById('confirm-no');

            openBtn.addEventListener('click', function(e) {
                e.preventDefault(); 
                modal.style.display = 'flex'; 
            });

            noBtn.addEventListener('click', function() {
                modal.style.display = 'none'; 
            });

            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none'; 
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) { 
                    modal.style.display = 'none'; 
                }
            });
        }
    });
    </script>
    
    <?php if ($show_roulette && !empty($prizes_for_js)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. 要素とPHPからのデータを取得 ---
        const canvas = document.getElementById('canvas');
        if (!canvas) return; // ルーレットが描画対象外なら何もしない
        
        const ctx = canvas.getContext('2d');
        const pointer = document.getElementById('pointer');
        const spinButton = document.getElementById('spin');
        const resultP = document.getElementById('result');
        const rouletteContainer = document.getElementById('roulette-container');
        
        // PHPから渡された景品リスト
        // ★ デモコードの ["0.5%off", ...] を、DBから取得したリストに置き換え
        const sectors = <?php echo json_encode($prizes_for_js); ?>; 
        const transactionId = <?php echo $transaction_id; ?>;
        
        // (デモコードの景品色を流用)
        const colors = ["#FF0000", "#0000FF", "#00FF00", "#FFFF00", "#FFC0CB", "#800080", "#FFA500", "#FFD700"];
        let angle = 0;
        let canvasSize = 0;
        const sectorAngle = 2 * Math.PI / sectors.length;

        // --- 2. ルーレット描画 (デモコードほぼそのまま + スマホ対応) ---
        function setCanvasSize() {
            // コンテナの幅に合わせてリサイズ
            canvasSize = rouletteContainer.clientWidth * 0.8;
            if (canvasSize < 200) canvasSize = 200; // 最小サイズ
            if (canvasSize > 320) canvasSize = 320; // ★ G-16 (480px幅) に合わせ最大サイズを調整
            
            canvas.width = canvasSize;
            canvas.height = canvasSize;
            
            // ポインターの位置も調整
            pointer.style.top = `${-canvasSize * 0.1}px`;
            pointer.style.left = `calc(50% - 20px)`; // (矢印の幅 40px / 2)
            
            drawRoulette();
        }

        function drawRoulette() {
            ctx.clearRect(0, 0, canvas.width, canvas.height); 
            ctx.save();
            ctx.translate(canvasSize / 2, canvasSize / 2);
            ctx.rotate(angle); 

            sectors.forEach((sector, index) => {
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.arc(0, 0, canvasSize / 2, index * sectorAngle, (index + 1) * sectorAngle);
                ctx.fillStyle = colors[index % colors.length]; // 色が足りなくても循環させる
                ctx.fill();
                ctx.closePath();

                ctx.save();
                ctx.rotate((index + 0.5) * sectorAngle);
                ctx.textAlign = "right";
                ctx.font = `bold ${canvasSize * 0.05}px Arial`;
                ctx.fillStyle = "#FFFFFF";
                ctx.textBaseline = "middle";
                // (文字がはみ出ないよう調整)
                ctx.fillText(sector, canvasSize * 0.45, 0, canvasSize * 0.4); 
                ctx.restore();
            });
            ctx.restore(); 
        }

        // --- 3. スピン処理 (★ロジックを「サーバーサイド抽選」に変更) ---
        function spinRoulette() {
            spinButton.disabled = true;
            resultP.textContent = "抽選中...";

            // (A) サーバーに「抽選して！」と依頼
            fetch('G-17_spin_roulette.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ transaction_id: transactionId })
            })
            .then(response => {
                if (!response.ok) {
                    // (サーバーが500エラーなどを返した場合)
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                // (B) サーバーから「抽選結果」が届いたら...
                if (data.status === 'success') {
                    
                    // (C) 当選した景品の「添字(index)」(例: 1) と「名前」を取得
                    const prizeIndex = data.prize_index;
                    const prizeName = data.prize_name;

                    // (D) その景品が止まる「角度」を計算
                    let targetSectorCenter = (prizeIndex + 0.5) * sectorAngle;
                    let targetAngle = (2 * Math.PI) - targetSectorCenter + (Math.PI / 2);
                    
                    // (最低10回転 + 最終位置)
                    const totalRotation = 10 * (2 * Math.PI) + targetAngle;

                    // (E) アニメーションを実行
                    animateSpin(totalRotation, prizeName);

                } else {
                    // (サーバーがエラーを返した場合)
                    resultP.textContent = `エラー: ${data.message}`;
                    spinButton.disabled = false;
                }
            })
            .catch(error => {
                // (通信エラー or サーバー500エラー)
                resultP.textContent = `エラー: ${error.message || '通信に失敗しました。'}`;
                spinButton.disabled = false;
                console.error('通信エラー:', error);
            });
        }
        
        // --- 4. アニメーション (デモコードベース) ---
        function animateSpin(targetAngle, prizeName) {
            const spinDuration = 3000; // 3秒
            const startTime = performance.now();

            function animate(time) {
                const elapsed = time - startTime;
                if (elapsed < spinDuration) {
                    // イージング（だんだん遅くなる）
                    const t = elapsed / spinDuration;
                    const easedT = 1 - Math.pow(1 - t, 3); // easeOutCubic
                    angle = (targetAngle * easedT) % (2 * Math.PI);
                    
                    drawRoulette();
                    requestAnimationFrame(animate);
                } else {
                    // (F) 最終的な角度に固定
                    angle = targetAngle % (2 * Math.PI);
                    drawRoulette(); 
                    
                    // (G) 結果を表示
                    resultP.textContent = `おめでとうございます！ ${prizeName} をゲットしました！`;
                    // (ボタンを完全に非表示にする)
                    spinButton.style.display = 'none'; 
                }
            }
            requestAnimationFrame(animate);
        }

        // --- 5. 初期化 ---
        spinButton.addEventListener('click', spinRoulette);
        window.addEventListener('resize', setCanvasSize);
        setCanvasSize(); // 最初の描画
    });
    </script>
    <?php endif; ?>

</body>
</html>