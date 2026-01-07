<?php
// ヘッダーなどでセッションを使う場合は最初に記述
session_start();
// ※ここで共通ヘッダーなどを読み込む
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>退会確認 | ニシムラOnline</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-6_withdrawal-check.css">
</head>
<body>
    <?php require __DIR__ . '/../common/header.php'; ?>
    
    <div class="container">
        <img src="../img/NishimuraOnline.png" alt="ニシムラOnline" class="logo">

        <main class="confirm-box">
            <div class="alert-icon">⚠️</div>
            <p class="confirm-text">ログアウトしますか？</p>
            <div class="button-group">
                <form action="G-7_withdrawal-complete.php" method="post">
                    <button type="submit" class="btn btn-red">はい</button>
                </form>
                <form action="G-8_home.php" method="get">
                    <button type="submit" class="btn btn-yellow">いいえ</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>