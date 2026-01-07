<?php
session_start();

// セッション変数を削除
$_SESSION = [];

// セッションクッキーも削除（あれば）
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// セッション破棄
session_destroy();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/G-7_withdrawal-complete.css">
    <link rel="stylesheet" href="../css/header.css">
    <title>ログアウト完了</title>
</head>
<body>
    <?php require __DIR__ . '/../common/header.php'; ?>

    <img src="../img/NishimuraOnline.png" alt="ニシムラOnlineロゴ" class="logo">

    <div class="message">
        ログアウトが完了しました！<br>
        ご利用ありがとうございました！
    </div>

    <a href="G-1_customer-form.php" class="btn">ログイン画面へ</a>
</body>
</html>
