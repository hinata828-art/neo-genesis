<?php
require '../common/db_connect.php';
session_start();

// 商品ID取得
if (!isset($_GET['product_id'])) {
    header("Location: G-22_product.php");
    exit;
}
$product_id = intval($_GET['product_id']);

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
    <meta charset="UTF-8">
    <title>商品詳細</title>
    <link rel="stylesheet" href="../css/G-23_product-detail.css">
    <link rel="stylesheet" href="../css/staff_header.css">
</head>
<body>
<?php require_once __DIR__ . '/../common/staff_header.php'; ?>

<h2>商品詳細</h2>

<div class="container">
    <!-- 左エリア -->
    <div class="left-area">
        <label>商品ID</label>
        <input type="text" value="<?= htmlspecialchars($product['product_id']) ?>" disabled>

        <label>商品名</label>
        <input type="text" value="<?= htmlspecialchars($product['product_name']) ?>" disabled>

        <label>価格(税込)</label>
        <input type="text" value="<?= number_format($product['price']) ?>" disabled>

        <label>商品カテゴリー</label>
        <input type="text" value="<?= $categoryList[$product['category_id']] ?? $product['category_id'] ?>" disabled>

        <label>メーカー</label>
        <input type="text" value="<?= htmlspecialchars($product['maker']) ?>" disabled>

        <label>色</label>
        <input type="text" value="<?= htmlspecialchars($product['color']) ?>" disabled>

        <label>在庫数</label>
        <input type="text" value="<?= htmlspecialchars($product['stock_quantity']) ?>" disabled>
    </div>

    <!-- 右エリア -->
    <div class="right-area">
        <label>商品画像</label>
        <div class="product-image-box">
            <img src="<?= htmlspecialchars($product['product_image']) ?>" alt="商品画像">
        </div>

        <label>商品詳細</label>
        <textarea readonly><?= htmlspecialchars($product['product_detail']) ?></textarea>

        <!-- 履歴エリア -->
        <div class="history-container">
            <!-- 発注・入荷履歴 -->
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

            <!-- 購入履歴 -->
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
        </div>

        <!-- ボタンエリア -->
        <div class="button-area">
            <a class="btn" href="G-24_product-arrival.php?product_id=<?= $product_id ?>">入荷登録</a>
            <a class="btn-cancel" href="G-22_product.php">戻る</a>
        </div>
    </div>
</div>

</body>
</html>
