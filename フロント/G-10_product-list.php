<?php
// ===== セッション開始 =====
session_start();

// ===== データベース接続 =====
require '../common/db_connect.php';

// ===== パラメータ取得 =====
$category = $_GET['category'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// ===== 検索条件をセッションに保存 =====
// カテゴリが空でない、またはキーワードが空でない場合に保存
if ($category !== '' || $keyword !== '') {
    $_SESSION['last_category'] = $category;
    $_SESSION['last_keyword'] = $keyword;
}
// 検索条件をリセットしたい場合は、別途ロジックが必要です
// 例: カテゴリもキーワードも指定がない場合はセッションをクリアするなど
// 今回は指定された値があれば上書き・保存します

// ===== カテゴリ名リスト =====
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

// ===== SQL組み立て =====
$sql = "SELECT product_id, product_name, price, product_image 
        FROM product 
        WHERE color = 'オリジナル'";
$params = [];

if ($category !== '') {
    $sql .= " AND category_id = :category";
    $params[':category'] = $category;
}
if ($keyword !== '') {
    $sql .= " AND product_name LIKE :keyword";
    $params[':keyword'] = "%{$keyword}%";
}

// 並び順（後で並べ替え機能追加も可）
$sql .= " ORDER BY product_id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // 本番環境では詳細なエラーメッセージをユーザーに表示しない方が安全です
    error_log("商品データ取得エラー: " . $e->getMessage()); // ログに出力
    echo "商品データ取得エラーが発生しました。";
    $products = [];
}

// ===== パンくず設定 =====
$breadcrumbs = [
    ['name' => 'ホーム', 'url' => 'G-8_home.php']
];

if ($keyword !== '') {
    $breadcrumbs[] = ['name' => '検索結果：「' . htmlspecialchars($keyword) . '」'];
} elseif ($category !== '') {
    $category_name = $category_names[$category] ?? '商品一覧';
    $breadcrumbs[] = ['name' => $category_name];
} else {
    $breadcrumbs[] = ['name' => '商品一覧'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
            if ($keyword !== '') echo "検索結果：" . htmlspecialchars($keyword);
            elseif ($category !== '') echo $category_names[$category] ?? "商品一覧";
            else echo "商品一覧";
        ?>
    </title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/breadcrumb.css">
    <link rel="stylesheet" href="../css/G-10_product-list.css">
</head>

<body>
    <?php require_once __DIR__ . '/../common/header.php'; ?>
    <?php 
    // require __DIR__ . '/../common/breadcrumb.php'; 
    // breadcrumb.php が存在し、上記で設定した $breadcrumbs 変数を使用する場合にコメントを外します。
    ?>

<main>
    <div class="top">
        <h1>
            <?php 
                if ($keyword !== '') echo "検索結果：「" . htmlspecialchars($keyword) . "」";
                elseif ($category !== '') echo htmlspecialchars($category_names[$category] ?? '商品一覧');
                else echo "商品一覧";
            ?>
        </h1>
    </div>
    <hr>

    <?php if (empty($products)): ?>
        <p class="no-result">該当する商品は見つかりませんでした。</p>
    <?php else: ?>
        <div class="product-list">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <a href="G-9_product-detail.php?id=<?php echo $p['product_id']; ?>" class="product-link">
                        <img src="<?php echo htmlspecialchars($p['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($p['product_name']); ?>" class="product-img">
                        <div class="product-info">
                            <h2><?php echo htmlspecialchars($p['product_name']); ?></h2>
                            <p class="price">¥<?php echo number_format($p['price']); ?></p>
                            </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>