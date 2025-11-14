<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../common/db_connect.php'; // DB接続ファイルを利用

// URLパラメータから顧客IDとフィルターを取得
$customer_id = $_GET['id'] ?? null;
$filter = $_GET['filter'] ?? "";

// PDO接続
try {
    $pdo = new PDO('mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8', USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('データベース接続エラー: ' . htmlspecialchars($e->getMessage()));
}

// 顧客情報取得
$sql_customer = "SELECT * FROM customer WHERE customer_id = :customer_id";
$stmt_customer = $pdo->prepare($sql_customer);
$stmt_customer->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
$stmt_customer->execute();
$customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);

/*--------------------------
   購入履歴（フィルター対応）
--------------------------*/
$sql_purchase = "SELECT * FROM transaction_table 
                 WHERE customer_id = :customer_id 
                 AND transaction_type = '購入'";

if ($filter !== "") {
    $sql_purchase .= " AND delivery_status = :filter";
}

$sql_purchase .= " ORDER BY transaction_date DESC";

$stmt_purchase = $pdo->prepare($sql_purchase);
$stmt_purchase->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);

if ($filter !== "") {
    $stmt_purchase->bindValue(':filter', $filter, PDO::PARAM_STR);
}

$stmt_purchase->execute();
$purchases = $stmt_purchase->fetchAll(PDO::FETCH_ASSOC);

/*--------------------------
   レンタル履歴（フィルター対応）
--------------------------*/
$sql_rental = "SELECT * FROM transaction_table 
               WHERE customer_id = :customer_id 
               AND transaction_type = 'レンタル'";

if ($filter !== "") {
    $sql_rental .= " AND delivery_status = :filter";
}

$sql_rental .= " ORDER BY transaction_date DESC";

$stmt_rental = $pdo->prepare($sql_rental);
$stmt_rental->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);

if ($filter !== "") {
    $stmt_rental->bindValue(':filter', $filter, PDO::PARAM_STR);
}

$stmt_rental->execute();
$rentals = $stmt_rental->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>顧客詳細 - 管理者画面</title>
<link rel="stylesheet" href="../css/G-21_customer-detail.css">
<link rel="stylesheet" href="../css/header.css">
</head>

<body>
<?php
$breadcrumbs = [
    ['name' => '現在のページ']
];
require __DIR__ . '/header-Administrator.php';
?>

<div class="container">

  <section class="customer-info">
    <h2>基本情報</h2>

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

  <section class="history">

    <!-- ===== 購入履歴 ===== -->
    <div class="history-box">
      <h3>購入履歴</h3>

      <form method="get" action="G-21_customer-detail.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($customer_id) ?>">
        <label for="filter">絞り込み：</label>
        <select name="filter" id="filter">
          <option value="">すべて</option>
          <option value="支払い済" <?= $filter=='支払い済'?'selected':'' ?>>支払い済</option>
          <option value="未配送" <?= $filter=='未配送'?'selected':'' ?>>未配送</option>
          <option value="配送済" <?= $filter=='配送済'?'selected':'' ?>>配送済</option>
        </select>
        <button type="submit">適用</button>
      </form>

      <?php if (!empty($purchases)): ?>
        <ul>
          <?php foreach ($purchases as $purchase): ?>
            <li>
              注文ID：<?= htmlspecialchars($purchase['transaction_id']) ?><br>
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

    <!-- ===== レンタル履歴 ===== -->
    <div class="history-box">
      <h3>レンタル履歴</h3>

      <form method="get" action="G-21_customer-detail.php">
        <input type="hidden" name="id" value="<?= htmlspecialchars($customer_id) ?>">
        <label for="filter">絞り込み：</label>
        <select name="filter" id="filter">
          <option value="">すべて</option>
          <option value="支払い済" <?= $filter=='支払い済'?'selected':'' ?>>支払い済</option>
          <option value="未配送" <?= $filter=='未配送'?'selected':'' ?>>未配送</option>
          <option value="配送済" <?= $filter=='配送済'?'selected':'' ?>>配送済</option>
        </select>
        <button type="submit">適用</button>
      </form>

      <?php if (!empty($rentals)): ?>
        <ul>
          <?php foreach ($rentals as $rental): ?>
            <li>
              注文ID：<?= htmlspecialchars($rental['transaction_id']) ?><br>
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

  </section>

  <div class="back-btn">
    <a href="G-20_customer-management.php">← 戻る</a>
  </div>

</div>

</body>
</html>
