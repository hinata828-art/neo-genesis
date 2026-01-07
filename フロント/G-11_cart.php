<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../common/db_connect.php';

// =====================================
// ▼ 個別購入時：カートから削除（HTML 出力前に実行）
// =====================================
if (isset($_GET['id'], $_GET['color'])) {
    $key = $_GET['id'] . '_' . $_GET['color'];

    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }
}
// =====================================

// カート取得
$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total = 0;

$cart_total_qty = array_sum($cart);

// カラー名マップ
$color_display_map = [
    'original' => 'オリジナル',
    '白色'     => 'ホワイト',
    '青'       => 'ブルー',
    'ゲーミング' => 'ゲーミング',
    '黄色'     => 'イエロー',
    '赤'       => 'レッド',
    '緑'       => 'グリーン',
    'ブラック' => 'ブラック',
    'ピンク'   => 'ピンク',
    'グレー'   => 'グレー',
];

// カート内の商品を1件ずつ処理
foreach ($cart as $key => $qty) {
    list($product_id, $color) = explode('_', $key);

    // 商品データ取得
    $sql = "SELECT product_id, product_name, price, product_image, color 
            FROM product WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($p) {
        $item = $p;
        $item['quantity'] = $qty;
        $item['color'] = $color;

        // ★ カラーバリアント画像処理
        $productImagePath = $p['product_image'];

        if ($color !== 'original' && $color !== $p['color']) {
            if (strpos($productImagePath, 'http') === 0) {
                $productImagePath .= '-' . $color;
            } else {
                $info = pathinfo($productImagePath);
                if (!empty($info['extension'])) {
                    $productImagePath = $info['filename'] . '-' . $color . '.' . $info['extension'];
                } else {
                    $productImagePath .= '-' . $color;
                }
            }
        }

        // 表示用 URL の生成
        if (strpos($productImagePath, 'http') === 0) {
            $imageUrl = $productImagePath;
        } elseif ($productImagePath) {
            $imageUrl = '../img/' . $productImagePath;
        } else {
            $imageUrl = 'images/noimage.png';
        }

        $item['product_image'] = $imageUrl;

        $cart_items[$key] = $item;
        $total += $item['price'] * $item['quantity'];
    }
}

if (isset($_GET['id'], $_GET['color'])) {
    // 単品モード
    // 対象商品の情報をデータとして G-12 へリダイレクト
    header("Location: G-12.php?id=$id&color=$color&qty=1&mode=single");
    exit;
}
// ------------------------------------
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カート</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <link rel="stylesheet" href="../css/G-11_cart.css">
</head>

<body>
<?php require __DIR__ . '/../common/header.php'; ?>

<div class="cart-container">
    <h2>カート</h2>

    <?php if (empty($cart_items)): ?>
        <p class="empty-message">カートに商品はありません。</p>
        <div class="button-area">
            <a href="G-8_home.php" class="btn back">お買い物を続ける</a>
        </div>

    <?php else: ?>
        <div class="summary-area">
            <p>合計点数：<?php echo $cart_total_qty; ?>点</p>
            <p>合計金額：¥<?php echo number_format($total); ?></p>

            <div class="button-area">
                <a href="G-8_home.php" class="btn back-to-shop-btn">お買い物を続ける</a>

                <?php
                    $cart_query = '';
                    foreach ($cart as $key => $qty) {
                        $cart_query .= 'cart_items[]=' . urlencode($key) . '&';
                    }
                    $cart_query = rtrim($cart_query, '&');
                ?>
                <a href="G-12_order.php?<?php echo $cart_query; ?>" class="btn checkout-btn">
                    レジへ進む
                </a>
            </div>
        </div>

        <div class="cart-items">
            <?php foreach ($cart_items as $key => $item): ?>
                <div class="cart-item">
                    <div class="item-right">
                        <h3>
                            <a href="G-9_product-detail.php?id=<?= $item['product_id'] ?>">
                                <?= htmlspecialchars($item['product_name']) ?>
                            </a>
                        </h3>

                        <p class="item-color">
                            カラー:
                            <?= htmlspecialchars($color_display_map[$item['color']] ?? $item['color']) ?>
                        </p>

                        <p class="item-price">¥<?= number_format($item['price']) ?></p>

                        <div class="action-buttons">
                            <form method="POST" action="G-11_delete-cart.php" class="delete-form">
                                <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                                <button type="submit" class="delete-btn">削除</button>
                            </form>

                            <a href="G-12_order.php?id=<?= urlencode($item['product_id']) ?>&color=<?= urlencode($item['color']) ?>"
                               class="buy-btn"
                               onclick="event.stopPropagation();">
                                購入
                            </a>
                        </div>
                    </div>

                    <div class="item-left">
                        <img src="<?= htmlspecialchars($item['product_image']) ?>"
                             alt="<?= htmlspecialchars($item['product_name']) ?>"
                             class="product-img">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
