<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

    // 商品データを取得
    $sql = "SELECT product_id, product_name, price, product_image, color FROM product WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($p) {
        $item = $p;
        $item['quantity'] = $qty;
        $item['color'] = $color; // 'original', '青' など
        
        // ★★★ 修正: 画像パス決定ロジックの適用 ★★★
        $productImagePath = $p['product_image']; // DBからの画像パス/URL

        // 1. カラーバリアントの適用
        // 'original'やDBのデフォルトカラー以外の時、パスにサフィックスを付与
        if ($color !== 'original' && $color !== $p['color']) {
            if (strpos($productImagePath, 'http') === 0) {
                // DB URL形式の場合: 末尾にカラーサフィックスを付与 (例: .../カメラ1 -> .../カメラ1-青)
                $productImagePath .= '-' . $color;
            } else {
                // ファイル名形式の場合: 拡張子の前にカラーサフィックスを付与
                $info = pathinfo($productImagePath);
                if (!empty($info['extension'])) {
                    // 拡張子がある場合: filename-color.ext
                    $productImagePath = $info['filename'] . '-' . $color . '.' . $info['extension'];
                } else {
                    // 拡張子がない場合 (念のため): filename-color
                    $productImagePath .= '-' . $color;
                }
            }
        }
        
        // 2. パス/URL形式の決定 (表示用URLの生成)
        $imageUrl = '';
        if (strpos($productImagePath, 'http') === 0) {
            $imageUrl = $productImagePath; // 完全なURL
        } else if ($productImagePath) {
            // G-11からの相対パスは '../img/'
            $imageUrl = '../img/' . $productImagePath; // サーバーフォルダ内のファイル
        } else {
            $imageUrl = 'images/noimage.png';
        }

        $item['product_image'] = $imageUrl; // 最終的な表示用URLをセット
        // ★★★ 修正ここまで ★★★

        $cart_items[$key] = $item;
        $total += $item['price'] * $item['quantity'];
    }
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
    <?php
    $breadcrumbs = [
        ['name' => 'ホーム', 'url' => 'G-8_home.php'],
        ['name' => 'カート']
    ];
    //require __DIR__ . '/../common/breadcrumb.php';
    ?>

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
                <a href="G-12_order.php" class="btn checkout-btn">レジへ進む</a>
            </div>
        </div>

        <div class="cart-items">
            <?php foreach ($cart_items as $key => $item): ?>
                <div class="cart-item">
                    <div class="item-right">
                        <h3>
                            <a href="G-9_product-detail.php?id=<?= $item['product_id'] ?>"><?= htmlspecialchars($item['product_name']) ?></a>
                        </h3>
                        <p class="item-color">カラー: 
                            <?php echo htmlspecialchars($color_display_map[$item['color']] ?? $item['color']); ?>
                        </p>
                        <p class="item-price">¥<?php echo number_format($item['price']) ?></p>
                        <form method="POST" action="G-11_update_cart.php" class="quantity-form">
                            <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                            <label for="qty_<?= htmlspecialchars($key) ?>">数量:</label>
                            <input type="number" id="qty_<?= htmlspecialchars($key) ?>" name="quantity" 
                                   value="<?= htmlspecialchars($item['quantity']) ?>" min="1" required>
                        </form>
                        
                        <div class="action-buttons">
                            <form method="POST" action="G-11_delete-cart.php" class="delete-form">
                                <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                                <button type="submit" class="delete-btn">削除</button>
                            </form>
                            <a href="G-12_order.php?id=<?= urlencode($item['product_id']) ?>&color=<?= urlencode($item['color']) ?>"
   class="buy-btn" onclick="event.stopPropagation();">購入</a>
                        </div>
                    </div>

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

// ▼ 1. レジへ進む（cart_items が来たらカートを空にする）
if (isset($_GET['cart_items'])) {
    // カートを空にする
    $_SESSION['cart'] = [];

    // そのまま G-12 に渡す
    header("Location: G-12_order.php?cart_items=" . urlencode($_GET['cart_items']));
    exit;
}

// ▼ 2. 個別購入（id + color が来たらその商品だけ削除）
if (isset($_GET['id'], $_GET['color'])) {
    $key = $_GET['id'] . '_' . $_GET['color'];

    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }

    // G-12 に遷移
    // header("Location: G-12_order.php?id=" . urlencode($_GET['id']) . "&color=" . urlencode($_GET['color']));
    // 個別購入はG-12_order.php内のロジックで処理されるため、ここではセッションの削除のみに留め、リダイレクトは個別購入リンク (buy-btn) の処理に任せる
    // (既存のコードの意図が不明確なため、ここではコメントアウトし、buy-btnのリンク先をそのまま使う)

    // この部分のロジックは、個別の購入リンクから遷移した場合、カートからその商品を削除する処理と思われるが、
    // 既存のコードではこのブロックはどこからも呼ばれていないため、一旦そのまま残します。
}
?>