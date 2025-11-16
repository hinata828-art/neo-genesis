<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('canvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('d');
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
        
        // ★★★ 修正点 1: 矢印の位置調整を削除 (CSSで固定するため) ★★★
        // pointer.style.top = `${-canvasSize * 0.1}px`;
        // pointer.style.left = `calc(50% - 20px)`; 
        
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

        fetch('G-17_spin-roulette.php', {
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
                
                resultP.textContent = `おめでとうございます！ ${prizeName} クーポンをゲットしました！`;
                spinButton.style.display = 'none'; 

                // ★★★ 修正点 2: クーポン一覧へのリンクを表示 ★★★
                const link = document.createElement('a');
                link.href = 'G-25_coupon_list.php';
                link.textContent = 'クーポン一覧ページへ移動';
                link.className = 'coupon-list-link'; // CSSでスタイリング
                
                // resultP要素の後 (下) にリンクを追加
                resultP.after(link); 
            }
        }
        requestAnimationFrame(animate);
    }

    spinButton.addEventListener('click', spinRoulette);
    window.addEventListener('resize', setCanvasSize);
    setCanvasSize(); // 最初の描画
});
</script>