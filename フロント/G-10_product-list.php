<?php
// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== パラメータ取得 =====
$category_id = isset($_GET['category']) ? $_GET['category'] : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// ===== SQL生成 =====
try {
    $sql = "SELECT product_id, product_name, price, product_image, category_id 
            FROM product 
            WHERE 1";

    // カテゴリが指定されている場合
    if ($category_id !== '') {
        $sql .= " AND category_id = :category_id";
    }

    // キーワード検索がある場合（部分一致）
    if ($keyword !== '') {
        $sql .= " AND product_name LIKE :keyword";
    }

    $sql .= " ORDER BY product_id ASC";

    $stmt = $pdo->prepare($sql);

    // バインド
    if ($category_id !== '') {
        $stmt->bindValue(':category_id', $category_id, PDO::PARAM_STR);
    }
    if ($keyword !== '') {
        $stmt->bindValue(':keyword', "%$keyword%", PDO::PARAM_STR);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '商品データ取得エラー: ' . $e->getMessage();
    $products = [];
}

// ===== カテゴリ名（C01など→日本語） =====
$category_names = [
    'C01' => 'テレビ',
    'C02' => '冷蔵庫',
    'C03' => '電子レンジ',
    'C04' => 'カメラ',
    'C05' => 'ヘッドホン',
    'C06' => '洗濯機',
    'C07' => 'ノートPC',
    'C08' => 'スマートフォン'
];

// タイトル名
$page_title = '商品一覧';
if ($category_id && isset($category_names[$category_id])) {
    $page_title = $category_names[$category_id] . '一覧';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <link rel="stylesheet" href="../css/G-10_product-list.css">
</head>

<body>
    <?php require_once __DIR__ . '/../common/header.php'; ?>
    <?php
    $breadcrumbs = [
        ['name' => 'ホーム', 'url' => 'G-8_home.php'],
        ['name' => htmlspecialchars($page_title)]
    ];
    require __DIR__ . '/../common/breadcrumb.php';
    ?>

<main>
    <div class="top">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
    </div>
    <hr>

    <?php if (!empty($products)): ?>
        <div class="product-list">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <a href="G-9_product-detail.php?id=<?php echo $p['product_id']; ?>">
                        <img src="<?php echo htmlspecialchars($p['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($p['product_name']); ?>" 
                             class="product-image">
                    </a>
                    <div class="product-info">
                        <h2 class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></h2>
                        <p class="product-price">¥<?php echo number_format($p['price']); ?></p>
                        <div class="product-buttons">
                            <a href="G-9_product-detail.php?id=<?php echo $p['product_id']; ?>" class="btn detail">詳細</a>
                            <a href="G-12_order.php?id=<?php echo $p['product_id']; ?>" class="btn buy">購入</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-result">該当する商品がありません。</p>
    <?php endif; ?>
</main>

</body>
</html>
