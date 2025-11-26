<?php
// DB接続
require '../common/db_connect.php';

// --- フィルター入力の取得（GET） ---
$search     = trim($_GET['search'] ?? '');
$min_price  = trim($_GET['min_price'] ?? '');
$max_price  = trim($_GET['max_price'] ?? '');
$category   = trim($_GET['category'] ?? '');
$maker      = trim($_GET['maker'] ?? '');

$where  = [];
$params = [];

// フィルターが入力されている場合のみ条件を追加
if ($search !== '') {
  $where[] = 'product_name LIKE :search';
  $params[':search'] = "%{$search}%";
}
if ($min_price !== '' && is_numeric($min_price)) {
  $where[] = 'price >= :min_price';
  $params[':min_price'] = $min_price;
}
if ($max_price !== '' && is_numeric($max_price)) {
  $where[] = 'price <= :max_price';
  $params[':max_price'] = $max_price;
}
if ($category !== '') {
  $where[] = 'category_id = :category';
  $params[':category'] = $category;
}
if ($maker !== '') {
  $where[] = 'maker LIKE :maker';
  $params[':maker'] = "%{$maker}%";
}

// SQL組み立て
$sql = 'SELECT * FROM product';
if (!empty($where)) {
  $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY product_id ASC';

// 実行
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>商品管理 - ニシムラ Online</title>
  <link rel="stylesheet" href="../css/G-22_staff_product.css">
  <link rel="stylesheet" href="../css/staff_header.css">
</head>
<body>
  <?php require_once __DIR__ . '/../common/staff_header.php'; ?>

  <main class="main-container">
    <h2 class="page-title">商品管理</h2>

    <!-- 上部：新規商品登録ボタン＋検索フォーム -->
    <div class="action-row" style="display:flex; gap:10px; align-items:center;">
      <a href="G-23_product-detail.php" class="new-product-btn">+新規商品登録</a>
      <form method="get" class="search-bar" style="display:flex; gap:8px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="商品名で検索">
        <button type="submit" class="apply-btn">検索</button>
      </form>
    </div>

    <div class="main-area">
      <!-- 商品一覧（DBから動的表示） -->
      <section class="product-list" style="flex: 1;">
        <?php if ($products): ?>
          <?php foreach ($products as $p): ?>
            <div class="product-card">
              <!-- 内部IDと外部公開用コードを両方表示 -->
              <div class="product-id">
                内部ID: <?= htmlspecialchars($p['product_id']) ?><br>
                公開コード: <?= htmlspecialchars($p['jan_code'] ?? '未設定') ?>
              </div>

              <div class="product-main">
                <!-- 商品画像 -->
                <img src="<?= htmlspecialchars($p['product_image']) ?>" alt="商品画像" onerror="this.src='images/noimage.png'">
                <!-- 商品情報（画像横） -->
                <div class="product-info">
                  <h4><?= htmlspecialchars($p['product_name']) ?></h4>
                  <p class="price">¥<?= number_format($p['price']) ?></p>
                  <p class="detail"><?= nl2br(htmlspecialchars($p['product_detail'])) ?></p>
                </div>
              </div>

              <!-- 詳細テーブル -->
              <div class="product-table">
                <table>
                  <tr>
                    <td><strong>メーカー:</strong> <?= htmlspecialchars($p['maker']) ?></td>
                    <td><strong>カラー:</strong> <?= htmlspecialchars($p['color']) ?></td>
                  </tr>
                  <tr>
                    <td><strong>在庫数:</strong> <?= htmlspecialchars($p['stock_quantity']) ?> 台</td>
                    <td><strong>発注数:</strong> <?= htmlspecialchars($p['order_quantity'] ?? '未設定') ?> 台</td>
                  </tr>
                  <tr>
                    <td><strong>最終入荷日:</strong> <?= htmlspecialchars($p['last_arrival_date'] ?? '未設定') ?></td>
                    <td><strong>最終発注日:</strong> <?= htmlspecialchars($p['last_order_date'] ?? '未設定') ?></td>
                  </tr>
                </table>
              </div>

              <div class="product-actions">
                <button onclick="location.href='G-23_product-detail.php?product_id=<?= $p['product_id'] ?>'">編集</button>
                <button onclick="if(confirm('削除しますか？')) location.href='delete_product.php?id=<?= $p['product_id'] ?>'">削除</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="color:#666;">条件に一致する商品がありません。</div>
        <?php endif; ?>
      </section>

      <!-- 右側フィルター -->
      <aside class="filter-box">
        <h3>フィルター</h3>
        <form method="get">
          <label>商品名<br><input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="例: テレビ"></label>
          <label>最低価格<br><input type="text" name="min_price" value="<?= htmlspecialchars($min_price) ?>" placeholder="例: 5000"></label>
          <label>最高価格<br><input type="text" name="max_price" value="<?= htmlspecialchars($max_price) ?>" placeholder="例: 20000"></label>
          <label>カテゴリー<br><input type="text" name="category" value="<?= htmlspecialchars($category) ?>" placeholder="例: 1"></label>
          <label>メーカー<br><input type="text" name="maker" value="<?= htmlspecialchars($maker) ?>" placeholder="例: AQUAVIEW"></label>
          <button class="apply-btn" type="submit">適用</button>
        </form>
      </aside>
    </div>
  </main>
</body>
</html>
