<?php
session_start();
require '../common/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['customer']['id']) || !isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$user_id = $_SESSION['customer']['id'];
$product_id = $_POST['product_id'];

// 既に登録されているか確認
$check = $pdo->prepare("SELECT * FROM `like` WHERE user_id = ? AND product_id = ?");
$check->execute([$user_id, $product_id]);

if ($check->fetch()) {
    // 登録済みなら削除
    $del = $pdo->prepare("DELETE FROM `like` WHERE user_id = ? AND product_id = ?");
    $del->execute([$user_id, $product_id]);
    echo json_encode(['status' => 'removed']);
} else {
    // 未登録なら挿入
    $ins = $pdo->prepare("INSERT INTO `like` (user_id, product_id, created_time) VALUES (?, ?, NOW())");
    $ins->execute([$user_id, $product_id]);
    echo json_encode(['status' => 'added']);
}