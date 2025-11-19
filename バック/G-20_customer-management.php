<?php
// DB接続
require '../common/db_connect.php';

// ====== 入力取得（検索・フィルタ） ======
$keyword = isset($_GET['searchKeyword']) ? trim($_GET['searchKeyword']) : '';
$ageGroup = isset($_GET['ageGroup']) ? trim($_GET['ageGroup']) : ''; // 年齢カラム導入時に使用
$pref = isset($_GET['pref']) ? trim($_GET['pref']) : '';

// ====== 都道府県リスト ======
$prefectures = [
  "北海道","青森県","岩手県","宮城県","秋田県","山形県","福島県","茨城県","栃木県","群馬県","埼玉県","千葉県",
  "東京都","神奈川県","新潟県","富山県","石川県","福井県","山梨県","長野県","岐阜県","静岡県","愛知県","三重県",
  "滋賀県","京都府","大阪府","兵庫県","奈良県","和歌山県","鳥取県","島根県","岡山県","広島県","山口県","徳島県",
  "香川県","愛媛県","高知県","福岡県","佐賀県","長崎県","熊本県","大分県","宮崎県","鹿児島県","沖縄県"
];



// ====== 検索・フィルタ用SQL構築 ======
// 年齢はテーブルに無いため「-」表示。年齢フィルタはコメントでフックを残します。
$sql = "
  SELECT
    c.customer_id,
    c.customer_name,
    c.email,
    c.phone_number,
    a.prefecture,
    a.city,
    a.address_line,
    a.postal_code
    -- , c.birth_date -- 例：birth_dateがあれば年齢算出に使用
  FROM customer c
  LEFT JOIN address a ON a.customer_id = c.customer_id
  WHERE 1 = 1
";

$params = [];

// キーワード（顧客名部分一致 or ID完全一致）
if ($keyword !== '') {
  if (ctype_digit($keyword)) {
    $sql .= " AND c.customer_id = :cid";
    $params[':cid'] = (int)$keyword;
  } else {
    $sql .= " AND c.customer_name LIKE :cname";
    $params[':cname'] = '%' . $keyword . '%';
  }
}

// 都道府県フィルタ
if ($pref !== '') {
  $sql .= " AND a.prefecture = :pref";
  $params[':pref'] = $pref;
}

// 年代フィルタ（年齢カラム導入後に有効化）
// if ($ageGroup !== '') {
//   switch ($ageGroup) {
//     case '10s': $sql .= " AND TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) BETWEEN 10 AND 19"; break;
//     case '20s': $sql .= " AND TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) BETWEEN 20 AND 29"; break;
//     case '30s': $sql .= " AND TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) BETWEEN 30 AND 39"; break;
//     case '40s': $sql .= " AND TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) BETWEEN 40 AND 49"; break;
//     case '50s': $sql .= " AND TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) BETWEEN 50 AND 59"; break;
//     case '60s': $sql .= " AND TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) >= 60"; break;
//   }
// }

$sql .= " ORDER BY c.customer_id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// ====== HTML描画 ======
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>顧客管理</title>
    <link rel="stylesheet" href="../css/G-20_customer-management.css">
    <link rel="stylesheet" href="../css/staff_header.css">
  
</head>
<body>
  <?php require_once __DIR__ . '/../common/staff_header.php'; ?>

  <main class="page">
    <div class="page-title">
      <h1>顧客管理</h1>
    </div>

    <!-- 統合検索バー（顧客名 or ID） -->
    <div class="search-bar">
      <form class="search-form" method="get">
        <label for="searchKeyword">顧客名またはID</label>
        <div class="search-row">
          <input
            type="text"
            id="searchKeyword"
            name="searchKeyword"
            placeholder="例：佐藤太郎 または 10001"
            value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>"
          />
          <button type="submit" class="btn btn-search">検索</button>
        </div>
      </form>
    </div>

    <div class="content">
      <!-- 左：顧客一覧 -->
      <section class="customer-list card">
        <div class="section-header">
          <h2>顧客一覧</h2>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th class="col-name">顧客名</th>
                <th class="col-id">ID</th>
                <th class="col-age">年齢</th>
                <th class="col-action">操作</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr>
                  <td colspan="4" style="text-align:center;color:#6b7280;">該当する顧客が見つかりません</td>
                </tr>
              <?php else: ?>
                <?php foreach ($rows as $row): ?>
                  <?php
                    // 年齢算出（birth_dateがある前提の例。現状は「-」）
                    // $age = null;
                    // if (!empty($row['birth_date'])) {
                    //   $age = (int)((new DateTime())->diff(new DateTime($row['birth_date']))->y);
                    // }
                    $ageDisplay = '-';
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['customer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $ageDisplay; ?></td>
                    <td>
                      <form action="customer_detail.php" method="get" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['customer_id'], ENT_QUOTES, 'UTF-8'); ?>" />
                        <button class="btn btn-detail" type="submit">詳細</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- 右：フィルター -->
      <aside class="filters card">
        <div class="section-header">
          <h2>フィルター</h2>
        </div>

        <form class="filter-form" method="get">
          <div class="form-group">
            <label for="ageGroup">年代</label>
            <select id="ageGroup" name="ageGroup" class="select">
              <option value="">すべて</option>
              <option value="10s" <?php echo $ageGroup==='10s'?'selected':''; ?>>10代</option>
              <option value="20s" <?php echo $ageGroup==='20s'?'selected':''; ?>>20代</option>
              <option value="30s" <?php echo $ageGroup==='30s'?'selected':''; ?>>30代</option>
              <option value="40s" <?php echo $ageGroup==='40s'?'selected':''; ?>>40代</option>
              <option value="50s" <?php echo $ageGroup==='50s'?'selected':''; ?>>50代</option>
              <option value="60s" <?php echo $ageGroup==='60s'?'selected':''; ?>>60代</option>
            </select>
          </div>

          <div class="form-group">
            <label for="pref">住所（都道府県）</label>
            <select id="pref" name="pref" class="select">
              <option value="">すべて</option>
              <?php foreach ($prefectures as $p): ?>
                <option value="<?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $pref===$p?'selected':''; ?>>
                  <?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 検索キーワードの維持 -->
          <input type="hidden" name="searchKeyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" />

          <div class="form-actions">
            <button type="submit" class="btn btn-apply">適用</button>
          </div>
        </form>
      </aside>
    </div>
  </main>
</body>
</html>
