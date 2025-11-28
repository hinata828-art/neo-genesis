<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../common/db_connect.php'; // DB接続ファイル

if (isset($_SESSION['customer']['id'])) {
    $customer_id = $_SESSION['customer']['id'];
} else {
    // ログインしていない場合
    echo "ログインしていません。";
    // header('Location: G-1_login.php'); // ログインページへ
    exit;
}
//$customer_id = $_SESSION['customer_id'] ?? 6; // 仮の顧客ID

// ★★★ ここからSQLクエリを修正 ★★★
$sql = "
SELECT 
    cc.customer_coupon_id, 
    c.coupon_id, 
    c.discount_rate, 
    
    -- HTML側で使えるよう 'category_id' という別名を付けます
    cc.applicable_category_id AS category_id, 
    
    c.expiration_date,
    
    -- ★ 修正点 1: LEFT JOIN に変更 (NULLでもクーポン情報自体は残す)
    cat.category_name, 
    p.product_image
FROM customer_coupon cc
JOIN coupon c ON cc.coupon_id = c.coupon_id

-- ★ 修正点 2: LEFT JOIN に変更
LEFT JOIN category cat ON cat.category_id = cc.applicable_category_id 

-- ★ 修正点 3: LEFT JOIN に変更
LEFT JOIN (
    SELECT category_id, MIN(product_id) AS min_product_id
    FROM product
    GROUP BY category_id
) first_product ON first_product.category_id = cc.applicable_category_id

-- ★ 修正点 4: LEFT JOIN に変更
LEFT JOIN product p ON p.product_id = first_product.min_product_id

WHERE cc.customer_id = :customer_id
  AND cc.used_at IS NULL
  AND c.expiration_date >= CURDATE()
ORDER BY cc.acquired_at DESC
";
// ★★★ ここまでSQLクエリを修正 ★★★


$stmt = $pdo->prepare($sql);
$stmt->execute(['customer_id' => $customer_id]);
$coupons = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>所持クーポン一覧</title>
  <link rel="stylesheet" href="../css/header.css">
  <link rel="stylesheet" href="../css/breadcrumb.css">
  <link rel="stylesheet" href="../css/G-25_coupon_list.css">
</head>
<body>

    <?php require __DIR__ . '/../common/header.php'; ?>
    <?php
    $breadcrumbs = [
        ['name' => '所持クーポン']
    ];
    /*
    require __DIR__ . '/../common/breadcrumb.php';
    */
    ?>

  <main class="coupon-container">
    <h2>所持クーポン一覧</h2>

    <?php if (empty($coupons)): ?>
        <p class="no-coupon-message">現在利用可能なクーポンはありません。</p>
    <?php endif; ?>

    <?php foreach ($coupons as $coupon): ?>
      <div class="coupon-card">
          <img src="<?= htmlspecialchars($coupon['product_image'] ?? '../img/coupon.png') ?>" alt="商品画像（クーポン対象）">
              
          <div class="coupon-info">
              <h3><?= htmlspecialchars($coupon['category_name'] ?? '全商品対象') ?>製品</h3>
              
              <p class="discount"><?= htmlspecialchars($coupon['discount_rate']) ?>% OFF！！</p>
              <p class="note">※購入時のみ適用可能</p>
              
              <a href="G-10_product-list.php?category=<?= htmlspecialchars($coupon['category_id'] ?? '') ?>" class="coupon-link">対象商品一覧へ</a>
          </div>
      </div>
    <?php endforeach; ?>

    <div class="home-button">
      <a href="G-8_home.php">ホーム画面へ</a>
    </div>
  </main>
</body>
</html>