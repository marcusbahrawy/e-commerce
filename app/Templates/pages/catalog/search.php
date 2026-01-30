<?php
$query = $query ?? '';
$products = $products ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 0;
$catalog = $catalog ?? null;
?>
<div class="catalog catalog--search">
    <header class="catalog__header">
        <h1 class="catalog__title">Søk<?= $query !== '' ? ': «' . e($query) . '»' : '' ?></h1>
        <form method="get" action="<?= url('/sok') ?>" class="catalog__search-form" style="margin: 1rem 0;">
            <input type="search" name="q" value="<?= e($query) ?>" placeholder="Søk i produkter..." class="input" style="max-width: 300px; padding: 0.5rem;">
            <button type="submit" class="btn btn--primary">Søk</button>
        </form>
    </header>
    <?php if ($query === ''): ?>
    <p>Skriv inn et søkeord og trykk Søk.</p>
    <?php elseif ($total === 0): ?>
    <p class="catalog__empty">Ingen produkter funnet for «<?= e($query) ?>».</p>
    <?php else: ?>
    <p class="catalog__count">Fant <?= $total ?> <?= $total === 1 ? 'produkt' : 'produkter' ?>.</p>
    <ul class="product-grid">
        <?php foreach ($products as $product): ?>
        <li><?php require dirname(__DIR__, 2) . '/components/product-card.php'; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($totalPages > 1): ?>
    <nav class="catalog__pagination" style="margin-top: 1.5rem;">
        <?php
        $baseUrl = url('/sok') . '?q=' . rawurlencode($query);
        if ($page > 1): ?>
        <a href="<?= $baseUrl . '&page=' . ($page - 1) ?>">← Forrige</a>
        <?php endif; ?>
        <span style="margin: 0 1rem;">Side <?= $page ?> av <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
        <a href="<?= $baseUrl . '&page=' . ($page + 1) ?>">Neste →</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>
