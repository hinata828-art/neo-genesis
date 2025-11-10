<?php
// 1. セッションを開始
session_start();

// 2. デバッグ（エラー表示）設定 (開発中のみ)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 3. 共通のデータベース接続ファイルを読み込む
require '../common/db_connect.php'; // $pdo が定義されていると仮定

// 4. フォームから送信されたデータを受け取る
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password_plain = $_POST['password'] ?? ''; // 平文のパスワード
$birth = $_POST['birth'] ?? null;

// 5. バリデーション（簡易）
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

// 6. パスワードのハッシュ化（★セキュリティ上、必須★）
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

// 7. 生年月日が空文字の場合は NULL に変換
if ($birth === '') {
    $birth = null;
}

try {
    // 8. データベースに挿入するSQLを準備
    $sql = "INSERT INTO customer 
                (customer_name, email, password, birth_date, created_at) 
            VALUES 
                (:name, :email, :password, :birth, CURDATE())";
    
    $stmt = $pdo->prepare($sql);
    
    // 9. 値をバインド
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':password', $password_hashed, PDO::PARAM_STR);
    $stmt->bindValue(':birth', $birth, PDO::PARAM_STR); 
    
    // 10. SQLを実行
    $stmt->execute();

    // 11. 登録完了後、ログイン画面にリダイレクト (成功メッセージ付き)
    header('Location: G-1_customer-form.php?success=1');
    exit();

} catch (PDOException $e) {
    // 12. エラー処理
    if ($e->getCode() == 23000) {
        // 23000は一意制約違反 (メールアドレスが重複)
        header('Location: G-2_customer-entry.php?error=3');
    } else {
        // その他のDBエラー
        echo 'データベースエラー: ' . $e->getMessage();
    }
    exit();
}
?>