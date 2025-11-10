<?php
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

    // 3. キャンセル対象の取引IDをURLから取得
    if (!isset($_GET['id'])) {
        throw new Exception('取引IDが指定されていません。');
    }
    $transaction_id = $_GET['id'];

    // 4. 念のため、本当にこの顧客の注文か確認
    $sql_check = "SELECT customer_id FROM transaction_table WHERE transaction_id = :tid";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindValue(':tid', $transaction_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$result || $result['customer_id'] != $customer_id) {
        // 他人の注文IDを指定されたか、注文が存在しない
        throw new Exception('この注文をキャンセルする権限がありません。');
    }

    // 5. 削除処理（トランザクション開始）
    // 関連するテーブル（detailとtable）を両方消すため、処理をまとめる
    $pdo->beginTransaction();

    // 5a. 詳細テーブル (transaction_detail) から関連レコードを削除
    $sql_delete_detail = "DELETE FROM transaction_detail WHERE transaction_id = :tid";
    $stmt_delete_detail = $pdo->prepare($sql_delete_detail);
    $stmt_delete_detail->bindValue(':tid', $transaction_id, PDO::PARAM_INT);
    $stmt_delete_detail->execute();

    // 5b. 本体テーブル (transaction_table) から削除
    $sql_delete_main = "DELETE FROM transaction_table WHERE transaction_id = :tid";
    $stmt_delete_main = $pdo->prepare($sql_delete_main);
    $stmt_delete_main->bindValue(':tid', $transaction_id, PDO::PARAM_INT);
    $stmt_delete_main->execute();

    // 5c. 処理を確定
    $pdo->commit();

    // 6. 処理が成功したら、マイページ (G-4) に移動
    header('Location: G-4_member-information.php');
    exit;

} catch (Exception $e) {
    // 7. エラーが発生した場合
    
    // 処理を元に戻す
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // エラーメッセージを表示
    echo "エラーが発生しました: " . htmlspecialchars($e->getMessage());
    echo '<br><a href="G-4_member-information.php">マイページに戻る</a>';
    exit;
}
?>