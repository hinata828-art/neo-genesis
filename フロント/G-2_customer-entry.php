<?php
// 1. セッションを開始
session_start();

// 2. 共通のDB接続ファイルを読み込む
require '../common/db_connect.php'; // $pdo が定義されていると仮定

// 3. フォームから送信されたデータを受け取る
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password_plain = $_POST['password'] ?? ''; // 平文のパスワード
$birth = $_POST['birth'] ?? null;

// 4. バリデーション（簡易）
if (empty($name) || empty($email) || empty($password_plain)) {
    // 必須項目が空の場合は、エラーメッセージと共に登録画面に戻す
    header('Location: G-2_customer-entry.php?error=1');
    exit();
}
if (strlen($password_plain) < 8) {
    // パスワードが8文字未満の場合
    header('Location: G-2_customer-entry.php?error=2');
    exit();
}

// 5. パスワードのハッシュ化（★セキュリティ上、必須★）
// データベースには元のパスワードではなく、ハッシュ化したものを保存します
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

// 6. 生年月日が空文字の場合は NULL に変換
if ($birth === '') {
    $birth = null;
}

try {
    // 7. データベースに挿入するSQLを準備
    // フォームにない項目（phone_number, payment_method）は省略し、
    // created_at には現在の日付 (CURDATE()) を入れます
    $sql = "INSERT INTO customer 
                (customer_name, email, password, birth_date, created_at) 
            VALUES 
                (:name, :email, :password, :birth, CURDATE())";
    
    $stmt = $pdo->prepare($sql);
    
    // 8. 値をバインド
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password_hashed, PDO::PARAM_STR);
    $stmt->bindValue(':birth', $birth, PDO::PARAM_STR); // NULLの可能性があるため STR
    
    // 9. SQLを実行
    $stmt->execute();

    // 10. 登録完了後、ログイン画面にリダイレクト
    // (G-3_customer-complete-display.php のような完了画面に飛ばしても良い)
    header('Location: G-1_customer-form.php?success=1');
    exit();

} catch (PDOException $e) {
    // エラー処理
    if ($e->getCode() == 23000) {
        // 23000は一意制約違反 (メールアドレスが重複している可能性が高い)
        header('Location: G-2_customer-entry.php?error=3');
    } else {
        // その他のDBエラー
        echo 'データベースエラー: ' . $e->getMessage();
    }
    exit();
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

  <form method="post" action="G-3_customer-complete.php">
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
