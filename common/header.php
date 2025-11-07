<!-- /common/header.php -->
<header>
    <!-- 上段：ロゴ、カート、会員情報 -->
    <div class="top">
        
        <!-- ロゴ (仮：ホームへのリンク) -->
        <div class="header-logo">
            <a href="G-8_home.php">
                <!-- imgフォルダは ../img/ にあると仮定 -->
                <!-- ★ロゴ画像のサイズ指定もCSS側で行うためインラインスタイルを削除（または .header-logo img としてCSSで指定） -->
                <img src="../img/NishimuraOnline.png" alt="ロゴ" class="logo-image">
            </a>
        </div>

        <!-- ボタン類 (カート・会員情報) -->
        <div class="header-button">

            <!-- カート -->
            <div class="header-cart">
                <a href="G-10_cart.php">
                    <!-- ★インラインスタイルがないことを確認 -->
                    <img src="../img/cart.png" alt="カート">
                    <label>カート</label>
                </a>
            </div>

            <!-- 会員情報 (アイコン) -->
            <div class="header-user" style="margin-left: 15px;">
                <a href="G-4_member-information.php">
                    <!-- ★★★ ここにあった style="..." を削除 ★★★ -->
                    <img src="../img/icon.png" alt="会員情報">
                </a>
            </div>

        </div>
    </div>
    <!-- (下段：検索フォーム...は省略) -->
</header>