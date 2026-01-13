<?php
// セッション開始など必要であれば記述
session_start();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会社概要 | NISHIMURAelectronics</title>
    <link rel="stylesheet" href="../css/G-28_kaisyagaiyou.css">
</head>
<body>

    <?php // include '../common/header.php'; ?>
    <header style="background:#ccc; padding:10px; text-align:center;">
        <h1>NISHIMURAelectronics</h1>
    </header>

    <div class="container">
        <main class="main-content">
            
            <h1 class="page-title">会社概要</h1>
            <p class="page-subtitle">Company Profile</p>

            <section class="ceo-message">
                <h2>代表挨拶</h2>
                <div class="message-box">
                    <div class="message-text">
                        <img src="../img/nisimura.jpg" alt="西村 陽奈太">
                        <p>
                            「家電で、明日をちょっと便利に。」<br>
                            私たちNISHIMURAelectronicsは、博多の地から最新のテクノロジーをお届けすることを使命としています。<br>
                            買うだけが選択肢ではない。「レンタル」という新しい所有のカタチを通じて、お客様のライフスタイルに革命を起こします。<br>
                            従業員5人の精鋭チームで、世界（まずは博多区）を驚かせるサービスを提供し続けます。
                        </p>
                        <p class="ceo-name">
                            代表取締役社長 <span>西村 陽奈太</span>
                        </p>
                    </div>
                    </div>
            </section>

            <section class="company-info">
                <h2>基本情報</h2>
                <table class="info-table">
                    <tr>
                        <th>会社名</th>
                        <td>NISHIMURAelectronics</td>
                    </tr>
                    <tr>
                        <th>所在地</th>
                        <td>
                            〒812-0016<br>
                            福岡県福岡市博多区博多駅南2-12-32
                        </td>
                    </tr>
                    <tr>
                        <th>代表者</th>
                        <td>代表取締役 西村 陽奈太</td>
                    </tr>
                    <tr>
                        <th>設立</th>
                        <td>2025年1月（予定）</td>
                    </tr>
                    <tr>
                        <th>資本金</th>
                        <td>1,000万円</td>
                    </tr>
                    <tr>
                        <th>事業内容</th>
                        <td>
                            ・最新家電の販売<br>
                            ・家電レンタルサービスの運営<br>
                            ・WEBサービスの企画・開発・運営
                        </td>
                    </tr>
                    <tr>
                        <th>従業員数</th>
                        <td>5名（アルバイト含む）</td>
                    </tr>
                </table>
            </section>

            <section class="access-map">
                <h2>アクセス</h2>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.9566378465646!2d130.4208076757976!3d33.5805723733383!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x354191c7e6f8f55f%3A0x12345678dummy!2z56aP5bKh55yM56aP5bKh5biC5Y2a5aSa5Yy65Y2a5aSa6aeF5Y2X77yS5LiB55uu77yR77yS4oiS77yT77yS!5e0!3m2!1sja!2sjp!4v1700000000000!5m2!1sja!2sjp" 
                        width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </section>

        </main>
    </div>

    <?php include 'footer.php'; ?> 

</body>
</html>