<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ===== データベース接続 =====
require '../common/db_connect.php';

// ログイン状態とユーザーIDの取得
$is_logged_in = isset($_SESSION['customer']['id']);
$current_user_id = $is_logged_in ? $_SESSION['customer']['id'] : 0;

// ===== パラメータ取得 =====
$category = $_GET['category'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// ===== カテゴリ名リスト =====
$category_names = [
    'C01' => 'テレビ', 'C02' => '冷蔵庫', 'C03' => '電子レンジ', 'C04' => 'カメラ',
    'C05' => 'ヘッドホン', 'C06' => '洗濯機', 'C07' => 'ノートPC', 'C08' => 'スマートフォン'
];

// ===== SQL組み立て (修正: お気に入り状態も一緒に取得) =====
// LEFT JOINを使って、商品と一緒に「自分のいいね情報」があるか確認します
$sql = "SELECT p.product_id, p.product_name, p.price, p.product_image,
               (CASE WHEN l.product_id IS NOT NULL THEN 1 ELSE 0 END) AS is_favorite
        FROM product p
        LEFT JOIN `like` l 
               ON p.product_id = l.product_id 
               AND l.user_id = :uid
        WHERE p.color = 'オリジナル'";

$params = [':uid' => $current_user_id];

if ($category !== '') {
    $sql .= " AND p.category_id = :category";
    $params[':category'] = $category;
}
if ($keyword !== '') {
    $sql .= " AND p.product_name LIKE :keyword";
    $params[':keyword'] = "%{$keyword}%";
}

// 並び順
$sql .= " ORDER BY p.product_id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "商品データ取得エラー: " . $e->getMessage();
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
    <style>
        /* 追加CSS: お気に入りボタン用（CSSファイルに移してもOK） */
        .product-card {
            position: relative; /* ハートボタンの基準位置 */
        }
        .btn-favorite-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            color: #ccc;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-favorite-overlay.active {
            color: #ff4757; /* 赤色 */
            border-color: #ff4757;
        }
        .btn-favorite-overlay:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../common/header.php'; ?>
    <?php
    //require __DIR__ . '/../common/breadcrumb.php';
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
                <?php
                    // 画像パスロジック
                    $productImagePath = $p['product_image'];
                    $imageUrl = '';
                    if (strpos($productImagePath, 'http') === 0) {
                        $imageUrl = htmlspecialchars($productImagePath);
                    } else if ($productImagePath) {
                        $imageUrl = '../img/' . htmlspecialchars($productImagePath);
                    } else {
                        $imageUrl = 'images/noimage.png'; 
                    }
                    
                    // お気に入り状態 (SQLで取得した 0 or 1)
                    $isFav = $p['is_favorite']; 
                ?>

                <div class="product-card">
                    
                    <button type="button" 
                            id="fav-btn-<?= $p['product_id'] ?>"
                            class="btn-favorite-overlay <?= $isFav ? 'active' : '' ?>"
                            onclick="toggleFavorite(<?= $p['product_id'] ?>)">
                        ♥
                    </button>

                    <img src="<?php echo $imageUrl; ?>"
                         alt="<?php echo htmlspecialchars($p['product_name']); ?>" class="product-img">
                    
                    <div class="product-info">
                        <h2><?php echo htmlspecialchars($p['product_name']); ?></h2>
                        <p class="price">¥<?php echo number_format($p['price']); ?></p>
                        <a href="G-9_product-detail.php?id=<?php echo $p['product_id']; ?>" class="detail-btn">詳細を見る</a>
                    </div>

                </div>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script>
// ★JavaScript: お気に入り切り替え機能
const IS_LOGGED_IN = <?php echo json_encode($is_logged_in); ?>;
const LOGIN_PAGE_URL = 'G-1_customer-form.php';

function toggleFavorite(productId) {
    if (!IS_LOGGED_IN) {
        alert("お気に入り機能を使うにはログインが必要です。");
        location.href = LOGIN_PAGE_URL;
        return;
    }

    const btn = document.getElementById('fav-btn-' + productId);
    
    // APIへ送信 (G-9_favorite.php を再利用)
    fetch('G-9_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'added') {
            btn.classList.add('active'); // 赤くする
        } else if (data.status === 'removed') {
            btn.classList.remove('active'); // グレーに戻す
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

</body>
</html>