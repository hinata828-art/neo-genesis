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
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit;
}
?>