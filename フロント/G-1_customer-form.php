<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン</title>
  <link rel="stylesheet" href="../css/G-1_customer-form.css">
</head>
<body>
      
  <img src="../img/NishimuraOnline.png" alt="企業ロゴ" class="logo">

  <!-- ★修正：action属性を、G-8_home.php から G-1_login-process.php に変更 -->
  <form action="G-1_login-process.php" method="post">
    <h1>ログイン</h1>
    <fieldset>
      <label for="email">メールアドレス</label>
      <input id="email" name="email" type="email" required placeholder="you@example.com">
 
      <label for="password">パスワード</label>
      <input id="password" name="password" type="password" required placeholder="パスワードを入力">
 
      <button type="submit">ログイン</button>
 
      <p>
        アカウントをお持ちでない方は<br>
        <a href="G-2_customer-entry.php">会員登録はこちら</a>
      </p>

      <?php
      // エラーメッセージの表示
      if (isset($_GET['error'])) {
          if ($_GET['error'] == 1) {
              echo '<p style="color:red;">メールアドレスとパスワードを入力してください。</p>';
          } else if ($_GET['error'] == 2) {
              echo '<p style="color:red;">メールアドレスまたはパスワードが間違っています。</p>';
          }
      }
      ?>
    </fieldset>
  </form>
 
</body>
</html>