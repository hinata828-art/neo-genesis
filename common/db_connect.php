<?php
const SERVER = 'mysql324.phy.lolipop.lan';
const DBNAME = 'LAA1607504-nishimura';
const USER = 'LAA1607504';
const PASS = 'nishimura12345';

try {
    $pdo = new PDO(
        'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8',
        USER,
        PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // 本番環境ではエラーを画面に出さないのが定石ですが、デバッグ中は表示します
    echo 'DB接続エラー: ' . $e->getMessage();
    exit;
}
// 終了タグ ?> は意図的に省略します（トラブル防止のため）