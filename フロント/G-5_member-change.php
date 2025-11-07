<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <!-- スマホ拡大防止 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">    <title>会員情報</title>
    <link rel="stylesheet" href="G-4_G-5_member.css">
    <title>会員情報</title>

</head>
<body>
    <!--ヘッダー-->
    <header>
        <a href="G-8_home.php" class="">
            <img src="img/NishimuraOnline.png" alt="ニシムラon-lineロゴ" class="logo-img">
        </a>
        <a href="G-4_member-infomation.php" class="">
            <img src="img/icon.png" alt="人イラスト" class="icon-img">
        </a>
    </header>
  <hr>

  <!--メイン-->
  <h1>会員情報変更</h1>
  
  <div class="form-container">
    <label class="">お名前</label><br>
        <input type="text" name="name" value=""><br>
    <label class="">ご住所</label><br>
        <label class="">郵便番号</label><br>
            <input type="number" name="postalcode" value=""><br>
        <label class="">都道府県</label><br>
            <input type="text" name="address1" value=""><br>
        <label class="">市区町村</label><br>
            <input type="text" name="address2" value=""><br>
        <label class="">番地・建物名</label><br>
            <input type="text" name="address3" value=""><br>
        
    <label class="">電話番号</label><br>
        <input type="tel" name="tel" value=""><br>
    <label class="">お支払方法</label><br>
        <select id="payment" name="payment_method" onchange="togglePaymentFields()">
            <option value="">選択してください</option>
            <option value="credit">クレジットカード</option>
            <option value="convenience">コンビニ支払い</option>
            <option value="bank">銀行振り込み</option>
        </select><br>

    <!--クレジットカード情報入力フォーム-->
    <div id="credit-fields" style="display: none;">
        <label>カード番号：</label>
            <input type="text" name="card_number" placeholder="例：1234-5678-9012-3456"><br>
        <label>有効期限：</label>
            <input type="text" name="card_expiry" placeholder="例：12/29"><br>
        <label>セキュリティコード：</label>
            <input type="text" name="card_cvv" placeholder="例：123"><br>
    </div>
    
    <label class="">生年月日</label><br>
        <input type="date"><br>


    
  </div>
  <a href="G-4_member-infomation.php" class="">会員情報変更確定</a>

  <!--クレジットカードの時のみフォーム表示するようjs-->
  <script>
    function togglePaymentFields() {
    const selected = document.getElementById("payment").value;
    const creditFields = document.getElementById("credit-fields");

    if (selected === "credit") {
        creditFields.style.display = "block";
    } else {
        creditFields.style.display = "none";
    }
    }
  </script>

</body>
</html>