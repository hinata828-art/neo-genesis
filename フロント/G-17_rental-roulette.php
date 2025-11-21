<?php
// G-17_rental-roulette.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. DB接続ファイルが致命的なエラーの原因ではないかを確認
require '../common/db_connect.php'; 
echo "DB接続成功"; // ★★★ この行は、DB接続成功の確認が取れたら削除してください ★★★

// 2. データの初期化
$transaction_id = 0;
$show_roulette = false; // ルーレットを表示するか
$prizes_for_js = [];    // ルーレットの景品リスト (JS用)
$error_message = '';

try {
    // ★★★ ここからコメントアウトを解除し、ロジックチェック部分を有効化 ★★★
    // 3. 顧客IDと取引IDを取得
    $customer_id = $_SESSION['customer']['id'] ?? null;
    if ($customer_id === null) {
        throw new Exception('ログイン情報が確認できません。');
    }
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];

    // ★★★ 4. DBクエリ以降は、まだコメントアウトを維持 ★★★
    /*
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
    // ... (景品取得クエリ) ...
    $sql_prizes = "SELECT coupon_name FROM coupon 
                   WHERE coupon_id IN (2, 3, 4, 5, 6, 7)
                   ORDER BY coupon_id ASC";
    
    $stmt_prizes = $pdo->prepare($sql_prizes);
    $stmt_prizes->execute();
    $prizes_for_js = $stmt_prizes->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (count($prizes_for_js) < 6) {
        throw new Exception('景品がDBに正しく登録されていません (ID 2-7が必要です)。');
    }
    */

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
                            <?php endif; ?>

        </main>
    </div> 
    
        <?php if ($show_roulette && !empty($prizes_for_js)): ?>
    /* ... JavaScript code ... */
    <?php endif; ?>

</body>
</html>