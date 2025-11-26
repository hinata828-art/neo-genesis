<?php
// G-18_easter-egg-process.php
session_start();
// エラーをJSONとして受け取るため、画面表示はオフ
ini_set('display_errors', 0);
error_reporting(0);

require '../common/db_connect.php'; 

header('Content-Type: application/json');

try {
    // 1. ログインチェック
    $customer_id = $_SESSION['customer']['id'] ?? null;
    if ($customer_id === null) {
        echo json_encode(['status' => 'error', 'message' => 'ログインしていません。']);
        exit;
    }

    // 2. クーポンIDをランダム決定
    $coupon_ids = [2, 3, 4, 5, 6, 7];
    $won_coupon_id = $coupon_ids[array_rand($coupon_ids)];

    // 割引率を取得
    $stmt = $pdo->prepare("SELECT discount_rate FROM coupon WHERE coupon_id = ?");
    $stmt->execute([$won_coupon_id]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    $discount_rate = $coupon['discount_rate'];

    // 3. DBに保存 (トランザクションを削除し、シンプルなINSERT実行)
    // ★重要: 全カテゴリ対応のため applicable_category_id は NULL
    $sql_insert = "INSERT INTO customer_coupon 
                    (customer_id, coupon_id, acquired_at, used_at, applicable_category_id)
                   VALUES 
                    (:cid, :coupon_id, NOW(), NULL, NULL)";
                    
    $stmt_insert = $pdo->prepare($sql_insert);
    $result = $stmt_insert->execute([
        ':cid' => $customer_id, 
        ':coupon_id' => $won_coupon_id
    ]);

    if ($result) {
        // 保存に成功した場合、そのIDを取得
        $inserted_id = $pdo->lastInsertId();
        
        echo json_encode([
            'status' => 'success',
            'discount_rate' => $discount_rate,
            // ★ デバッグ用メッセージを追加
            'message' => "保存成功！ID: {$inserted_id} / 顧客: {$customer_id}"
        ]);
    } else {
        throw new Exception("保存処理に失敗しました。");
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'システムエラー: ' . $e->getMessage()]);
}
?>