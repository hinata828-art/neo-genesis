<?php
session_start();
require '../common/db_connect.php'; // DB接続

// 入力チェック
if (empty($_POST['staff_id']) || empty($_POST['password'])) {
    header('Location: G-19_admin-login.php?error=1');
    exit();
}

$staff_id = $_POST['staff_id'];
$password = $_POST['password'];

try {
    // staff テーブルから社員IDを検索
    $sql = "SELECT * FROM staff WHERE staff_id = :staff_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':staff_id', $staff_id, PDO::PARAM_STR);
    $stmt->execute();
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    // パスワード（平文）の照合
    if ($staff && $password === $staff['password']) {

        // セッション格納
        $_SESSION['staff'] = [
            'id'   => $staff['staff_id'],
            'name' => $staff['staff_name']
        ];

        // 社員用ダッシュボードへ
        header('Location: G-20_customer-management.php');
        exit();
    } else {
        header('Location: G-19_admin-login.php?error=2');
        exit();
    }

} catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
}
?>
