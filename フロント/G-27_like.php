<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
// 正しい接続ファイルを読み込みます
require '../common/db_connect.php'; 

// ログインチェック
if (isset($_SESSION['customer']['id'])) {
    $customer_id = $_SESSION['customer']['id'];
} else {
    // ログインしていない場合はログイン画面へ（ファイル名はプロジェクトに合わせてください）
    header('Location: G-1_customer-form.php');
    exit;
}

/**
 * $pdo という変数が db_connect.php で定義されている前提です。
 * もしエラーが出る場合は $pdo を $db などに変えてみてください。
 */
try {
    // あなたのDB構造（productテーブル）に合わせたSQL
    // お気に入りテーブル名は仮に 'likes' としていますが、実際のテーブル名に合わせてください
    $sql = "SELECT p.product_id, p.product_name, p.price, p.product_image 
            FROM like AS l
            JOIN product AS p ON l.product_id = p.product_id
            WHERE l.customer_id = :customer_id
            ORDER BY l.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['customer_id' => $customer_id]);
    $favorite_items = $stmt->fetchAll();
} catch (PDOException $e) {
    // SQLエラーなどを画面に表示
    die("エラーが発生しました: " . htmlspecialchars($e->getMessage()));
}
?>

<div class="favorite-container">
    <h2>お気に入り一覧</h2>
    <div class="product-grid">
        <?php foreach ($favorite_items as $item): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?= htmlspecialchars($item['product_image'] ?? '../img/no-image.png') ?>" alt="商品画像">
            </div>
            <div class="product-info">
                <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                <p class="price">¥<?= number_format((int)($item['price'] ?? 0)) ?></p>
                <div class="actions">
                    <button class="btn-buy">カートに入れる</button>
                    <button class="btn-remove">削除</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>