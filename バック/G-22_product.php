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
  $where[] = 'name LIKE :maker';
  $params[':maker'] = "%{$maker}%";
}
// ▼▼▼ 拡張子(.jpg) を追加するロジックを「削除」 ▼▼▼
$base_image_url_from_db = $product['product_image'] ?? '';
$js_base_url = '';

if (!empty($base_image_url_from_db)) {
    // ベースURL (例: .../カメラ1-白 -> .../カメラ1)
    // (例: .../カメラ1 -> .../カメラ1 のまま)
    $js_base_url = preg_replace('/-[^-]+$/u', '', $base_image_url_from_db);
}
// ▲▲▲ ここまで ▲▲▲

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
    <?php require '../common/staff_header.php'; ?>

  <main class="main-container">
    <h2 class="page-title">商品管理</h2>

    <!-- 上部：新規商品登録ボタン＋検索フォーム -->
    <div class="action-row" style="display:flex; gap:10px; align-items:center;">
      <a href="g23_product_edit.php" class="new-product-btn">+新規商品登録</a>
      <form method="get" class="search-bar" style="display:flex; gap:8px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="商品名で検索">
        <button type="submit" class="apply-btn">検索</button>
      </form>
    </div>

    <div class="main-area">
      <!-- 商品一覧（DBから動的表示） -->
      <section class="product-list">
        <?php if ($products): ?>
          <?php foreach ($products as $p): ?>
            <div class="product-card">
              <div class="product-id">商品ID: <?= htmlspecialchars($p['product_id']) ?></div>
              <div class="product-main">
                <!-- 画像はhttps形式をそのまま利用 -->
                <img src="<?= htmlspecialchars($p['product_image']) ?>" alt="商品画像" onerror="this.src='images/noimage.png'">
                <div class="product-info">
                  <h4><?= htmlspecialchars($p['product_name']) ?></h4>
                  <p class="price">¥<?= number_format($p['price']) ?></p>
                  <p>メーカー: <?= htmlspecialchars($p['name']) ?></p>
                  <p>カラー: <?= htmlspecialchars($p['color']) ?></p>
                  <div class="product-actions">
                    <button onclick="location.href='g23_product_edit.php?id=<?= $p['product_id'] ?>'">編集</button>
                    <button onclick="if(confirm('削除しますか？')) location.href='delete_product.php?id=<?= $p['product_id'] ?>'">削除</button>
                  </div>
                </div>
                <div class="stock-info">
                  <p><strong>在庫数:</strong> <?= htmlspecialchars($p['stock_quantity']) ?> 台</p>
                  <p><strong>詳細:</strong> <?= htmlspecialchars($p['product_detail']) ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="color:#666;">条件に一致する商品がありません。</div>
        <?php endif; ?>
      </section>

      <!-- 右側フィルター（同じGETパラメータで動作） -->
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
