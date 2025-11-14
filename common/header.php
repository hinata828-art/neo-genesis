<?php
// データベース接続ファイルの読み込み
// ★★★ 注意: 'db-connect.php' へのパスが正しいか必ず確認してください ★★★
require_once 'db-connect.php'; 

// セッションを開始 (他のファイルで開始されていない場合)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$customer_name = null;
// セッションから顧客IDを取得します。セッションキーは環境に合わせて修正してください。
$customer_id = $_SESSION['customer']['id'] ?? null; 

if ($customer_id) {
    try {
        // 顧客名を取得するSQL
        $sql = "SELECT customer_name FROM customer WHERE customer_id = :customer_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // エスケープ処理はHTML側で行うため、ここではそのまま代入
            $customer_name = $result['customer_name']; 
        }
    } catch (PDOException $e) {
        // DB接続エラー時の処理（必要に応じて）
        // error_log("Database error: " . $e->getMessage());
    }
}
?>
<header>
    <div class="top-row">
        
        <div class="header-left">
            <div class="hamburger-menu navbar-burger" @click="toggleButton">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="header-logo">
                <a href="../フロント/G-8_home.php">
                    <img src="../img/NishimuraOnline.png" alt="ロゴ" class="logo-image">
                </a>
            </div>
        </div>

        <div class="header-right-icons">
            
            <div class="welcome-message">
                <?php if ($customer_name): ?>
                    いらっしゃいませ<br>
                    <span class="customer-name"><?= htmlspecialchars($customer_name) ?>さん</span>
                <?php else: ?>
                    いらっしゃいませ<br>
                    ゲスト様
                <?php endif; ?>
            </div>
            
            <div class="header-user">
                <a href="../フロント/G-4_member-information.php">
                    <img src="../img/icon.png" alt="会員情報" class="user-icon"> 
                </a>
            </div>

            <div class="header-cart">
                <a href="../フロント/G-11_cart.php">
                    <img src="../img/cart.png" alt="カートアイコン">
                    <label>カート</label>
                </a>
            </div>
        </div>
    </div>

    <form action="../フロント/G-10_product-list.php" method="GET" class="bottom-row">
        <div class="search-container">
            
            <select name="category" class="category-select">
                <option value="">家電</option>
                <option value="C01">テレビ</option>
                <option value="C02">冷蔵庫</option>
                <option value="C03">電子レンジ</option>
                <option value="C04">カメラ</option>
                <option value="C05">ヘッドホン</option>
                <option value="C06">洗濯機</option>
                <option value="C07">ノートPC</option>
                <option value="C08">スマートフォン</option>
            </select>

            <input type="text" name="keyword" placeholder="何をお探しですか？">

            <button type="submit" class="search-button">
                <img src="../img/kensaku.png" alt="検索">
            </button>
        </div>
    </form>
    
    <?php
    // パンくずリストの配列を受け取る（存在しない場合は空配列）
    $breadcrumbs = $breadcrumbs ?? [];

    // ホームは必ず先頭に追加
    array_unshift($breadcrumbs, ['name' => 'ホーム', 'url' => '../フロント/G-8_home.php']);
    ?>

    <nav class="breadcrumb">
        <ul>
            <?php foreach ($breadcrumbs as $index => $crumb): ?>
                <li>
                    <?php if (!empty($crumb['url']) && $index !== array_key_last($breadcrumbs)): ?>
                        <a href="<?= htmlspecialchars($crumb['url'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php else: ?>
                        <?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>