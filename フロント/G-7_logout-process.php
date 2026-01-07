<?php
session_start();

// 1. セッション変数を全て解除する
$_SESSION = array();

// 2. セッションクッキーを削除する (これを追加すると確実です)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// 3. セッションを最終的に破壊する
session_destroy();

// 4. ホーム画面へリダイレクト
header('Location: G-8_home.php');
exit;
?>