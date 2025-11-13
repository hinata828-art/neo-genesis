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
            
                <a href="../フロント/G-4_member-information.php">
                    <img src="../img/icon.png" alt="会員情報" class="user-icon"> 
                </a>

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
</header>