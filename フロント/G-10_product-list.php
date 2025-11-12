<?php
// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== パラメータ取得 =====
// GETで受け取るカテゴリID (例: C04など)
$category_id = isset($_GET['category']) ? $_GET['category'] : '';
// 検索キーワード
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// ===== SQL動的生成 =====
try {
    $sql = "SELECT product_id, product_name, price, product_image, category_id
            FROM product
            WHERE color = 'オリジナル'"; // ← オリジナルカラー限定

    // カテゴリが指定されていたら絞り込み
    if ($category_id !== '') {
        $sql .= " AND category_id = :category_id";
    }

    // キーワード検索
    if ($keyword !== '') {
        $sql .= " AND product_name LIKE :keyword";
    }

    $sql .= " ORDER BY product_id DESC";

    $stmt = $pdo->prepare($sql);

    // バインド処理
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
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品一覧</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <link rel="stylesheet" href="../css/G-10_product-list.css">
</head>

<body>
    <?php require_once __DIR__ . '/../common/header.php'; ?>

    <?php
    // パンくず設定
    $breadcrumbs = [['name' => 'ホーム', 'url' => 'G-8_home.php']];
    if ($category_id !== '') {
        $breadcrumbs[] = ['name' => 'カテゴリ別一覧'];
    } elseif ($keyword !== '') {
        $breadcrumbs[] = ['name' => '検索結果'];
    } else {
        $breadcrumbs[] = ['name' => '商品一覧'];
    }
    require __DIR__ . '/../common/breadcrumb.php';
    ?>

<main>
    <div class="top">
        <?php if ($category_id !== ''): ?>
            <h1>
                <?php
                    // カテゴリ名を表示
                    $category_names = [
                        'C01' => 'テレビ',
                        'C02' => '冷蔵庫',
                        'C03' => '電子レンジ',
                        'C04' => 'カメラ',
                        'C05' => 'ヘッドホン',
                        'C06' => '洗濯機',
                        'C07' => 'ノートPC',
                        'C08' => 'スマートフォン',
                    ];
                    echo htmlspecialchars($category_names[$category_id] ?? '商品一覧');
                ?>一覧
            </h1>
        <?php elseif ($keyword !== ''): ?>
            <h1>「<?php echo htmlspecialchars($keyword); ?>」の検索結果</h1>
        <?php else: ?>
            <h1>商品一覧</h1>
        <?php endif; ?>
    </div>

    <hr>

    <?php if (!empty($products)): ?>
        <div class="product-list">
            <?php foreach ($products as $p): ?>
                <div class="product-item">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($p['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($p['product_name']); ?>">
                    </div>

                    <div class="product-info">
                        <h2><?php echo htmlspecialchars($p['product_name']); ?></h2>
                        <p class="price">¥<?php echo number_format($p['price']); ?></p>

                        <div class="buttons">
                            <a href="G-9_product-detail.php?id=<?php echo $p['product_id']; ?>" class="detail-btn">詳細</a>
                            <a href="G-12_order.php?id=<?php echo $p['product_id']; ?>" class="buy-btn">購入</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-result">該当する商品はありません。</p>
    <?php endif; ?>
</main>

</body>
</html>
