<?php
// 1. セッションとエラー表示
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. DB接続
require '../common/db_connect.php'; // $pdo が定義されていると仮定

// 3. 顧客IDの取得
if (isset($_SESSION['customer']['id'])) {
    $customer_id = $_SESSION['customer']['id'];
} else {
    echo "ログインしていません。";
    exit;
}

// 4. データの初期化
$customer_info = null;
$error_message = '';

try {
    // 5. SQL: 会員情報と住所情報を取得
    // (G-4にならい、customerテーブルとaddressテーブルをJOIN)
    $sql_customer = "SELECT c.customer_name, c.phone_number, c.payment_method, c.birth_date,
                            a.postal_code, a.prefecture, a.city, a.address_line
                       FROM customer AS c
                       LEFT JOIN address AS a ON c.customer_id = a.customer_id
                       WHERE c.customer_id = :id
                       LIMIT 1";
    
    $stmt_customer = $pdo->prepare($sql_customer);
    $stmt_customer->bindValue(':id', $customer_id, PDO::PARAM_INT);
    $stmt_customer->execute();
    $customer_info = $stmt_customer->fetch(PDO::FETCH_ASSOC);

    if (!$customer_info) {
        throw new Exception('顧客情報が見つかりません。');
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
}

// 支払方法の選択肢
$payment_options = ['クレジット', '代金引換', '銀行振込', 'コンビニ決済'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員情報変更</title>
    <link rel="stylesheet" href="../css/header.css"> 
    <link rel="stylesheet" href="../css/G-5_member-change.css">
</head>
<body>
    
    <?php require '../common/header.php'; ?><hr>

    <div class="container">

        <header class="header">
            <a href="G-4_member-information.php" class="back-link"><img src="../img/modoru.png" alt="戻る"></a>
            <h1 class="header-title">会員情報変更</h1>
            <span class="header-dummy"></span>
        </header>

        <main class="main-content">
            <?php if ($error_message): ?>
                <div class="error-box"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if ($customer_info): ?>
                <form action="G-4_member-information.php" method="post" class="change-form">
                    
                    <div class="form-group">
                        <label for="name">お名前</label>
                        <div class="input-wrapper">
                            <input type="text" id="name" name="customer_name" value="<?php echo htmlspecialchars($customer_info['customer_name'] ?? ''); ?>">
                            <img src="../img/insert.png" alt="編集" class="edit-icon">
                        </div>
                    </div>

                    <h2 class="section-label">ご住所</h2>
                    
                    <div class="form-group">
                        <label for="postal_code">郵便番号</label>
                        <div class="input-wrapper">
                            <input type="text" id="postal_code" name="postal_code" placeholder="000-0000" value="<?php echo htmlspecialchars($customer_info['postal_code'] ?? ''); ?>">
                            <img src="../img/insert.png" alt="編集" class="edit-icon">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="prefecture">都道府県</label>
                        <div class="input-wrapper">
                            <input type="text" id="prefecture" name="prefecture" value="<?php echo htmlspecialchars($customer_info['prefecture'] ?? ''); ?>">
                            <img src="../img/insert.png" alt="編集" class="edit-icon">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="city">市区町村</label>
                        <div class="input-wrapper">
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($customer_info['city'] ?? ''); ?>">
                            <img src="../img/insert.png" alt="編集" class="edit-icon">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address_line">番地・建物名</label>
                        <div class="input-wrapper">
                            <input type="text" id="address_line" name="address_line" placeholder="00-00" value="<?php echo htmlspecialchars($customer_info['address_line'] ?? ''); ?>">
                            <img src="../img/insert.png" alt="編集" class="edit-icon">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">電話番号</label>
                        <div class="input-wrapper">
                            <input type="tel" id="phone" name="phone_number" placeholder="090-0000-0000" value="<?php echo htmlspecialchars($customer_info['phone_number'] ?? ''); ?>">
                            <img src="../img/insert.png" alt="編集" class="edit-icon">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment">お支払方法</label>
                        <div class="input-wrapper select-wrapper">
                            <select id="payment" name="payment_method">
                                <?php foreach ($payment_options as $option): ?>
                                    <option value="<?php echo $option; ?>" 
                                        <?php if ($option === ($customer_info['payment_method'] ?? '')): ?>
                                            selected
                                        <?php endif; ?>>
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                    </div>

                    <div class="form-group">
                        <label for="birthdate">生年月日</label>
                        <div class="input-wrapper">
                            <input type="text" id="birthdate" name="birth_date" placeholder="2000/01/01" value="<?php echo htmlspecialchars($customer_info['birth_date'] ?? ''); ?>">
                            <img src="../img/insert.png" alt="編集" class="edit-icon">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit">会員情報変更確定</button>

                </form>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>