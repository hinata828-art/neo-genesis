<?php
session_start();
require '../common/db_connect.php';

// 取引ID確認
if (!isset($_GET['id'])) {
    exit('取引IDが指定されていません。');
}

$transaction_id = $_GET['id'];

try {

    // レンタル情報取得
    $sql = "SELECT rental_end, delivery_status 
            FROM rental 
            JOIN transaction_table 
            ON rental.transaction_id = transaction_table.transaction_id
            WHERE rental.transaction_id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        exit('レンタル情報が見つかりません。');
    }

    // 返却済み or キャンセルなら延長不可
    if ($rental['delivery_status'] === '返却済み' || 
        $rental['delivery_status'] === 'キャンセル済み') {
        exit('この取引は延長できません。');
    }

    // ★★ ここで延長日数を設定 ★★
    $new_end = date('Y-m-d', strtotime($rental['rental_end'] . ' +1 day'));

    // DB更新
    $update = "UPDATE rental 
               SET rental_end = :end 
               WHERE transaction_id = :id";

    $stmt = $pdo->prepare($update);
    $stmt->bindValue(':end', $new_end);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();

    // 戻る
    header("Location: G-17_rental-history-detail.php?id=" . $transaction_id);
    exit();

} catch (Exception $e) {
    echo 'エラー: ' . $e->getMessage();
}
