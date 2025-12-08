<?php
session_start();
require __DIR__ . '/../common/db_connect.php';

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
    // 商品IDとカラーを分離（例： "23_red" → 23, red）
    list($product_id, $color) = explode('_', $key);

    $sql = "SELECT product_id, product_name, price, product_image, color FROM product WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($p) {
        // 画像切り替え（例：image-original.jpg → image-blue.jpg）
        $p['product_image'] = preg_replace('/-[^-]+(\.\w+)$/', '-' . $color . '$1', $p['product_image']);

        $p['qty'] = $qty; // 数量
        $p['color'] = $color; // カラー
        $p['color_display'] = $color_display_map[$color] ?? $color; // 表示用カラー名

        $cart_items[] = $p;
        $total += $p['price'] * $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>カート | ニシムラOnline</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/G-11_cart.css">
</head>
<body>
<?php require __DIR__ . '/../common/header.php'; ?>

<div class="cart-page-wrapper">
    <!-- 小計・レジボタン -->
    <div class="cart-summary">
        <p class="total">小計：￥<?= number_format($total) ?></p>
        <?php
        $cart_query = http_build_query([
            'cart_items' => array_keys($cart)
            ]);
            ?>
          <a href="G-12_order.php?<?= $cart_query ?>" class="checkout-btn">レジへ進む（<?= $cart_total_qty ?>個の商品）</a>
<hr>
    <div class="cart">
        <?php if (empty($cart_items)): ?>
            <p>カートに商品がありません。</p>
        <?php else: ?>
            <?php foreach ($cart_items as $item): ?>
                <div class="item" onclick="location.href='G-9_product-detail.php?product_id=<?= $item['product_id'] ?>'">
                    <!-- 左側：商品情報＋ボタン -->
                    <div class="item-right">
                        <p class="name"><?= htmlspecialchars($item['product_name']) ?></p>
                        <p class="price">¥<?= number_format($item['price']) ?></p>
                        <p class="color">カラー：<?= htmlspecialchars($item['color_display']) ?></p>
                        <div class="buttons">
                        <form action="G-11_delete-cart.php" method="POST" style="display:inline;" onsubmit="event.stopPropagation();">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($item['product_id'] . '_' . $item['color']) ?>">
                            <button type="submit" class="delete-btn" 
                                onclick="event.stopPropagation();">削除</button>
                        </form>
                        <a href="G-12_order.php?id=<?= urlencode($item['product_id']) ?>&color=<?= urlencode($item['color']) ?>"
                        class="buy-btn" onclick="event.stopPropagation();">購入</a>
                        </div>
                    </div>

                    <!-- 右側：商品画像 -->
                    <div class="item-left">
                        <img src="<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="product-img">
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
<?php
// ================================
// ▼▼ G-11_cart.php の最後に追加 ▼▼
// ================================



// ▼ 2. 個別購入（id + color が来たらその商品だけ削除）
if (isset($_GET['id'], $_GET['color'])) {
    $key = $_GET['id'] . '_' . $_GET['color'];

    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }

    // G-12 に遷移
    header("Location: G-12_order.php?id=" . urlencode($_GET['id']) . "&color=" . urlencode($_GET['color']));
    exit;
}

// ================================
// ▲▲ 追加ここまで ▲▲
// ================================
?>
</html>
