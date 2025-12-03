<?php
session_start();
require '../common/db_connect.php'; // DB接続

// 入力チェック（未入力）
if (empty($_POST['staff_id']) || empty($_POST['password'])) {
    header('Location: G-19_admin-login.php?error=1');
    exit();
}

$staff_id = $_POST['staff_id'];
$password = $_POST['password'];

try {
    // 社員情報取得
    $sql = "SELECT * FROM staff WHERE staff_id = :staff_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':staff_id', $staff_id, PDO::PARAM_STR);
    $stmt->execute();
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    // パスワード照合（平文比較）
    if ($staff && $password === $staff['password']) {

        // セッションにスタッフ情報を保存
        $_SESSION['staff'] = [
            'id'   => $staff['staff_id'],
            'name' => $staff['staff_name']
        ];

        // ✔ ログイン成功 → 顧客管理画面へ
        header('Location: G-20_customer-management.php');
        exit();

    } else {
        // IDまたはPWが間違い
        header('Location: G-19_admin-login.php?error=2');
        exit();
    }

} catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
}
