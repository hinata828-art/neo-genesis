<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require '../common/db_connect.php'; 

if (!isset($_SESSION['customer']['id'])) {
    header('Location: G-1_customer-form.php');
    exit;
}

$customer_id = $_SESSION['customer']['id'];

try {
    $sql = "SELECT p.product_id, p.product_name, p.price, p.product_image 
            FROM `like` AS l
            JOIN product AS p ON l.product_id = p.product_id
            WHERE l.user_id = :customer_id
            ORDER BY l.created_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['customer_id' => $customer_id]);
    $favorite_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("エラーが発生しました: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お気に入り一覧</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-27_like.css">
</head>
<body>

    <?php require __DIR__ . '/../common/header.php'; ?>

    <main class="favorite-container">
        <h2 class="page-title">お気に入り一覧</h2>

        <?php if (empty($favorite_items)): ?>
            <div id="emptyMessage" class="no-items">
                <p>お気に入りに登録された商品はありません。</p>
                <div class="home-link"><a href="G-8_home.php">買い物を続ける</a></div>
            </div>
        <?php else: ?>
            <div id="emptyMessage" class="no-items" style="display: none;">
                <p>お気に入りに登録された商品はありません。</p>
                <div class="home-link"><a href="G-8_home.php">買い物を続ける</a></div>
            </div>

            <div class="product-grid" id="productGrid">
                <?php foreach ($favorite_items as $item): ?>
                    <?php
                        $img = $item['product_image'] ?? '';
                        $imgSrc = (strpos($img, 'http') === 0) ? $img : '../img/' . $img;
                        if (empty($img)) $imgSrc = '../img/no-image.png';
                    ?>
                    
                    <div class="product-card" id="card-<?= $item['product_id'] ?>">
                        
                        <div class="image-area">
                            <a href="G-9_product-detail.php?id=<?= $item['product_id'] ?>" class="image-link">
                                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="商品画像">
                            </a>
                            
                            <button type="button" class="btn-favorite-overlay active" 
                                    onclick="removeFavorite(<?= $item['product_id'] ?>)">
                                ♥
                            </button>
                        </div>

                        <div class="product-info">
                            <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                            <p class="price">¥<?= number_format((int)$item['price']) ?></p>
                            
                            <div class="actions">
                                <a href="G-9_product-detail.php?id=<?= $item['product_id'] ?>" class="btn-buy">詳細を見る</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
    // 即時削除用スクリプト
    function removeFavorite(productId) {
        // 確認ダイアログなしで処理開始
        
        // APIへ送信 (G-9_favorite.php を再利用)
        fetch('G-9_favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            // ステータスが removed (削除) なら画面から消す
            if (data.status === 'removed') {
                const card = document.getElementById('card-' + productId);
                if (card) {
                    // ふわっと消えるアニメーション
                    card.style.transition = "all 0.4s ease";
                    card.style.opacity = "0";
                    card.style.transform = "scale(0.8)";
                    
                    setTimeout(() => {
                        card.remove();
                        checkEmpty(); // 空になったかチェック
                    }, 400);
                }
            } else {
                // 万が一削除済みだったりエラーの場合も、画面からは消しておく（UX優先）
                console.warn('API response:', data);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // 商品が全部なくなった時に「ありません」メッセージを出す関数
    function checkEmpty() {
        const grid = document.getElementById('productGrid');
        // gridの中にカードが残っているか確認
        if (grid && grid.children.length === 0) {
            document.getElementById('emptyMessage').style.display = 'block';
        }
    }
    </script>
</body>
</html>