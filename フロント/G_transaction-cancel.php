<?php
// G_transaction-cancel.php
// (G-16 と G-17 の両方の処理をこれ1つで行う)

// 1. セッションとDB接続
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../common/db_connect.php';

try {
    // 2. ログインしているか確認
    if (!isset($_SESSION['customer']['id'])) {
        throw new Exception('ログインしていません。');
    }
    $customer_id = $_SESSION['customer']['id'];

    // 3. キャンセル対象の「取引ID」をURLから取得
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];

    // 4. 本当にキャンセル「可能」か、ステータスを確認
    $sql_check = "SELECT customer_id, delivery_status FROM transaction_table WHERE transaction_id = :tid";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindValue(':tid', $transaction_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception('該当する取引が見つかりません。');
    }
    if ($result['customer_id'] != $customer_id) {
        throw new Exception('この取引をキャンセルする権限がありません。');
    }
    
    // ★★★ 最も重要なチェック ★★★
    // 「注文受付」以外のステータス（例: '配達完了', 'キャンセル済み'）なら、処理を止める
    if ($result['delivery_status'] != '注文受付') {
        throw new Exception('この取引はすでに出荷準備中、またはキャンセル・完了済みのため、キャンセルできません。');
    }

    // 5. 【論理削除】
    // DELETE (物理削除) ではなく、UPDATE (ステータス更新) を行う
    // これなら「購入」か「レンタル」かを気にする必要がない
    $sql_update = "UPDATE transaction_table 
                   SET delivery_status = 'キャンセル済み' 
                   WHERE transaction_id = :tid AND customer_id = :cid AND delivery_status = '注文受付'";

    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindValue(':tid', $transaction_id, PDO::PARAM_INT);
    $stmt_update->bindValue(':cid', $customer_id, PDO::PARAM_INT);
    $stmt_update->execute();

    // (もし在庫管理をしているなら、ここで在庫を戻す処理も追加)

    // 6. 処理が成功したら、マイページ (G-4) に移動
    header('Location: G-4_member-information.php');
    exit;

} catch (Exception $e) {
    // 7. エラーが発生した場合
    
    // (UPDATE文だけなのでトランザクションは不要だが、エラーハンドリングはしっかり行う)
    
    // エラーメッセージを表示
    echo "エラーが発生しました: " . htmlspecialchars($e.getMessage());
    echo '<br><a href="G-4_member-information.php">マイページに戻る</a>';
    exit;
}
?>