<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン</title>
  <!-- 外部CSSを読み込む -->
  <link rel="stylesheet" href="../css/G-1_customer-form.css">

</head>
<body>
     
  <!-- 🔵 ロゴ画像部分 -->
  <img src="../img/NishimuraOnline.png" alt="企業ロゴ" class="logo">

  <form action="G-3_home.php" method="post">
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
    </fieldset>
  </form>
 
</body>
</html>
 
 