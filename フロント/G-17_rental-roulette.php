<?php
// G-17_rental-roulette.php
// 1. セッションとDB接続
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php'; 

// 2. データの初期化
$transaction_id = 0;
$show_roulette = false; // ルーレットを表示するか
$prizes_for_js = [];    // ルーレットの景品リスト (JS用)
$error_message = '';

try {
    // 3. 顧客IDと取引IDを取得
    $customer_id = $_SESSION['customer']['id'] ?? null;
    if ($customer_id === null) {
        throw new Exception('ログイン情報が確認できません。');
    }
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];

    // 4. このレンタルが「抽選可能」か、DBを確認
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

    // 5. すべてOKなら、ルーレットを表示
    $show_roulette = true;
    
    // 6. 景品リストをDBから取得 (ID 2〜7)
    $sql_prizes = "SELECT coupon_name FROM coupon 
                   WHERE coupon_id IN (2, 3, 4, 5, 6, 7)
                   ORDER BY discount_rate ASC";
    
    $stmt_prizes = $pdo->prepare($sql_prizes);
    $stmt_prizes->execute();
    $prizes_for_js = $stmt_prizes->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (count($prizes_for_js) < 6) {
        throw new Exception('景品がDBに正しく登録されていません (ID 2-7が必要です)。');
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// G-16/G-4 と同じステータス色分け関数
function getStatusClass($status) {
    if ($status == 'キャンセル済み') return 'status-cancelled';
    if ($status == '配達完了' || $status == '返却済み') return 'status-delivered';
    return 'status-processing'; 
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
    <?php require '../common/header.php'; // ヘッダーを読み込む ?>
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
                <a href="G-17_rental-history.php?id=<?php echo htmlspecialchars($transaction_id); ?>" class="btn-roulette-back">履歴詳細に戻る</a>

            <?php elseif ($show_roulette && !empty($prizes_for_js)): ?>
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

        </main>
    </div> 
    
    <?php if ($show_roulette && !empty($prizes_for_js)): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('canvas');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const pointer = document.getElementById('pointer');
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
            canvasSize = rouletteContainer.clientWidth * 0.8;
            if (canvasSize < 200) canvasSize = 200;
            if (canvasSize > 320) canvasSize = 320;
            
            canvas.width = canvasSize;
            canvas.height = canvasSize;
            
            pointer.style.top = `${-canvasSize * 0.1}px`;
            pointer.style.left = `calc(50% - 20px)`; 
            
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
                ctx.fillStyle = colors[index % colors.length];
                ctx.fill();
                ctx.closePath();

                ctx.save();
                ctx.rotate((index + 0.5) * sectorAngle);
                ctx.textAlign = "right";
                ctx.font = `bold ${canvasSize * 0.05}px Arial`;
                ctx.fillStyle = "#FFFFFF";
                ctx.textBaseline = "middle";
                ctx.fillText(sector, canvasSize * 0.45, 0, canvasSize * 0.4); 
                ctx.restore();
            });
            ctx.restore(); 
        }

        function spinRoulette() {
            spinButton.disabled = true;
            resultP.textContent = "抽選中...";

            fetch('G-17_spin_roulette.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ transaction_id: transactionId })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    const prizeIndex = data.prize_index;
                    const prizeName = data.prize_name;

                    let targetSectorCenter = (prizeIndex + 0.5) * sectorAngle;
                    let targetAngle = (2 * Math.PI) - targetSectorCenter + (Math.PI / 2);
                    
                    const totalRotation = 10 * (2 * Math.PI) + targetAngle;
                    animateSpin(totalRotation, prizeName);

                } else {
                    resultP.textContent = `エラー: ${data.message}`;
                    spinButton.disabled = false;
                }
            })
            .catch(error => {
                resultP.textContent = `エラー: ${error.message || '通信に失敗しました。'}`;
                spinButton.disabled = false;
                console.error('通信エラー:', error);
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
                    
                    resultP.textContent = `おめでとうございます！ ${prizeName} をゲットしました！`;
                    spinButton.style.display = 'none'; 
                }
            }
            requestAnimationFrame(animate);
        }

        spinButton.addEventListener('click', spinRoulette);
        window.addEventListener('resize', setCanvasSize);
        setCanvasSize(); // 最初の描画
    });
    </script>
    <?php endif; ?>

</body>
</html>