<?php
// 1. セッションを開始
session_start();

// 2. DB接続ファイルを読み込む
require '../common/db_connect.php';

// 3. POSTデータ（メールとパスワード）を受け取る
if (empty($_POST['email']) || empty($_POST['password'])) {
    // データが送信されていない場合は、ログイン画面に戻す
    header('Location: G-1_customer-form.php?error=1');
    exit();
}

$email = $_POST['email'];
$password = $_POST['password'];

try {
    // 4. データベースからメールアドレスが一致する顧客を探す
    $sql = "SELECT * FROM customer WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    // 5. 顧客が見つかり、かつパスワードが一致するか確認
    // (password_verifyは、DBにハッシュ化されたパスワードが保存されている場合に使います)
    if ($customer && password_verify($password, $customer['password'])) {
        
        // 6. ★★★ 認証成功：セッションに顧客情報を保存 ★★★
        // G-4_member-information.php が探しているキー名 'customer' と 'id' に合わせる
        $_SESSION['customer'] = [
            'id' => $customer['customer_id'],
            'name' => $customer['customer_name']
        ];
        
        // 7. ホームページにリダイレクト
        header('Location: G-8_home.php');
        exit();
        
    } else {
        // 8. 認証失敗：パスワードが違うか、ユーザーが存在しない
        header('Location: G-1_customer-form.php?error=2');
        exit();
    }

} catch (PDOException $e) {
    echo 'データベースエラー: ' . $e->getMessage();
}
?>