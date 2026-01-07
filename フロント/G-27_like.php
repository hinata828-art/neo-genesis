<?php
// 1. データベース接続（例）
$pdo = new PDO('mysql:host=localhost;dbname=shop_db', 'user', 'pass');

// 2. ログイン中のユーザーID（仮に123とします）
$user_id = 123;

// 3. お気に入りテーブルと商品テーブルを結合して取得
$sql = "SELECT p.id, p.name, p.price, p.image_url 
        FROM likes AS l
        JOIN products AS p ON l.product_id = p.id
        WHERE l.user_id = :user_id
        ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$favorite_items = $stmt->fetchAll();
?>

<div class="favorite-container">
    <h2>お気に入り一覧</h2>
    <div class="product-grid">
        <?php foreach ($favorite_items as $item): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="商品画像">
            </div>
            <div class="product-info">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p class="price">¥<?= number_format($item['price']) ?></p>
                <div class="actions">
                    <button class="btn-buy">カートに入れる</button>
                    <button class="btn-remove">削除</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
/* CSS部分（フォーマットを整える） */
.favorite-container { max-width: 1000px; margin: 0 auto; padding: 20px; font-family: sans-serif; }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
.product-card { border: 1px solid #eee; border-radius: 8px; overflow: hidden; transition: 0.3s; }
.product-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.product-image img { width: 100%; height: 200px; object-fit: cover; }
.product-info { padding: 15px; }
.product-info h3 { font-size: 16px; margin: 0 0 10px; }
.price { color: #e60000; font-weight: bold; font-size: 18px; }
.actions { display: flex; flex-direction: column; gap: 8px; margin-top: 10px; }
.btn-buy { background: #ff9900; color: white; border: none; padding: 8px; border-radius: 4px; cursor: pointer; }
.btn-remove { background: none; border: 1px solid #ccc; color: #666; padding: 5px; border-radius: 4px; cursor: pointer; font-size: 12px; }
</style>