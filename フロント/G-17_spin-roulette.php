<?php
if (isset($_GET['test'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'debug_ok', 'message' => 'PHPファイルは生きています']);
    exit;
}
// G-17_spin_roulette.php
// (ルーレットの抽選とDB保存を行う、裏方のファイル)

// 1. セッションとDB接続
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php';

// 応答はJSONで行う
header('Content-Type: application/json');

// エラーを返すための関数
function sendError($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

try {
    // 2. 顧客IDと、POSTされた取引IDを取得
    $customer_id = $_SESSION['customer']['id'] ?? null;
    if ($customer_id === null) {
        sendError('ログイン情報が確認できません。');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $transaction_id = $data['transaction_id'] ?? 0;
    if (empty($transaction_id)) {
        sendError('取引IDが送信されていません。');
    }

    // 3. DB処理をトランザクションで開始
    $pdo->beginTransaction();

    // 4. このレンタルが「抽選可能」か、DBをロックして確認
    // (product_id -> category_id も同時に取得)
    $sql_check = "SELECT 
                    r.coupon_claimed, 
                    p.category_id,
                    t.delivery_status
                  FROM rental AS r
                  JOIN product AS p ON r.product_id = p.product_id
                  JOIN transaction_table AS t ON r.transaction_id = t.transaction_id
                  WHERE r.transaction_id = :tid 
                    AND t.customer_id = :cid
                  LIMIT 1
                  FOR UPDATE"; // ★ 他の処理が同時に走らないようロック

    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':tid' => $transaction_id, ':cid' => $customer_id]);
    $rental_info = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$rental_info) {
        sendError('対象のレンタル履歴が見つかりません。');
    }
    if ($rental_info['delivery_status'] !== '返却済み') {
        sendError('このレンタルはまだ返却が完了していません。');
    }
    if ($rental_info['coupon_claimed'] == 1) {
        sendError('このレンタルでは既にルーレットを回しています。');
    }
    
    // このレンタルの「カテゴリID」 (例: 'C02')
    $rental_category_id = $rental_info['category_id'];

    // 5. 景品リストをDBから取得
    // ★ あなたがDB準備 (ステップ2) でINSERTした景品を取得
    // 5. 景品リストをDBから取得
    // ★ G-17.phpのJSが期待するリストを確実に取得するため、coupon_idで絞り込みます。
    $sql_prizes = "SELECT coupon_id, coupon_name, discount_rate FROM coupon 
                WHERE coupon_id IN (2, 3, 4, 5, 6, 7)
                ORDER BY discount_rate ASC";
    
    $stmt_prizes = $pdo->prepare($sql_prizes);
    $stmt_prizes->execute();
    $prizes = $stmt_prizes->fetchAll(PDO::FETCH_ASSOC);

    if (count($prizes) < 6) {
        sendError('景品がDBに正しく登録されていません (coupon_id 2〜7が必要です)。');
    }

    // 6. 抽選 (PHPの array_rand で安全に実行)
    // $prizes 配列 (0〜5) から、ランダムで1つの添字(index)を選ぶ
    $prize_index = array_rand($prizes);
    $won_prize = $prizes[$prize_index]; // 当選した景品
    $won_coupon_id = $won_prize['coupon_id']; // (例: 7)

    // 7. (A) customer_coupon テーブルに「当選結果」を INSERT
    // ★「どのカテゴリで使えるか」('applicable_category_id') も保存
    $sql_insert = "INSERT INTO customer_coupon 
                    (customer_id, coupon_id, acquired_at, used_at, applicable_category_id)
                   VALUES 
                    (:cid, :coupon_id, NOW(), NULL, :cat_id)";
                   
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([
        ':cid' => $customer_id, 
        ':coupon_id' => $won_coupon_id,
        ':cat_id' => $rental_category_id // ★ (例: 'C02')
    ]);
    
    // 7. (B) rental テーブルを「抽選済み(1)」に UPDATE
    $sql_update = "UPDATE rental SET coupon_claimed = 1 WHERE transaction_id = :tid";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([':tid' => $transaction_id]);

    // 8. すべて成功したらコミット
    $pdo->commit();

    // 9. 成功結果をJSに返す
    echo json_encode([
        'status' => 'success',
        'message' => '抽選完了',
        'prize_index' => $prize_index, // ★ JSがアニメーション停止に使う「配列の添字」
        'prize_name' => $won_prize['coupon_name'] // ★ 画面表示用の景品名
    ]);
    exit;

} catch (Exception $e) {
    // 10. エラーが発生したらロールバック
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendError('サーバーエラー: ' . $e->getMessage());
}
?>