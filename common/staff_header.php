<header class="staff-header">

  <!-- 左側：ハンバーガーメニュー＋タイトル -->
  <div class="staff-header-left">
    <div class="staff-hamburger-menu">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <h1>ニシムラ Online</h1>
  </div>

  <!-- 右側：ユーザーアイコン画像 -->
  <div class="staff-user-icon">
    <img src="../img/icon.png" alt="ユーザーアイコン">
  </div>

</header>


<!-- スライドメニュー -->
<nav id="sideMenu" class="staff-side-menu">
    <ul>
        <li><a href="../バック/.php">顧客管理</a></li>
        <li><a href="../バック/G-22_product.php">商品管理</a></li>
    </ul>
</nav>

<!-- メニューを閉じるための黒背景 -->
<div id="overlay" class="staff-menu-overlay"></div>


<script>
    const menu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");
    const burger = document.querySelector(".staff-hamburger-menu");

    burger.addEventListener("click", function () {
        menu.classList.toggle("open");
        overlay.classList.toggle("show");
    });

    overlay.addEventListener("click", function () {
        menu.classList.remove("open");
        overlay.classList.remove("show");
    });
</script>

<!-- ヘッダーの下の余白 -->
<div class="staff-header-space"></div>
