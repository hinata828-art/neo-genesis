<?php
// このファイルが require される前に、
// session_start() は実行済みであると想定します。
// ※カテゴリ用のDB接続は不要になりました
?>
<header>
    <!-- 上段：ロゴ、カート、会員情報 -->
    <div class="top-row">
        
        <!-- ハンバーガーメニュー (Bulmaのnavbar-burgerを流用) -->
        <!-- Vue.jsの :class と @click は、
             G-8_home.php などの読み込み元で Vueインスタンスが
             定義されていることを前提としています -->
        <div 
            class="hamburger-menu navbar-burger" 
            :class="{'is-active': isActive}" 
            @click="toggleButton"
        >
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <!-- ロゴ (ホームへのリンク) -->
        <div class="header-logo">
            <a href="G-8_home.php">
                <img src="../img/NishimuraOnline.png" alt="ロゴ" class="logo-image">
            </a>
        </div>

        <!-- ボタン類 (カート・会員情報) -->
        <div class="header-icons">
            <!-- 会員情報 (アイコン) -->
            <div class="header-user">
                <a href="G-4_member-information.php">
                    <img src="../img/icon.png" alt="会員情報" class="user-icon-image">
                </a>
            </div>
            <!-- カート -->
            <div class="header-cart">
                <a href="G-10_cart.php">
                    <img src="../img/cart.png" alt="カート">
                    <label>カート</label>
                </a>
            </div>
        </div>
    </div>

    <!-- 下段：検索フォーム -->
    <form action="G-9_search-result.php" method="GET" class="bottom-row">
        
        <div class="search-container">
            <input type="text" name="keyword" placeholder="何をお探しですか？">
            
            <!-- 検索ボタン (kensaku.png を使用) -->
            <button type="submit" class="search-button">
                <img src="../img/kensaku.png" alt="検索">
            </button>
        </div>

    </form>
</header>