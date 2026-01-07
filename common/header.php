<?php
// ヘッダーコードの最上部にあるPHPコードを以下に置き換えてください。

// 1. セッションがまだ開始されていない場合のみ、セッションを開始する
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. ログイン状態とユーザー名を取得
$login = isset($_SESSION['customer']['id']); 
$user_name = $login ? $_SESSION['customer']['name'] : '';
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
                <span class="login-text">ログインはこちら →</span>
            <?php endif; ?>
        </div>

        <!-- 右側：ユーザ情報・カート -->
        <div class="header-right-icons">

            <div class="header-user">
                <?php if ($login): ?>
                <!-- ログイン時：会員情報へ -->
                    <a href="../フロント/G-4_member-information.php">
                        <img src="../img/icon.png" alt="会員情報" class="user-icon">
                    </a>
                <?php else: ?>
                <!-- 未ログイン時：会員登録フォームへ -->
                    <a href="../フロント/G-1_customer-form.php">
                        <img src="../img/icon.png" alt="ログイン" class="user-icon">
                    </a>
                <?php endif; ?>
            </div>

            <div class="header-cart">
                <a href="../フロント/G-11_cart.php">
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

    <!-- スライドメニュー -->
<nav id="sideMenu" class="side-menu">
    <ul>
        <li><a href="../フロント/G-4_member-information.php">マイページ</a></li>
        <li><a href="../フロント/G-8_home.php">トップページ</a></li>
        <li><a href="../フロント/G-25_coupon-list.php">所持クーポン一覧</a></li>
        <li><a href="../フロント/G-6_withdrawal-check.php">ログアウト</a></li>
        <li><a href="../フロント/G-26_notice.php">お知らせ</a></li>
    </ul>
</nav>

<!-- メニューを閉じるための黒背景 -->
<div id="overlay" class="menu-overlay"></div>

<script>
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    const burger = document.querySelector(".hamburger-menu");

    burger.addEventListener("click", function () {
        menu.classList.toggle("open");
        overlay.classList.toggle("show");
    });

    overlay.addEventListener("click", function () {
        menu.classList.remove("open");
        overlay.classList.remove("show");
    });
</script>


</header>
