<?php
// --- 必要ファイル読み込み ---
require '../common/db_connect.php';
session_start();

// --- product_id 取得 ---
if (!isset($_GET['product_id'])) {
    echo "商品IDが指定されていません。";
    exit();
}
$product_id = intval($_GET['product_id']);

// --- スタッフID（ログイン中の管理者） ---
$staff_id = $_SESSION['staff_id'];

// --- 発注日（今日） ---
$order_date = date('Y-m-d');

// --- 入荷日（今日 + 乱数日 1〜7） ---
$random_days = rand(1, 7);
$arrival_date = date('Y-m-d', strtotime("+{$random_days} days"));

// --- 商品名の取得（画面表示用） ---
$sql = $pdo->prepare("SELECT product_name FROM product WHERE product_id = ?");
$sql->execute([$product_id]);
$product = $sql->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    echo "商品が見つかりません。";
    exit();
}

// --- 入荷登録処理 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $quantity = intval($_POST['quantity']);
    $cost_price = intval($_POST['cost_price']);
    $note = $_POST['note'];
    $staff_id = $_POST['staff_id'];

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

<h2>入荷登録（<?php echo htmlspecialchars($product['product_name']); ?>）</h2>

<div class="container">

    <form action="" method="POST">

        <label>商品名</label>
        <input type="text" value="<?php echo htmlspecialchars($product['product_name']); ?>" readonly>

        <label>発注日（自動）</label>
        <input type="text" name="order_date" value="<?php echo $order_date; ?>" readonly>

        <label>入荷日（自動）</label>
        <input type="text" name="arrival_date" value="<?php echo $arrival_date; ?>" readonly>

        <label>入荷数量</label>
        <input type="number" name="quantity" min="1" required>

        <label>仕入れ価格</label>
        <input type="number" name="cost_price" min="0" required>

        <label>備考</label>
        <textarea name="note"></textarea>

        <!-- hidden -->
        <input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>">

        <div class="button-area">
            <button class="btn" type="submit">登録</button>
            <a class="btn-cancel" href="G-23_product-detail.php?product_id=<?php echo $product_id; ?>">キャンセル</a>
        </div>

    </form>

</div>

</body>
</html>
