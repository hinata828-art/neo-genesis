<?php
session_start();
require '../common/db_connect.php';

// POSTチェック
if (!isset($_POST['transaction_id'], $_POST['extend_days'])) {
    exit('不正なアクセスです。');
}

$transaction_id = (int)$_POST['transaction_id'];
$extend_days    = (int)$_POST['extend_days'];

try {
    // 延長可能日数（ユーザーが選べるものだけ）
    $allowed_days = [3, 7, 30, 90, 365];
    if (!in_array($extend_days, $allowed_days, true)) {
        exit('無効な延長日数です。');
    }

    // レンタル情報取得
    $sql = "SELECT r.rental_end, t.delivery_status
            FROM rental r
            JOIN transaction_table t
              ON r.transaction_id = t.transaction_id
            WHERE r.transaction_id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        exit('レンタル情報が見つかりません。');
    }

    // 返却済み・キャンセル済みは延長不可
    if ($rental['delivery_status'] === '返却済み' ||
        $rental['delivery_status'] === 'キャンセル済み') {
        exit('この取引は延長できません。');
    }

    // 新しい返却日を計算
    $new_end = date(
        'Y-m-d',
        strtotime($rental['rental_end'] . " +{$extend_days} day")
    );

    // DB更新
    $update = "UPDATE rental
               SET rental_end = :end
               WHERE transaction_id = :id";

    $stmt = $pdo->prepare($update);
    $stmt->bindValue(':end', $new_end, PDO::PARAM_STR);
    $stmt->bindValue(':id', $transaction_id, PDO::PARAM_INT);
    $stmt->execute();

    // 詳細画面へ戻る
    header("Location: G-17_rental-history-detail.php?id=" . $transaction_id);
    exit();

} catch (Exception $e) {
    echo 'エラー: ' . htmlspecialchars($e->getMessage());
}
