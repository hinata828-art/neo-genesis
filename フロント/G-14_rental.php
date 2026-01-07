<?php
// 1. セッションとDB接続
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$customer_info = $_SESSION['customer_info'] ?? null;

require '../common/db_connect.php';

// 2. ログイン状態の確認
/*$customer_info = null;
$customer_id = 0;
if (isset($_SESSION['customer'])) {
    $customer_info = $_SESSION['customer'];
    $customer_id = $_SESSION['customer']['id'];
} else {
    $customer_info = ['name' => '（ゲスト）', 'address' => '（住所未登録）'];
}*/
if (!isset($_SESSION['customer'])) { 
    echo "ログインしていません。"; 
    exit; 
} 

$customer_id = $_SESSION['customer']['id'];

// 3. URLから商品IDとカラーを取得
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$color_value = isset($_GET['color']) ? htmlspecialchars($_GET['color']) : 'original';

// 4. 商品IDを使ってDBから商品情報を取得
try {
    // rental_price も取得する
    $sql = "SELECT p.product_name, p.product_image, p.color, c.rental_price 
            FROM product p
            JOIN category c ON p.category_id = c.category_id
            WHERE p.product_id = :id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '商品データ取得エラー: ' . $e->getMessage();
    exit;
}

// 5. 商品が存在しない場合
if (!$product) {
    echo "<p>商品が見つかりません。</p>";
    exit;
}

// ▼▼▼ 辞書ロジック (G-12と同じ) ▼▼▼
$color_display_map = [
    'original' => 'オリジナル',
    '白'       => 'ホワイト',
    '青'       => 'ブルー',
    'ゲーミング' => 'ゲーミング',
    '黄色'     => 'イエロー',
    '赤'       => 'レッド',
    '緑'       => 'グリーン',
    'ブラック'   => 'ブラック',
    'ピンク'     => 'ピンク',
    'グレー'     => 'グレー'
];

// カラー名を取得
$color_name = $color_display_map[$color_value] ?? $color_value;

// ▼▼▼ 画像URL生成ロジック (G-12と同じ) ▼▼▼
$base_image_url_from_db = $product['product_image'] ?? '';
$selected_color_filename = $color_value; 
$image_to_display = '';

if (!empty($base_image_url_from_db)) {
    $true_base_url = preg_replace('/-[^-]+$/u', '', $base_image_url_from_db);

    if ($selected_color_filename === 'original') {
        $image_to_display = $true_base_url;
    } else {
        $image_to_display = $true_base_url . '-' . $selected_color_filename;
    }
} else {
    $image_to_display = '../img/no_image.jpg'; 
}


// ▼▼▼ 修正点1：JSで使う変数をPHPで準備 ▼▼▼

// レンタル料金（1ヶ月あたり）
$rental_price_base = $product['rental_price'] ?? 0;
// 補償サービス料（必須）
$compensation_price = 500;

// レンタル期間の倍率
$term_multipliers = [
    '1week'   => 0.5,
    '2weeks'  => 0.7,
    '1month'  => 1.0,
    '3months' => 1.5,
    '6months' => 2.0,
    '1year'   => 3.0
];

