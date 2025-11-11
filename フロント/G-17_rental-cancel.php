<?php
// G-17_rental-cancel.php

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

    // 3. キャンセル対象のレンタルIDをURLから取得
    if (!isset($_GET['id'])) {
        throw new Exception('レンタルIDが指定されていません。');
    }
    // G-16の transaction_id から rental_id に変更
    $rental_id = $_GET['id'];

    // 4. 念のため、本当にこの顧客のレンタルか確認 (テーブル名を rental_table に変更)
    $sql_check = "SELECT customer_id, return_status FROM rental_table WHERE rental_id = :rid";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->bindValue(':rid', $rental_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$result || $result['customer_id'] != $customer_id) {
        // 他人のレンタルIDを指定されたか、レンタルが存在しない
        throw new Exception('このレンタルをキャンセルする権限がありません。');
    }

    // ★★★ G-16のロジックに基づく注意点 ★★★
    // このまま削除すると、「レンタル中」や「返却済み」の履歴も消えてしまいます。
    // (改善案は後述します)

    // 5. 削除処理（トランザクション開始）
    // 関連するテーブル（detailとtable）を両方消すため、処理をまとめる
    $pdo->beginTransaction();

    // 5a. 詳細テーブル (rental_detail) から関連レコードを削除
    $sql_delete_detail = "DELETE FROM rental_detail WHERE rental_id = :rid";
    $stmt_delete_detail = $pdo->prepare($sql_delete_detail);
    $stmt_delete_detail->bindValue(':rid', $rental_id, PDO::PARAM_INT);
    $stmt_delete_detail->execute();

    // 5b. 本体テーブル (rental_table) から削除
    $sql_delete_main = "DELETE FROM rental_table WHERE rental_id = :rid";
    $stmt_delete_main = $pdo->prepare($sql_delete_main);
    $stmt_delete_main->bindValue(':rid', $rental_id, PDO::PARAM_INT);
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