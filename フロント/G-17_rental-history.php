<?php
// -------------------------------------------------------------------
// エラーを強制表示する設定 (本番環境では消すべきですが、今は必須)
// -------------------------------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 全体を try-catch で囲み、致命的なエラーもキャッチする
try {
    // 1. セッション開始
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // 2. ファイルパスを絶対パスで指定 (読み込みミスの防止)
    $db_path = __DIR__ . '/../common/db_connect.php';
    $header_path = __DIR__ . '/../common/header.php';

    // ファイルが存在するかチェック
    if (!file_exists($db_path)) {
        throw new Exception("DB接続ファイルが見つかりません: " . $db_path);
    }
    require $db_path;

    // 3. データの初期化
    $transaction_id = 0;
    $show_roulette = false;
    $prizes_for_js = [];
    $error_message = '';

    // 4. ログイン & IDチェック
    $customer_id = $_SESSION['customer']['id'] ?? null;
    if ($customer_id === null) {
        throw new Exception('ログインしていません。<a href="G-1_login.php">ログイン画面へ</a>');
    }
    if (!isset($_GET['id'])) {
        throw new Exception('取引ID(id)がURLに指定されていません。');
    }
    $transaction_id = $_GET['id'];

    // 5. レンタル情報の確認
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
        throw new Exception('該当するレンタル履歴が見つかりません。(ID不一致)');
    }
    if ($rental_info['delivery_status'] !== '返却済み') {
        // デバッグ用に、ステータスが何になっているか表示
        throw new Exception('返却済みではありません。現在のステータス: ' . htmlspecialchars($rental_info['delivery_status']));
    }
    if ($rental_info['coupon_claimed'] == 1) {
        throw new Exception('このレンタルでは既にルーレットを回しています。');
    }

    // 6. ルーレット表示許可
    $show_roulette = true;
    
    // 7. 景品リスト取得
    $sql_prizes = "SELECT coupon_name FROM coupon 
                   WHERE coupon_id IN (2, 3, 4, 5, 6, 7)
                   ORDER BY coupon_id ASC";
    $stmt_prizes = $pdo->prepare($sql_prizes);
    $stmt_prizes->execute();
    $prizes_for_js = $stmt_prizes->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (count($prizes_for_js) < 6) {
        throw new Exception('景品データ(couponテーブル)が不足しています。');
    }

} catch (Throwable $e) {
    // PHP7以降の致命的エラー(Error)と例外(Exception)の両方をキャッチ
    $error_message = $e->getMessage();
    $show_roulette = false;
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
    <style>
        /* 万が一CSSが読み込めない場合の緊急スタイル */
        body { font-family: sans-serif; text-align: center; padding-top: 120px;}
        .error-box { color: red; background: #ffe6e6; padding: 20px; border: 1px solid red; margin: 20px auto; max-width: 600px; }
        #roulette { border: 2px solid #1f2937; border-radius: 50%; margin: 80px auto 50px auto; }
    </style>
</head>
<body>
    <?php 
    // ヘッダー読み込み
    if (file_exists(__DIR__ . '/../common/header.php')) {
        require __DIR__ . '/../common/header.php';
    } else {
        echo '<div style="background:#ccc;padding:10px;">(ヘッダー読み込み失敗)</div>';
    }
    ?>
    
    <div class="container">
        <header class="header">
            <a href="G-17_rental-history.php?id=<?php echo htmlspecialchars($transaction_id); ?>">
                <img src="../img/modoru.png" alt="戻る" class="back-link">
            </a>
            <h1 class="header-title">ルーレット</h1>
            <span class="header-dummy"></span>
        </header>

        <main class="main-content">
            <?php if (!empty($error_message)): ?>
                <div class="error-box">
                    <h3>エラーが発生しました</h3>
                    <p><?php echo $error_message; ?></p>
                </div>
                <a href="G-17_rental-history.php?id=<?php echo htmlspecialchars($transaction_id); ?>" class="btn-roulette-back" style="display:inline-block; padding:10px; background:#999; color:#fff; text-decoration:none;">履歴詳細に戻る</a>

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
        
        // PHPデータをJSONで受け取る
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
                canvasSize = 300; // フォールバック
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
                ctx.fillStyle = "#000000"; // 黒文字
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
                    // サーバーエラーの場合、テキストとしてエラーを受け取る
                    return response.text().then(text => { throw new Error(text) });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    const prizeIndex = data.prize_index;
                    const prizeName = data.prize_name;
                    let targetSectorCenter = (prizeIndex + 0.5) * sectorAngle;
                    let targetAngle = (2 * Math.PI) - targetSectorCenter + (1.5 * Math.PI);
                    const totalRotation = 10 * (2 * Math.PI) + targetAngle;
                    animateSpin(totalRotation, prizeName);
                } else {
                    resultP.textContent = `エラー: ${data.message}`;
                    spinButton.disabled = false;
                }
            })
            .catch(error => {
                resultP.textContent = `通信エラー: ${error.message}`;
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