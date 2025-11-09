<?php
// 1. セッションとDB接続
session_start();
require '../common/db_connect.php';

// 2. ログイン状態とリクエストメソッドの確認
if (!isset($_SESSION['customer']['id'])) {
    die("エラー: ログインしていません。");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("エラー: 不正なアクセスです。");
}

// 3. POSTデータをすべて取得
$customer_id    = $_SESSION['customer']['id'];
$customer_name  = $_POST['customer_name'] ?? '';
$postal_code    = $_POST['postal_code'] ?? '';
$prefecture     = $_POST['prefecture'] ?? '';
$city           = $_POST['city'] ?? '';
$address_line   = $_POST['address_line'] ?? '';
$phone_number   = $_POST['phone_number'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$birth_date     = $_POST['birth_date'] ?? null;

// 生年月日が空文字で送信された場合は、DBに NULL を保存する
if (empty($birth_date)) {
    $birth_date = null;
}

// 4. データベース更新 (トランザクション処理)
try {
    $pdo->beginTransaction();

    // SQL 1: customer テーブルを更新
    $sql_customer = "UPDATE customer SET 
                        customer_name = :customer_name,
                        phone_number = :phone_number,
                        payment_method = :payment_method,
                        birth_date = :birth_date
                     WHERE customer_id = :customer_id";
    
    $stmt_customer = $pdo->prepare($sql_customer);
    $stmt_customer->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);
    $stmt_customer->bindValue(':phone_number', $phone_number, PDO::PARAM_STR);
    $stmt_customer->bindValue(':payment_method', $payment_method, PDO::PARAM_STR);
    $stmt_customer->bindValue(':birth_date', $birth_date, PDO::PARAM_STR); // DATE型でもPARAM_STRでOK
    $stmt_customer->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt_customer->execute();

    // SQL 2: address テーブルを更新 (または新規挿入)
    // REPLACE INTO は、customer_idをキーとして...
    // 1. 既存の行があれば「削除」→「挿入」（=実質更新）
    // 2. 既存の行がなければ「挿入」（=新規登録）
    // ...を行うSQLです。
    $sql_address = "REPLACE INTO address 
                        (customer_id, postal_code, prefecture, city, address_line)
                    VALUES 
                        (:customer_id, :postal_code, :prefecture, :city, :address_line)";
    
    $stmt_address = $pdo->prepare($sql_address);
    $stmt_address->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt_address->bindValue(':postal_code', $postal_code, PDO::PARAM_STR);
    $stmt_address->bindValue(':prefecture', $prefecture, PDO::PARAM_STR);
    $stmt_address->bindValue(':city', $city, PDO::PARAM_STR);
    $stmt_address->bindValue(':address_line', $address_line, PDO::PARAM_STR);
    $stmt_address->execute();

    // 5. 両方のSQLが成功したら、変更を確定 (コミット)
    $pdo->commit();

    // 6. 処理が完了したら、マイページ (G-4) に自動で戻る (リダイレクト)
    header('Location: G-4_member-information.php');
    exit;

} catch (Exception $e) {
    // 7. エラーが発生したら、すべての変更を取り消し (ロールバック)
    $pdo->rollBack();
    echo "データベースの更新に失敗しました: " . $e->getMessage();
}
?>