<?php
// セッションを開始
session_start();

// ★ ログイン済みの人はホームページに飛ばす（無限リダイレクト防止）
if (isset($_SESSION['customer']['id'])) {
    header('Location: G-8_home.php');
    exit();
}

// エラーメッセージを格納する変数を初期化
$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 1) {
        $error_message = '必須項目（名前、メール、パスワード）をすべて入力してください。';
    } else if ($_GET['error'] == 2) {
        $error_message = 'パスワードは8文字以上で入力してください。';
    } else if ($_GET['error'] == 3) {
        $error_message = 'そのメールアドレスは既に使用されています。';
    }
}
?>
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

  <!-- ★ action属性が G-3_customer-complete.php になっていることを確認 -->
  <form method="post" action="G-2_customer-complete.php">
    <h1>会員登録</h1>
    <fieldset>
      <legend>新規アカウント作成</legend>

      <!-- ★ エラーメッセージ表示欄 -->
      <?php if (!empty($error_message)): ?>
          <p style="color:red; font-weight: bold;"><?php echo $error_message; ?></p>
      <?php endif; ?>

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