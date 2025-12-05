<?php
require '../common/db_connect.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 商品ID取得（GETまたはPOST）
if (!isset($_GET['product_id']) && !isset($_POST['product_id'])) {
    header("Location: G22.php");
    exit;
}
$product_id = intval($_GET['product_id'] ?? $_POST['product_id']);

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

// --- 更新処理（POST送信時） ---
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE product 
            SET product_name = :name,
                price = :price,
                category_id = :cat,
                maker = :maker,
                color = :color,
                product_image = :image,
                product_detail = :detail
            WHERE product_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'   => $_POST['product_name'],
        ':price'  => $_POST['price'],
        ':cat'    => $_POST['category_id'],
        ':maker'  => $_POST['maker'],
        ':color'  => $_POST['color'],
        ':image'  => $_POST['product_image'],
        ':detail' => $_POST['product_detail'],
        ':id'     => $product_id
    ]);
    $message = "商品情報を更新しました。";
}

// 商品情報取得（更新後も再取得）
$sql = "SELECT * FROM product WHERE product_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "商品が見つかりません。";
    exit;
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

// 発注・入荷履歴取得
$orderSql = "
    SELECT 
        sa.order_date,
        sa.arrival_date,
        sa.quantity AS arrival_quantity,
        sa.cost_price,
        s.staff_name,
        sa.note
    FROM stock_arrival sa
    LEFT JOIN staff s ON sa.staff_id = s.staff_id
    WHERE sa.product_id = ?
    ORDER BY sa.order_date DESC
";
$orderStmt = $pdo->prepare($orderSql);
$orderStmt->execute([$product_id]);
$orderHistory = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <div class="right-area">
            <label>商品画像</label>
            <div class="product-image-box">
                <?php if ($imageUrl): ?>
                    <img src="<?= $imageUrl ?>" alt="商品画像">
                <?php else: ?>
                    <p>画像がありません</p>
                <?php endif; ?>
                </div>
            <input type="text" name="product_image" value="<?= $productImagePath ?>">

            <label>商品詳細</label>
    <meta charset="UTF-8">
    <title>商品編集</title>
    <link rel="stylesheet" href="../css/G-23_product-detail.css">
    <link rel="stylesheet" href="../css/staff_header.css">
</head>
<body>
    <?php require_once __DIR__ . '/../common/staff_header.php'; ?>

<h2>商品編集</h2>

<?php if ($message): ?>
    <p style="color:green; font-weight:bold;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form action="" method="post">
    <input type="hidden" name="product_id" value="<?= $product_id ?>">
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
                <?php foreach ($categoryList as $id => $name): ?>
                    <option value="<?= $id ?>" <?= $product['category_id']===$id?'selected':''; ?>>
                        <?= $name ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>メーカー</label>
            <input type="text" name="maker" value="<?= htmlspecialchars($product['maker']) ?>">

            <label>色</label>
            <input type="text" name="color" value="<?= htmlspecialchars($product['color']) ?>">

            <label>在庫数</label>
            <input type="text" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity']) ?>" readonly>

            <!-- 履歴エリア（表示専用） -->
            <div class="order-history-box">
                <label>発注・入荷履歴</label>
                    <?php if ($orderHistory): ?>
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>発注日</th>
                                    <th>入荷日</th>
                                    <th>入荷数</th>
                                    <th>担当者</th>
                                    <th>備考</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderHistory as $oh): ?>
                                    <tr>
                                        <td><?= date("n/j", strtotime($oh['order_date'])) ?></td>
                                        <td><?= $oh['arrival_date'] ? date("n/j", strtotime($oh['arrival_date'])) : '未入荷' ?></td>
                                        <td><?= htmlspecialchars($oh['arrival_quantity']) ?>台</td>
                                        <td><?= htmlspecialchars($oh['staff_name'] ?? '-') ?></td>
                                        <td><?= nl2br(htmlspecialchars($oh['note'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>発注・入荷履歴はありません。</p>
                    <?php endif; ?>
            </div>
        </div>

        <!-- 右エリア -->
        <div class="right-area">
            <label>商品画像</label>
            <div class="product-image-box">
                <img src="<?= htmlspecialchars($product['product_image']) ?>" alt="商品画像">
            </div>
            <input type="text" name="product_image" value="<?= htmlspecialchars($product['product_image']) ?>">

            <label>商品詳細</label>
            <textarea name="product_detail"><?= htmlspecialchars($product['product_detail']) ?></textarea>

            
            

            <div class="history-box">
                    <label>購入履歴</label>
                    <?php if ($history): ?>
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>購入日</th>
                                    <th>数量</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $h): ?>
                                    <tr>
                                        <td><?= date("n/j", strtotime($h['transaction_date'])) ?></td>
                                        <td><?= htmlspecialchars($h['quantity']) ?>台</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>購入履歴はありません。</p>
                    <?php endif; ?>
            </div>
             <!-- ボタンエリア -->
            <div class="button-area">
                <a class="btn" href="G-24_product-arrival.php?product_id=<?= $product_id ?>">入荷登録</a>
                <button type="submit" class="btn">更新</button>
                <a class="btn-cancel" href="G-22_product.php">戻る</a>
            </div>
    
        </div>
    </form>
</div>
            

</body>
</html>
