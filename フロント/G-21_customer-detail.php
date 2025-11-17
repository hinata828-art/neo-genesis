<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db-connect.php';

// URLパラメータから顧客IDを取得
$customer_id = $_GET['id'] ?? null;
$filter = $_GET['filter'] ?? '';

try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 顧客情報
    $sql_customer = "SELECT * FROM customer WHERE customer_id = :customer_id";
    $stmt_customer = $pdo->prepare($sql_customer);
    $stmt_customer->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt_customer->execute();
    $customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);

    // ★購入履歴（商品名あり）
    $sql_purchase = "
        SELECT t.*, p.product_name
        FROM transaction_table t
        LEFT JOIN transaction_detail d ON t.transaction_id = d.transaction_id
        LEFT JOIN product p ON d.product_id = p.product_id
        WHERE t.customer_id = :customer_id
          AND t.transaction_type = '購入'
    ";
    if ($filter !== '') {
        $sql_purchase .= " AND t.delivery_status = :filter";
    }
    $sql_purchase .= " ORDER BY t.transaction_date DESC";

    $stmt_purchase = $pdo->prepare($sql_purchase);
    $stmt_purchase->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
    if ($filter !== '') {
        $stmt_purchase->bindValue(':filter', $filter, PDO::PARAM_STR);
    }
    $stmt_purchase->execute();
    $purchases = $stmt_purchase->fetchAll(PDO::FETCH_ASSOC);

    // ★レンタル履歴（商品名あり）
    $sql_rental = "
    SELECT 
        t.*, 
        d.product_id,
        p.product_name,
        r.rental_start,
        r.rental_end,
        r.rental_days,
        r.return_date,
        r.coupon_claimed
    FROM rental r
    LEFT JOIN transaction_table t 
        ON r.transaction_id = t.transaction_id
    LEFT JOIN transaction_detail d 
        ON t.transaction_id = d.transaction_id
    LEFT JOIN product p 
        ON d.product_id = p.product_id
    WHERE r.customer_id = :customer_id
";
    if ($filter !== '') {
        $sql_rental .= " AND t.delivery_status = :filter";
    }
    $sql_rental .= " ORDER BY t.transaction_date DESC";

    $stmt_rental = $pdo->prepare($sql_rental);
    $stmt_rental->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
    if ($filter !== '') {
        $stmt_rental->bindValue(':filter', $filter, PDO::PARAM_STR);
    }
    $stmt_rental->execute();
    $rentals = $stmt_rental->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'データベースエラー: ' . htmlspecialchars($e->getMessage());
    exit;
}
?>


<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>顧客詳細 - 管理者画面</title>
<link rel="stylesheet" href="../css/G-21_customer-detail.css">
<link rel="stylesheet" href="../css/header-Administrator.css">
<link rel="stylesheet" href="../css/G-21_sub.css"> <!-- ★追加したCSS -->
</head>

<body>
<?php
$breadcrumbs = [
    ['name' => '現在のページ']
];
require __DIR__ . '/header-Administrator.php';
?>

<div class="container">

  <!-- ★ 顧客情報と履歴を横並びにする wrapper -->
  <div class="detail-wrapper">

    <!-- 顧客情報 -->
    <section class="customer-info">
      <h2>顧客詳細</h2>

      <?php if ($customer): ?>
        <p><strong>会員ID：</strong><?= htmlspecialchars($customer['customer_id']) ?></p>
        <p><strong>氏名：</strong><?= htmlspecialchars($customer['customer_name']) ?></p>
        <p><strong>電話番号：</strong><?= htmlspecialchars($customer['phone_number']) ?></p>
        <p><strong>メール：</strong><?= htmlspecialchars($customer['email']) ?></p>
        <p><strong>支払い方法：</strong><?= htmlspecialchars($customer['payment_method']) ?></p>
        <p><strong>生年月日：</strong><?= htmlspecialchars($customer['birth_date']) ?></p>
        <p><strong>登録日：</strong><?= htmlspecialchars($customer['created_at']) ?></p>
      <?php else: ?>
        <p>顧客情報が見つかりません。</p>
      <?php endif; ?>
    </section>

    <!-- 履歴 -->
    <section class="history">

      <!-- 購入履歴 -->
      <div class="history-box">
        <h3>購入履歴</h3>

        <form method="get" action="G-21_customer-detail.php">
          <input type="hidden" name="id" value="<?= htmlspecialchars($customer_id) ?>">
          <label>絞り込み：</label>
          <select name="filter">
            <option value="">すべて</option>
            <option value="支払い済">支払い済</option>
            <option value="未配送">未配送</option>
            <option value="配送済">配送済</option>
          </select>
          <button type="submit">適用</button>
        </form>

        <?php if (!empty($purchases)): ?>
          <ul>
          <?php foreach ($purchases as $purchase): ?>
            <li>
              注文ID：<?= htmlspecialchars($purchase['transaction_id']) ?><br>
              商品名：<?= htmlspecialchars($purchase['product_name']) ?><br> <!-- ★追加 -->
              日付：<?= htmlspecialchars($purchase['transaction_date']) ?><br>
              支払い方法：<?= htmlspecialchars($purchase['payment']) ?><br>
              配送状況：<?= htmlspecialchars($purchase['delivery_status']) ?><br>
              合計金額：<?= htmlspecialchars($purchase['total_amount']) ?>円
            </li>
          <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>購入履歴はありません。</p>
        <?php endif; ?>
      </div>

      <!-- レンタル履歴 -->
      <div class="history-box">
        <h3>レンタル履歴</h3>

        <form method="get" action="G-21_customer-detail.php">
          <input type="hidden" name="id" value="<?= htmlspecialchars($customer_id) ?>">
          <label>絞り込み：</label>
          <select name="filter">
            <option value="">すべて</option>
            <option value="支払い済">支払い済</option>
            <option value="未配送">未配送</option>
            <option value="配送済">配送済</option>
          </select>
          <button type="submit">適用</button>
        </form>

        <?php if (!empty($rentals)): ?>
          <ul>
          <?php foreach ($rentals as $rental): ?>
            <li>
              注文ID：<?= htmlspecialchars($rental['transaction_id']) ?><br>
             商品名：<?= htmlspecialchars($rental['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?><br>
              日付：<?= htmlspecialchars($rental['transaction_date']) ?><br>
              支払い方法：<?= htmlspecialchars($rental['payment']) ?><br>
              配送状況：<?= htmlspecialchars($rental['delivery_status']) ?><br>
              合計金額：<?= htmlspecialchars($rental['total_amount']) ?>円
            </li>
          <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>レンタル履歴はありません。</p>
        <?php endif; ?>
      </div>

    </section><!-- history -->

  </div><!-- detail-wrapper -->

  <div class="back-btn">
    <a href="G-20_customer-management.php">← 戻る</a>
  </div>

</div>

</body>
</html>
