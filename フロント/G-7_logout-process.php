<?php
session_start();
$_SESSION = array(); // セッション変数を全てクリア
session_destroy();  // セッションを破棄
header('Location: G-8_home.php'); // ホーム画面へリダイレクト
exit;
?>