// 初期値（1ヶ月）で計算
$initial_subtotal = $rental_price_base * $term_multipliers['1month'];
$initial_total_price = $initial_subtotal + $compensation_price;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="../css/G-14_rental.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <title>レンタル申し込み</title>
</head>
<body>
    <?php require __DIR__ . '/../common/header.php'; ?>
    <?php
    $breadcrumbs = [
        ['name' => 'ホーム', 'url' => 'G-8_home.php'],
        ['name' => htmlspecialchars($product['product_name']), 'url' => 'G-9_product-detail.php?id=' . $product_id],
        ['name' => 'レンタル申し込み']
    ];
    //require __DIR__ . '/../common/breadcrumb.php';
    ?>
    <div class="container">
    <p>レンタル</p>
    <hr>
    <div class="product-section">
        <img src="<?php echo htmlspecialchars($image_to_display); ?>" alt="商品画像" class="product-image">
        <div class="product-info">
            <label class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></label>
            <div class="product-color-row">
                <label class="product-color-label">商品カラー：</label>
                <label class="product-color"><?php echo htmlspecialchars($color_name); ?></label>
            </div>
        </div>
    </div>

    <form action="G-15_rental-finish.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="color" value="<?php echo htmlspecialchars($color_value); ?>">
        
        <input type="hidden" name="total_amount" id="total_amount_hidden" value="<?php echo $initial_total_price; ?>">
        

    <div class="rental-section">
            <label>レンタル期間</label>
            <select name="rental_term" id="rental_term_select">
                <option value="1week">1週間</option>
                <option value="2weeks">2週間</option>
                <option value="1month" selected>1ヶ月</option>
                <option value="3months">3ヶ月</option>
                <option value="6months">6ヶ月</option>
                <option value="1year">1年</option>
            </select>
    </div>

    <div class="price-section">
        <label>レンタル料金 (1ヶ月あたり):<span class="price">￥<?php echo number_format($rental_price_base); ?></span></label><br>
        <label>小計：<span class="price" id="subtotal_display">￥<?php echo number_format($initial_subtotal); ?></span></label>
        <label>オプション代：<span class="price" id="option_price_display">￥<?php echo number_format($compensation_price); ?></span></label>
        <label>ご請求額：<span class="price" id="total_price_display">￥<?php echo number_format($initial_total_price); ?></span></label>
    </div>

    <hr>

    <div class="delivery-section">
        <label>お届け先氏名：</label><br>
        <input type="text" name="name" class="input-text" value="<?php echo htmlspecialchars($customer_info['customer_name'] ?? ''); ?>" required><br>
        <label>お届け先住所：</label><br>
        <input type="text" name="address" class="input-text" value="<?php echo htmlspecialchars($customer_info['full_address'] ?? ''); ?>" required><br>
    </div>

    <hr>

    <div class="payment-section">
        <p>お支払方法：</p><br>
        <div class="payment-box">
            <label><input type="radio" name="payment" value="conveni">コンビニ支払い</label><br>
        </div>

        <div class="payment-box">
            <label><input type="radio" name="payment" value="credit" checked>クレジットカード決済</label><br>

            <div class="credit-details">
                <label>カード名義：</label><br>
                <input type="text" name="cardname" placeholder="YAMADA TAROU" class="input-text"><br>
                <label>カード番号：</label><br>
                <input type="text" name="cardnumber" placeholder="0000-0000-0000" class="input-text"><br>
                <label>有効期限：</label><br>
                <div class="expiry-row">
                    <input type="text" name="monthnumber" placeholder="月" class="input-small">
                    <label>/</label>
                    <input type="text" name="yearnumber" placeholder="年" class="input-small"><br>
                </div>
            <label>セキュリティコード：</label><br>
            <input type="text" name="code" class="input-text"><br>
        </div>
    </div>
    <div class="payment-box">
        <label><input type="radio" name="payment" value="bank">銀行振込</label><br>
    </div>

    <div class="option-section">
        <p>追加オプション</p>
        <label><input type="checkbox" name="option_delivery" value="1">配送・返却サービス（自宅集荷）</label>
        <label><input type="checkbox" name="option_buy" value="1">購入オプション（レンタル料金を購入代金に充当）</label>
        <label>
            <input type="checkbox" checked disabled>
            補償サービス（+500円/月で破損・水没も補償）
        </label>
        <span>補償サービスは必須です</span>
        <input type="hidden" name="option_warranty" value="500">
    </div>
</div>

    <button type="submit" class="confirm-button">レンタルを確定する</button>
    </form> <script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. PHPから計算に必要な情報を取得
    const baseRentalPrice = <?php echo $rental_price_base; ?>;
    const compensationPrice = <?php echo $compensation_price; ?>;
    const multipliers = <?php echo json_encode($term_multipliers); ?>;

    // 2. 必要なHTML要素を取得
    const termSelect = document.getElementById('rental_term_select');
    const subtotalDisplay = document.getElementById('subtotal_display');
    const totalPriceDisplay = document.getElementById('total_price_display');
    const totalAmountHidden = document.getElementById('total_amount_hidden');

    // 3. 金額を更新する関数
    function updatePrice() {
        // 3a. 選択された期間 (例: '1month') を取得
        const selectedTerm = termSelect.value;
        
        // 3b. 倍率 (例: 1.0) を取得
        const multiplier = multipliers[selectedTerm];
        
        // 3c. 料金を計算
        const newSubtotal = baseRentalPrice * multiplier;
        const newTotalPrice = newSubtotal + compensationPrice;

        // 3d. 画面表示を更新 (カンマ区切り)
        subtotalDisplay.innerText = '￥' + newSubtotal.toLocaleString();
        totalPriceDisplay.innerText = '￥' + newTotalPrice.toLocaleString();
        
        // 3e. G-15に送る hidden フィールドの値を更新
        totalAmountHidden.value = newTotalPrice;
    }

    // 4. レンタル期間(select)が変更されたら、updatePrice関数を実行
    termSelect.addEventListener('change', updatePrice);

    // 5. ページ読み込み時にも一度実行 (初期金額を正しく表示するため)
    // (※PHP側で初期値を計算済みの場合は厳密には不要だが、念のため)
    updatePrice(); 
});
</script>
    
</body>
</html>