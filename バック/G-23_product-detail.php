<?php
require '../common/db_connect.php';

// 商品ID取得
if (!isset($_GET['product_id'])) {
    header("Location: G-22_product.php");
    exit;
}
$product_id = $_GET['product_id'];

// カテゴリID → 名称
$categoryList = [
    "C01" => "テレビ",
    "C02" => "冷蔵庫",
    "C03" => "電子レンジ",
    "C04" => "カメラ",
    "C05" => "イヤホン",
    "C06" => "洗濯機",
    "C07" => "ノートPC",
    "C08" => "スマートフォン"
];

// 商品情報取得
$sql = "SELECT * FROM product WHERE product_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "商品が見つかりません。";
    exit;
}

// 更新処理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $category = $_POST['category_id'];
    $color = $_POST['color'];
    $maker = $_POST['maker'];
    $detail = $_POST['product_detail'];
    $stock = $_POST['stock_quantity'];

    $updateSql = "UPDATE product 
        SET product_name = ?, price = ?, category_id = ?, color = ?, 
            maker = ?, product_detail = ?, stock_quantity = ?
        WHERE product_id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$name, $price, $category, $color, $maker, $detail, $stock, $product_id]);

    header("Location: G-22_product.php");
    exit;
}

// 購入履歴取得
$historySql = "
    SELECT 
        t.transaction_date,
        d.quantity
    FROM transaction_detail d
    INNER JOIN transaction_table t 
        ON d.transaction_id = t.transaction_id
    WHERE d.product_id = ?
      AND t.transaction_type = '購入'
    ORDER BY t.transaction_date DESC
";
$historyStmt = $pdo->prepare($historySql);
$historyStmt->execute([$product_id]);
$history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品詳細</title>

    <!-- ❗ ここが重要：CSS ファイルは ../css/ 以下 -->
    <link rel="stylesheet" href="../css/G-23_product-detail.css">
    <link rel="stylesheet" href="../css/staff_header.css">
</head>
<body>
<?php require_once __DIR__ . '/../common/staff_header.php'; ?>

<h2>商品詳細</h2>

<form method="POST">
<div class="container">

    <!-- 左エリア -->
    <div class="left-area">

        <label>商品ID</label>
        <input type="text" value="<?= htmlspecialchars($product['product_id']) ?>" disabled>

        <label>商品名</label>
        <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>">

        <label>価格(税込)</label>
        <input type="text" name="price" value="<?= htmlspecialchars($product['price']) ?>">

        <label>商品カテゴリー</label>
        <select name="category_id">
            <?php foreach ($categoryList as $key => $value): ?>
                <option value="<?= $key ?>" <?= $product['category_id'] === $key ? 'selected' : '' ?>>
                    <?= $value ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>メーカー</label>
        <input type="text" name="maker" value="<?= htmlspecialchars($product['maker']) ?>">

        <label>色</label>
        <input type="text" name="color" value="<?= htmlspecialchars($product['color']) ?>">

        <label>在庫数</label>
        <input type="text" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity']) ?>">

    </div>

    <!-- 右エリア -->
    <div class="right-area">

        <label>商品画像</label>
        <div class="product-image-box">
            <img src="<?= htmlspecialchars($product['product_image']) ?>" alt="商品画像">
        </div>

        <label>商品詳細</label>
        <textarea name="product_detail"><?= htmlspecialchars($product['product_detail']) ?></textarea>

        <label>購入履歴</label>
        <div class="history-box">
            <?php foreach ($history as $h): ?>
                <p>
                    <span class="history-date"><?= date("n/j", strtotime($h['transaction_date'])) ?></span>
                        <?= $h['quantity'] ?>台購入
                </p>
            <?php endforeach; ?>
        </div>

    </div>

</div>

<div class="button-area">
    <button type="button" class="btn btn-cancel" onclick="location.href='G-22_product.php'">キャンセル</button>
    <button type="submit" class="btn">登録</button>
    <a class="btn" href="G-24_product-arrival.php?product_id=<?php echo $product_id; ?>">
    入荷登録
</a>
</div>

</form>

</body>
</html>
