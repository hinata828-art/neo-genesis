<header class="header">
  <!-- 左側：ハンバーガーメニュー＋タイトル -->
  <div class="header-left">
    <div class="hamburger-menu">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <h1>ニシムラ Online</h1>
  </div>

  

  <!-- スライドメニュー -->
  <nav id="sideMenu" class="side-menu">
    <ul>
        <li><a href="../バック/G-20_customer-management.php">顧客管理</a></li>
        <li><a href="/バック/G-22_product.php">商品管理</a></li>
    </ul>
  </nav>

  <!-- メニューを閉じるための黒背景 -->
  <div id="overlay" class="menu-overlay"></div>
</header>

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