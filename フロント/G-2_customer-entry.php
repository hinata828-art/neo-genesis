<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>会員登録</title>
  <!-- 外部CSSを読み込む -->
  <link rel="stylesheet" href="../css/G-2_customer-entry.css">
</head>
<body>

  <!-- 🔵 ロゴ部分 -->
  <img src="../img/NishimuraOnline.png" alt="企業ロゴ" class="logo">

  <form method="post" action="/register">
    <h1>会員登録</h1>
    <fieldset>
      <legend>新規アカウント作成</legend>

      <label for="name">お名前</label>
      <input id="name" name="name" type="text" required placeholder="山田 太郎">

      <label for="email">メールアドレス</label>
      <input id="email" name="email" type="email" required placeholder="you@example.com">

      <label for="password">パスワード</label>
      <input id="password" name="password" type="password" required placeholder="8文字以上" minlength="8">

      <label for="birth">生年月日（任意）</label>
      <input id="birth" name="birth" type="date">

      <button type="submit">登録する</button>

      <p>
        すでにアカウントをお持ちの方は<br>
        <a href="G-1_customer-form.php">ログインはこちら</a>
      </p>
    </fieldset>
  </form>

</body>
</html>
