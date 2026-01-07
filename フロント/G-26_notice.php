<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../common/db_connect.php'; // ここで $pdo が定義されることを前提とします

if (isset($_SESSION['customer']['id'])) {
    $customer_id = $_SESSION['customer']['id'];
} else {
    // ログインしていない場合のリダイレクト
    header('Location: G-1_customer-form.php');
    exit;
}

// --- お知らせ用ロジック（DB接続後に実行） ---
$notifications = [];

try {
    // 1. 商品テーブルからランダムに5件取得
    // ※ $pdo が定義されていないエラーが出る場合は、db_connect.php内の変数名（$dbなど）に合わせてください
    $info_sql = "SELECT product_name, product_image FROM product ORDER BY RAND() LIMIT 5";
    $info_stmt = $pdo->query($info_sql);
    $random_products = $info_stmt->fetchAll();

    // 2. メッセージのパターン定義
    $action_patterns = ["を購入しました", "をレンタルしました", "が到着しました"];
    $coupon_rates = [5, 10, 15, 20];

    // 商品ベースの通知を作成
    foreach ($random_products as $p) {
        $action = $action_patterns[array_rand($action_patterns)];
        $notifications[] = [
            'img' => $p['product_image'],
            'text' => "「{$p['product_name']}」{$action}",
            'type' => 'product'
        ];
    }

    // クーポン通知を1つ混ぜる
    $notifications[] = [
        'img' => '../img/coupon.png',
        'text' => $coupon_rates[array_rand($coupon_rates)] . "%のクーポンを取得しました！",
        'type' => 'coupon'
    ];

    // 表示順をシャッフル
    shuffle($notifications);

} catch (PDOException $e) {
    // エラーが出た場合に画面で確認できるようにする
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お知らせ</title>
    <link rel="stylesheet" href="../css/header.css">
    <style>
        /* 簡単なスタイル調整 */
        .notification-container { max-width: 600px; margin: 20px auto; padding: 0 15px; }
        .notification-item { display: flex; align-items: center; border-bottom: 1px solid #eee; padding: 15px 0; }
        .notification-img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px; background: #f0f0f0; }
        .home-button { margin-top: 30px; text-align: center; }
        .home-button a { text-decoration: none; color: #007bff; border: 1px solid #007bff; padding: 10px 20px; border-radius: 5px; }
    </style>
</head>
<body>

    <?php require __DIR__ . '/../common/header.php'; ?>

    <main class="notification-container">
        <h2>お知らせ</h2>
        
        <div class="notification-list">
            <?php if (empty($notifications)): ?>
                <p>新しいお知らせはありません。</p>
            <?php else: ?>
                <?php foreach ($notifications as $note): ?>
                    <div class="notification-item">
                        <img src="<?= htmlspecialchars($note['img'] ?? '../img/no-image.png') ?>" 
                             alt="画像" class="notification-img">
                        
                        <div class="notification-content">
                            <p style="margin: 0; font-size: 0.95rem; color: #333;">
                                <?= htmlspecialchars($note['text']) ?>
                            </p>
                            <span style="font-size: 0.8rem; color: #999;">たった今</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="home-button">
            <a href="G-8_home.php">ホーム画面へ</a>
        </div>
    </main>

</body>
</html>