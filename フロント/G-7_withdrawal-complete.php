<?php
// ====== ログアウト処理（絶対に最初に書く） ======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// キャッシュ無効化（戻るボタン対策）
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// セッション変数を削除
$_SESSION = [];

// セッションクッキー削除
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    // path違いにも対応（2通り削除）
    setcookie(session_name(), '', time() - 42000,
        '/',
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );

    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// RememberMe 系も削除（もし使ってた場合用）
$rememberCookies = ['user_id','login_token','remember_token','auth_token'];
foreach ($rememberCookies as $c) {
    setcookie($c, '', time() - 3600, '/');
    setcookie($c, '', time() - 3600);
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

    <a href="G-8_home.php" class="btn">ログイン画面へ</a>
</body>
</html>
