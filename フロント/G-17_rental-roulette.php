<?php
// G-17_rental-roulette.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php'; 

// データの初期化
$transaction_id = 0;
$show_roulette = false; 
$prizes_for_js = [];
$error_message = '';

try {
    // ログインチェック
    $customer_id = $_SESSION['customer']['id'] ?? null;
    if ($customer_id === null) {
        throw new Exception('ログイン情報が確認できません。');
    }
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];

    // DBチェックロジック (省略せず記述)
    $sql_check = "SELECT 
                    r.coupon_claimed, 
                    p.category_id,
                    t.delivery_status
                FROM rental AS r
                JOIN product AS p ON r.product_id = p.product_id
                JOIN transaction_table AS t ON r.transaction_id = t.transaction_id
                WHERE r.transaction_id = :tid 
                  AND t.customer_id = :cid
                LIMIT 1";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':tid' => $transaction_id, ':cid' => $customer_id]);
    $rental_info = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$rental_info) {
        throw new Exception('対象のレンタル履歴が見つかりません。');
    }
    if ($rental_info['delivery_status'] !== '返却済み') {
        throw new Exception('このレンタルはまだ返却が完了していません。');
    }
    if ($rental_info['coupon_claimed'] == 1) {
        throw new Exception('このレンタルでは既にルーレットを回しています。');
    }

    // ルーレット表示許可
    $show_roulette = true;
    
    // 景品リスト取得
    $sql_prizes = "SELECT coupon_name FROM coupon 
                   WHERE coupon_id IN (2, 3, 4, 5, 6, 7)
                   ORDER BY coupon_id ASC";
    $stmt_prizes = $pdo->prepare($sql_prizes);
    $stmt_prizes->execute();
    $prizes_for_js = $stmt_prizes->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (count($prizes_for_js) < 6) {
        throw new Exception('景品データが不足しています。');
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>割引ルーレット!!!</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-17_rental-history.css"> 
</head>
<body>
    <div class="container">

        <header class="header">
        <a href="G-17_rental-history.php?id=<?php echo htmlspecialchars($transaction_id); ?>"><img src="../img/modoru.png" alt="戻る" class="back-link"></a>
            <h1 class="header-title">ルーレット</h1>
            <span class="header-dummy"></span>
        </header>

        <main class="main-content">
            <?php if (!empty($error_message)): ?>
                <div class="error-box">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
                <div style="text-align:center; margin-top:20px;">
                    <a href="G-17_rental-history.php?id=<?php echo htmlspecialchars($transaction_id); ?>" class="btn-roulette-back" style="background:#999; padding:10px 20px; color:white; text-decoration:none; border-radius:5px;">履歴詳細に戻る</a>
                </div>

            <?php elseif ($show_roulette && !empty($prizes_for_js)): ?>
                <section id="roulette-container">
                    <h2 class="section-title">割引クーポンルーレット！</h2>
                    <p>次回使える購入クーポンが当たります！</p>
                    
                    <div id="roulette">
                        <div id="pointer"></div>
                        <canvas id="canvas"></canvas>
                    </div>

                    <button id="spin">スピンする</button>
                    <p id="result"></p>
                </section>
            <?php endif; ?>
        </main>
    </div> 
    
    <?php if ($show_roulette && !empty($prizes_for_js)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('canvas');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const spinButton = document.getElementById('spin');
        const resultP = document.getElementById('result');
        const rouletteContainer = document.getElementById('roulette-container');
        
        const sectors = <?php echo json_encode($prizes_for_js); ?>; 
        const transactionId = <?php echo $transaction_id; ?>;
        
        const colors = ["#FF0000", "#0000FF", "#00FF00", "#FFFF00", "#FFC0CB", "#800080", "#FFA500", "#FFD700"];
        let angle = 0;
        let canvasSize = 0;
        const sectorAngle = 2 * Math.PI / sectors.length;

        function setCanvasSize() {
            if(rouletteContainer.clientWidth > 0){
                canvasSize = rouletteContainer.clientWidth * 0.8;
            } else {
                canvasSize = 300;
            }
            if (canvasSize < 200) canvasSize = 200;
            if (canvasSize > 320) canvasSize = 320;
            
            canvas.width = canvasSize;
            canvas.height = canvasSize;
            drawRoulette();
        }

        function drawRoulette() {
            if (!ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height); 
            ctx.save();
            ctx.translate(canvasSize / 2, canvasSize / 2);
            ctx.rotate(angle); 

            sectors.forEach((sector, index) => {
                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.arc(0, 0, canvasSize / 2, index * sectorAngle, (index + 1) * sectorAngle);
                ctx.fillStyle = colors[index % colors.length];
                ctx.fill();
                ctx.closePath();

                ctx.save();
                ctx.rotate((index + 0.5) * sectorAngle);
                ctx.textAlign = "right";
                ctx.font = `bold ${canvasSize * 0.05}px Arial`;
                
                // 文字色を黒 (#000000) に設定
                ctx.fillStyle = "#000000"; 
                
                ctx.textBaseline = "middle";
                ctx.fillText(sector, canvasSize * 0.45, 0, canvasSize * 0.4); 
                ctx.restore();
            });
            ctx.restore(); 
        }

        function spinRoulette() {
            spinButton.disabled = true;
            resultP.textContent = "抽選中...";

            fetch('G-17_spin-roulette.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ transaction_id: transactionId })
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text) });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    const prizeIndex = data.prize_index;
                    const prizeName = data.prize_name;

                    let targetSectorCenter = (prizeIndex + 0.5) * sectorAngle;
                    let targetAngle = (2 * Math.PI) - targetSectorCenter + (1.5 * Math.PI); // 12時の位置補正
                    
                    const totalRotation = 10 * (2 * Math.PI) + targetAngle;
                    animateSpin(totalRotation, prizeName);

                } else {
                    resultP.textContent = `エラー: ${data.message}`;
                    spinButton.disabled = false;
                }
            })
            .catch(error => {
                resultP.textContent = `通信エラーが発生しました。`;
                spinButton.disabled = false;
                console.error('Fetch Error:', error);
            });
        }
        
        function animateSpin(targetAngle, prizeName) {
            const spinDuration = 3000;
            const startTime = performance.now();

            function animate(time) {
                const elapsed = time - startTime;
                if (elapsed < spinDuration) {
                    const t = elapsed / spinDuration;
                    const easedT = 1 - Math.pow(1 - t, 3);
                    angle = (targetAngle * easedT) % (2 * Math.PI);
                    
                    drawRoulette();
                    requestAnimationFrame(animate);
                } else {
                    angle = targetAngle % (2 * Math.PI);
                    drawRoulette(); 
                    
                    resultP.textContent = `おめでとうございます！ ${prizeName} クーポンをゲットしました！`;
                    spinButton.style.display = 'none'; 

                    const link = document.createElement('a');
                    link.href = 'G-25_coupon-list.php';
                    link.textContent = 'クーポン一覧ページへ移動';
                    link.className = 'coupon-list-link';
                    
                    resultP.after(link); 
                }
            }
            requestAnimationFrame(animate);
        }

        spinButton.addEventListener('click', spinRoulette);
        window.addEventListener('resize', setCanvasSize);
        setCanvasSize(); 
    });
    </script>
    <?php endif; ?>

</body>
</html>