<?php
// パンくずリストの配列を受け取る（存在しない場合は空配列）
$breadcrumbs = $breadcrumbs ?? [];

// ホームは必ず先頭に追加
array_unshift($breadcrumbs, ['name' => 'ホーム', 'url' => '../フロント/G-8_home.php']);
?>

<nav class="breadcrumb">
    <ul>
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <li>
                <?php if (!empty($crumb['url']) && $index !== array_key_last($breadcrumbs)): ?>
                    <a href="<?= htmlspecialchars($crumb['url'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($crumb['name'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>