<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>社員ログイン</title>
  <link rel="stylesheet" href="../css/admin_login.css">
  <style>
    h1 { color: #d32f2f; }
    body { background-color: #f8f8f8; }
  </style>
</head>
<body>

  <img src="../img/NishimuraOnline.png" alt="企業ロゴ" class="logo">

  <form action="G-19_admin-login-process.php" method="post">
    <h1>社員ログイン</h1>
    <fieldset>
      <label for="staff_id">社員ID</label>
      <input id="staff_id" name="staff_id" type="text" required placeholder="例: S00123">

      <label for="password">パスワード</label>
      <input id="password" name="password" type="password" required placeholder="パスワードを入力">

      <button type="submit">ログイン</button>

      <?php
      if (isset($_GET['error'])) {
          if ($_GET['error'] == 1) {
              echo '<p style="color:red;">社員IDとパスワードを入力してください。</p>';
          } else if ($_GET['error'] == 2) {
              echo '<p style="color:red;">社員IDまたはパスワードが間違っています。</p>';
          }
      }
      ?>
    </fieldset>
  </form>

</body>
</html>
