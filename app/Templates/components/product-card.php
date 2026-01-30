<?php
$product = $product ?? [];
$catalog = $catalog ?? null;
$imagePath = null;
if ($catalog && isset($product['id'])) {
    $imagePath = $catalog->getProductPrimaryImagePath((int) $product['id'], isset($product['primary_image_id']) && $product['primary_image_id'] !== null ? (int) $product['primary_image_id'] : null);
}
$price = (int) ($product['price_from_ore'] ?? 0);
$url = url('/produkt/' . ($product['slug'] ?? ''));
?>
<article class="product-card">
    <a href="<?= e($url) ?>" class="product-card__link">
        <div class="product-card__image-wrap">
            <?php if ($imagePath): ?>
            <img src="<?= e(url('/' . ltrim($imagePath, '/'))) ?>" alt="<?= e($product['title'] ?? '') ?>" class="product-card__image" loading="lazy" width="300" height="300">
            <?php else: ?>
            <div class="product-card__image product-card__image--placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        </div>
        <div class="product-card__body">
            <h3 class="product-card__title"><?= e($product['title'] ?? '') ?></h3>
            <div class="product-card__price-wrap">
                <span class="product-card__price price-tag"><?= $price > 0 ? e(\App\Support\Money::format($price)) : 'Pris på forespørsel' ?></span>
            </div>
        </div>
    </a>
    <div class="product-card__cta">
        <a href="<?= e($url) ?>" class="btn btn--primary btn--block btn--sm">Se produkt</a>
    </div>
</article>
