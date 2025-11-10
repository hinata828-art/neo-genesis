<?php
// 1. セッションを開始 (1回だけにする)
session_start();

// 2. デバッグ（エラー表示）設定 (開発中のみ)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. 共通のデータベース接続ファイルを読み込む
require '../common/db_connect.php'; 

$delivery_days = '未定'; // 初期値

// 4. セッションにIDがあるか確認
if (isset($_SESSION['last_transaction_id'])) {
    $last_transaction_id = $_SESSION['last_transaction_id'];

    try {
        $sql = "SELECT rental_days FROM rental WHERE transaction_id = :tid ORDER BY rental_id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tid', $last_transaction_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $delivery_days = $result['rental_days'];
        }
        
        // 一度使ったセッションは削除する
        unset($_SESSION['last_transaction_id']);

    } catch (PDOException $e) {
        $delivery_days = 'エラー';
    }
} else {
    // セッションにIDがない（直接アクセスしたなど）
    $delivery_days = '（表示不可）';
}
// ... PHP処理の最後
// $last_transaction_id が確定した後

?>
<!DOCTYPE html>
<body>
    <div class="delivery-date">
        お届け日 : <span><?php echo htmlspecialchars($delivery_days); ?>日後</span>
    </div>

    <?php if (isset($last_transaction_id) && $delivery_days !== '（表示不可）' && $delivery_days !== 'エラー'): ?>
        <a href="../G-16_order-history.php?id=<?php echo htmlspecialchars($last_transaction_id); ?>" class="detail-button">注文詳細を見る</a>
    <?php endif; ?>

    <a href="../G-8_home.php" class="home-button">ホーム画面へ</a>
</body>
</html>