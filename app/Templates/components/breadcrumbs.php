<?php
$items = $items ?? [];
if (empty($items)) {
    return;
}
?>
<nav class="breadcrumbs" aria-label="Brødsmule">
    <ol class="breadcrumbs__list">
        <li class="breadcrumbs__item"><a href="<?= url('/') ?>">Hjem</a></li>
        <?php foreach ($items as $i => $item): ?>
        <li class="breadcrumbs__item">
            <?php if ($i < count($items) - 1): ?>
            <a href="<?= e(url('/kategori/' . ($item['path'] ?? $item['slug'] ?? ''))) ?>"><?= e($item['name'] ?? '') ?></a>
            <?php else: ?>
            <span aria-current="page"><?= e($item['name'] ?? '') ?></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ol>
</nav>
