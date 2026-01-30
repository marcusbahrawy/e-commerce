<?php
$category = $category ?? [];
$breadcrumbs = $breadcrumbs ?? [];
$subcategories = $subcategories ?? [];
$products = $products ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$sort = $sort ?? 'relevance';
$perPage = 24;
$from = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
$to = min($page * $perPage, $total);
$categoryPath = !empty($breadcrumbs) ? end($breadcrumbs)['path'] : ($category['slug'] ?? '');
?>
<div class="catalog">
    <?php require dirname(__DIR__, 2) . '/components/breadcrumbs.php'; ?>
    <header class="catalog__header">
        <h1 class="catalog__title"><?= e($category['name'] ?? '') ?></h1>
        <?php if (!empty($category['description_html'])): ?>
        <div class="catalog__intro"><?= $category['description_html'] ?></div>
        <?php endif; ?>
    </header>
    <?php if (!empty($subcategories)): ?>
    <section class="catalog__subs">
        <h2 class="visually-hidden">Underkategorier</h2>
        <ul class="category-chips">
            <?php foreach ($subcategories as $sub): ?>
            <li><a href="<?= e(url('/kategori/' . ($categoryPath ? $categoryPath . '/' : '') . ($sub['slug'] ?? ''))) ?>"><?= e($sub['name'] ?? '') ?></a></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>
    <div class="catalog__meta">
        <p class="catalog__count">Viser <?= $from ?>–<?= $to ?> av <?= $total ?> produkter</p>
    </div>
    <?php if (empty($products)): ?>
    <p class="catalog__empty">Ingen produkter i denne kategorien.</p>
    <?php else: ?>
    <ul class="product-grid">
        <?php foreach ($products as $product): ?>
        <li><?php require dirname(__DIR__, 2) . '/components/product-card.php'; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Paginering">
        <ul class="pagination__list">
            <?php if ($page > 1): ?>
            <li><a href="<?= e(url('/kategori/' . $categoryPath . '?page=' . ($page - 1) . '&sort=' . $sort)) ?>">Forrige</a></li>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li>
                <?php if ($p === $page): ?>
                <span aria-current="page"><?= $p ?></span>
                <?php else: ?>
                <a href="<?= e(url('/kategori/' . $categoryPath . '?page=' . $p . '&sort=' . $sort)) ?>"><?= $p ?></a>
                <?php endif; ?>
            </li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <li><a href="<?= e(url('/kategori/' . $categoryPath . '?page=' . ($page + 1) . '&sort=' . $sort)) ?>">Neste</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>
