<?php
// --- お知らせ用ロジックの追加 ---

// 1. 商品テーブルからランダムに3〜5件取得
$info_sql = "SELECT product_name, product_image FROM product ORDER BY RAND() LIMIT 5";
$info_stmt = $pdo->query($info_sql);
$random_products = $info_stmt->fetchAll();

// 2. メッセージのパターン定義
$action_patterns = ["を購入しました", "をレンタルしました", "が到着しました"];
$coupon_rates = [5, 10, 15, 20]; // ダミーのクーポン%

$notifications = [];

// 商品ベースの通知を作成
foreach ($random_products as $p) {
    $action = $action_patterns[array_rand($action_patterns)];
    $notifications[] = [
        'img' => $p['product_image'],
        'text' => "「{$p['product_name']}」{$action}",
        'type' => 'product'
    ];
}

// クーポン取得の通知を1つ混ぜる（ランダム演出）
$notifications[] = [
    'img' => '../img/coupon.png', // 既存のクーポン画像を利用
    'text' => $coupon_rates[array_rand($coupon_rates)] . "%のクーポンを取得しました！",
    'type' => 'coupon'
];

// 表示順もシャッフル
shuffle($notifications);
?>

<main class="notification-container">
    <h2>お知らせ</h2>
    
    <div class="notification-list">
        <?php foreach ($notifications as $note): ?>
            <div class="notification-item" style="display: flex; align-items: center; border-bottom: 1px solid #eee; padding: 10px;">
                <img src="<?= htmlspecialchars($note['img'] ?? '../img/no-image.png') ?>" 
                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px;">
                
                <div class="notification-content">
                    <p style="margin: 0; font-size: 0.95rem; color: #333;">
                        <?= htmlspecialchars($note['text']) ?>
                    </p>
                    <span style="font-size: 0.8rem; color: #999;">たった今</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="home-button">
        <a href="G-8_home.php">ホーム画面へ</a>
    </div>
</main>