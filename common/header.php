<?php
// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB接続（ユーザ名取得に必要なら）
require_once __DIR__ . '/db_connect.php';

// ★ ログイン判定
$login = isset($_SESSION['user_id']);   // ← ログイン時は user_id などで判断（任意）
$user_name = $login ? ($_SESSION['user_name'] ?? 'ユーザー') : '';
?>

<header>

    <!-- 上段：ロゴ、中央メッセージ、カート・ユーザ -->
    <div class="top-row">

        <!-- 左側：ハンバーガー + ロゴ -->
        <div class="header-left">
            <div class="hamburger-menu navbar-burger" @click="toggleButton">
                <span></span><span></span><span></span>
            </div>

            <div class="header-logo">
                <a href="../フロント/G-8_home.php">
                    <img src="../img/NishimuraOnline.png" alt="ロゴ">
                </a>
            </div>
        </div>

        <!-- ★★★ 中央メッセージ（追加部分） ★★★ -->
        <div class="welcome-message">
            <?php if ($login): ?>
                <span>いらっしゃいませ</span>
                <span class="customer-name"><?php echo htmlspecialchars($user_name); ?> 様</span>
            <?php else: ?>
                <span>いらっしゃいませ</span>
                <a href="../フロント/G-1_customer-form.php" class="login-link">ログインはこちら →</a>
            <?php endif; ?>
        </div>

        <!-- 右側：ユーザ情報・カート -->
        <div class="header-right-icons">

            <div class="header-user">
                <a href="../フロント/G-4_member-information.php">
                    <img src="../img/icon.png" alt="会員情報" class="user-icon">
                </a>
            </div>

            <div class="header-cart">
                <a href="../フロント/G-10_cart.php">
                    <img src="../img/cart.png" alt="カート">
                    <label>カート</label>
                </a>
            </div>
        </div>
    </div>

    <!-- 下段：検索バー -->
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

</header>
