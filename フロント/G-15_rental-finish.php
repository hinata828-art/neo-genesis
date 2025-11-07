<?php session_start(); ?>
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php // ヘッダーは通常 <body> 内で読み込みます ?>
<?php 
// db-connect.php で $pdo が定義されている
require '../common/db_connect.php'; 
?>
<?php
$delivery_days = '未定'; // 初期値

// ★不具合修正 1: セッションから「今完了した注文」のIDを取得
// (このセッション変数は、注文処理の最後に必ず保存してください)
if (isset($_SESSION['last_transaction_id'])) {
    $last_transaction_id = $_SESSION['last_transaction_id'];

    try {
        // ★不具合修正 2: DB接続を再定義せず、$pdo をそのまま使う
        // ★不具合修正 3: 注文ID (transaction_id) で絞り込む
        $sql = "SELECT rental_days FROM rental WHERE transaction_id = :tid ORDER BY rental_id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tid', $last_transaction_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $delivery_days = $result['rental_days'];
        }
        
        // 一度使ったセッションは削除する（リロード時に備える）
        unset($_SESSION['last_transaction_id']);

    } catch (PDOException $e) {
        $delivery_days = 'エラー';
        // エラー処理
    }
} else {
    // 直接このページに来た場合など
    $delivery_days = '（表示不可）';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>購入完了</title>
    <link rel="stylesheet" href="../css/G-15_rental-finish.css"> 
    <link rel="stylesheet" href="../css/header.css">
</head>
<body>
    
    <?php require '../common/header.php'; // ★ヘッダーは通常ここに配置します ?>

    <img src="../img/NishimuraOnline.png" alt="ニシムラOnline" class="logo-image">

    <div class="message-area">
        レンタルが完了しました！！！！
    </div>

    <div class="delivery-date">
        お届け日 : <span><?php echo htmlspecialchars($delivery_days); ?>日後</span>
    </div>

    <a href="../G-8_home.php" class="home-button">ホーム画面へ</a>
</body>
</html>