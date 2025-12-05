<?php
session_start();
require '../common/db_connect.php';

// --- product_id 取得 ---
if (!isset($_GET['product_id'])) {
    echo "商品IDが指定されていません。";
    exit();
}
// 画像のパスを決定
$productImagePath = htmlspecialchars($product['product_image']);
$imageUrl = '';

// 値が「http」で始まる場合はDB直接保存のURLと判断
if (strpos($productImagePath, 'http') === 0) {
    $imageUrl = $productImagePath;
} else if ($productImagePath) {
    // それ以外の場合はサーバーフォルダ保存のファイル名と判断し、パスを結合
    // G-26_product-register.phpでは画像が '../img/' に保存されているため、
    // G-23からの相対パスは '../img/' となります。
    $imageUrl = '../img/' . $productImagePath;
} else {
    // 画像データがない場合のデフォルト画像を設定する場合はここに記述
    // $imageUrl = 'path/to/default_image.png';
}

$product_id = intval($_GET['product_id']);

// --- スタッフID（ログイン中の管理者） ---
$staff_id = $_SESSION['staff']['id'];
$staff_name = $_SESSION['staff']['name'];

// --- 発注日（今日） ---
$order_date = date('Y-m-d');

// --- 入荷日（今日 + 乱数日 1〜7） ---
$random_days = rand(1, 7);
$arrival_date = date('Y-m-d', strtotime("+{$random_days} days"));

// --- 商品名の取得（画面表示用） ---
$sql = $pdo->prepare("SELECT * FROM product WHERE product_id = ?");
$sql->execute([$product_id]);
$product = $sql->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    echo "商品が見つかりません。";
    exit();
}

// --- 入荷登録処理 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity    = intval($_POST['quantity']);
    $cost_price  = intval($_POST['cost_price']);
    $note        = $_POST['note'];
    $staff_id    = $_POST['staff_id'];

    // INSERT（入荷履歴）
    $insert = $pdo->prepare("
        INSERT INTO stock_arrival 
        (product_id, order_date, arrival_date, quantity, cost_price, staff_id, note)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $insert->execute([
        $product_id,
        $order_date,
        $arrival_date,
        $quantity,
        $cost_price,
        $staff_id,
        $note
    ]);

    // 在庫更新
    $update = $pdo->prepare("
        UPDATE product 
        SET stock_quantity = stock_quantity + ?
        WHERE product_id = ?
    ");
    $update->execute([$quantity, $product_id]);

    echo "<script>
            alert('入荷処理が完了しました。');
            window.location.href = 'G-23_product-detail.php?product_id={$product_id}';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>入荷登録</title>
    <link rel="stylesheet" href="../css/G-24_product-arrival.css">
    <link rel="stylesheet" href="../css/staff_header.css">
</head>
<body>
  <?php require_once __DIR__ . '/../common/staff_header.php'; ?>

<h2 class="page-title">入荷登録（<?= htmlspecialchars($product['product_name']) ?>）</h2>

<form method="POST">
<div class="container">
    <!-- 左エリア：入力 -->
    <div>
        <label>商品名</label>
        <input type="text" value="<?= htmlspecialchars($product['product_name']) ?>" readonly class="narrow-input">

        <label>発注日（自動）</label>
        <input type="text" name="order_date" value="<?= $order_date ?>" readonly class="narrow-input">

        <label>入荷日（自動）</label>
        <input type="text" name="arrival_date" value="<?= $arrival_date ?>" readonly class="narrow-input">

        <label>担当者（自動）</label>
        <input type="text" value="<?= htmlspecialchars($staff_name) ?>" readonly class="narrow-input">
        <input type="hidden" name="staff_id" value="<?= $staff_id ?>">

        <label>入荷数量</label>
        <input type="number" name="quantity" min="1" required class="narrow-input">

        <label>仕入れ価格</label>
        <input type="number" name="cost_price" min="0" required class="narrow-input">

        <input type="hidden" name="staff_id" value="<?= $staff_id ?>">
    </div>

    <!-- 右エリア：商品画像＋備考 -->
    <div>
        <label>商品画像</label>
        <div class="product-image-box">
                <?php if ($imageUrl): ?>
                    <img src="<?= $imageUrl ?>" alt="商品画像">
                <?php else: ?>
                    <p>画像がありません</p>
                <?php endif; ?>
                </div>

        <label>備考</label>
        <textarea name="note" class="narrow-input"></textarea>
    </div>

    <!-- ボタン -->
    <div class="button-area">
        <button type="submit" class="btn">登録</button>
        <a class="btn-cancel" href="G-23_product-detail.php?product_id=<?= $product_id ?>">キャンセル</a>
    </div>
</div>
</form>

</body>
</html>
