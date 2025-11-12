<?php
// このファイルが require される前に、
// session_start() は実行済みであると想定します。
?>
<header>
    <!-- 上段：ロゴ、カート、会員情報 -->
    <div class="top-row">
        
        <!-- ★修正：左側（ハンバーガーとロゴ）をグループ化 -->
        <div class="header-left">
            <!-- ハンバーガーメニュー (Bulmaのnavbar-burgerを流用) -->
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
                <a href="../フロント/G-8_home.php">
                    <img src="../img/NishimuraOnline.png" alt="ロゴ" class="logo-image">
                </a>
            </div>
        </div>

        <div class="header-cart">
            <a href="../フロント/G-11_cart.php">
                <img src="../img/cart.png" alt="カートアイコン">
                <label>カート</label>
            </a>
        </div>
    </div>

    <!-- 下段：検索フォーム -->
    <form action="../フロント/G-9_search-result.php" method="GET" class="bottom-row">
        
        <div class="search-container">
            <input type="text" name="keyword" placeholder="何をお探しですか？">
            
            <!-- 検索ボタン (kensaku.png を使用) -->
            <button type="submit" class="search-button">
                <img src="../img/kensaku.png" alt="検索">
            </button>
        </div>

    </form>
</header>