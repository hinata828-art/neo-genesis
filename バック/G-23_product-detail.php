<div class="history-container">
    <!-- 発注・入荷履歴 -->
    <div class="history-box order-history-box">
        <label>発注・入荷履歴</label>
        <?php if ($orderHistory): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>発注日</th>
                        <th>入荷日</th>
                        <th>入荷数</th>
                        <th>担当者</th>
                        <th>備考</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderHistory as $oh): ?>
                        <tr>
                            <td><?= date("n/j", strtotime($oh['order_date'])) ?></td>
                            <td><?= $oh['arrival_date'] ? date("n/j", strtotime($oh['arrival_date'])) : '未入荷' ?></td>
                            <td><?= htmlspecialchars($oh['arrival_quantity']) ?>台</td>
                            <td><?= htmlspecialchars($oh['staff_name'] ?? '-') ?></td>
                            <td><?= nl2br(htmlspecialchars($oh['note'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>発注・入荷履歴はありません。</p>
        <?php endif; ?>
    </div>

    <!-- 購入履歴 -->
    <div class="history-box">
        <label>購入履歴</label>
        <?php if ($history): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>購入日</th>
                        <th>数量</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= date("n/j", strtotime($h['transaction_date'])) ?></td>
                            <td><?= htmlspecialchars($h['quantity']) ?>台</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>購入履歴はありません。</p>
        <?php endif; ?>
    </div>
</div>

<div class="button-area">
    <a class="btn" href="G-24_product-arrival.php?product_id=<?= $product_id ?>">入荷登録</a>
    <a class="btn-cancel" href="G-22_product.php">戻る</a>
</div>
