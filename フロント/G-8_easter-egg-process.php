<?php
// G-18_easter-egg-process.php
// イースターエッグの判定とクーポン発行を行うサーバー側スクリプト

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php'; 

header('Content-Type: application/json');

// エラーを返すための共通関数
function sendError($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

try {
    // 1. 顧客IDのチェック
    $customer_id = $_SESSION['customer']['id'] ?? null;
    if ($customer_id === null) {
        sendError('ログイン情報が確認できません。');
    }

    // 2. 景品リストの取得
    // ルーレットと同様のクーポンID 2〜7 を取得
    $sql_prizes = "SELECT coupon_id, discount_rate FROM coupon 
                   WHERE coupon_id IN (2, 3, 4, 5, 6, 7)";
    $stmt_prizes = $pdo->prepare($sql_prizes);
    $stmt_prizes->execute();
    $prizes = $stmt_prizes->fetchAll(PDO::FETCH_ASSOC);

    if (count($prizes) < 6) {
        sendError('景品データが不足しています。');
    }

    // 3. 抽選（ランダムに1つ選ぶ）
    $won_prize = $prizes[array_rand($prizes)]; 
    $won_coupon_id = $won_prize['coupon_id'];
    $discount_rate = $won_prize['discount_rate'];

    // 4. トランザクション開始
    $pdo->beginTransaction();

    // 5. customer_coupon テーブルに「当選結果」を INSERT
    // ★ 修正点: 全カテゴリで使えるよう applicable_category_id は NULL で保存
    $sql_insert = "INSERT INTO customer_coupon 
                    (customer_id, coupon_id, acquired_at, used_at, applicable_category_id)
                   VALUES 
                    (:cid, :coupon_id, NOW(), NULL, NULL)"; 
                    
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':cid' => $customer_id, 
        ':coupon_id' => $won_coupon_id
    ]);
    
    // 6. コミット
    $pdo->commit();

    // 7. 成功結果をJSに返す
    echo json_encode([
        'status' => 'success',
        'discount_rate' => $discount_rate // 画面表示用に割引率を返す
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendError('サーバーエラー: ' . $e->getMessage());
}
?>