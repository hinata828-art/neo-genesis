<?php
session_start();
require '../common/db_connect.php'; // DB接続ファイルを利用

// 入力チェック
if (empty($_POST['email']) || empty($_POST['password'])) {
    header('Location: G-19_admin-login.php?error=1');
    exit();
}

$email = $_POST['email'];
$password = $_POST['password'];

try {
    // 管理者テーブルからメールを検索
    $sql = "SELECT * FROM admin WHERE admin_email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['admin_password'])) {

        // セッション格納（キー名は admin で区別）
        $_SESSION['admin'] = [
            'id' => $admin['admin_id'],
            'name' => $admin['admin_name']
        ];

        // 管理者専用トップへ
        header('Location: G-20_admin-dashboard.php');
        exit();
    } else {
        header('Location: G-19_admin-login.php?error=2');
        exit();
    }

} catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
}
?>
