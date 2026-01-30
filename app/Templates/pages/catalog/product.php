<?php
$product = $product ?? [];
$images = $images ?? [];
$primaryImagePath = $primaryImagePath ?? null;
$priceFrom = (int) ($product['price_from_ore'] ?? 0);
$priceTo = (int) ($product['price_to_ore'] ?? 0);
?>
<div class="product-detail">
    <div class="product-detail__grid">
        <div class="product-detail__gallery">
            <?php if ($primaryImagePath): ?>
            <img src="<?= e(url('/' . ltrim($primaryImagePath, '/'))) ?>" alt="<?= e($product['title'] ?? '') ?>" class="product-detail__image" width="600" height="600">
            <?php else: ?>
            <div class="product-detail__image product-detail__image--placeholder" aria-hidden="true"></div>
            <?php endif; ?>
        </div>
        <div class="product-detail__info">
            <?php if (!empty($product['brand_name'])): ?>
            <p class="product-detail__brand"><a href="<?= e(url('/kategori/' . ($product['brand_slug'] ?? ''))) ?>"><?= e($product['brand_name']) ?></a></p>
            <?php endif; ?>
            <h1 class="product-detail__title"><?= e($product['title'] ?? '') ?></h1>
            <?php if (!empty($product['sku'])): ?>
            <p class="product-detail__sku">Art.nr. <?= e($product['sku']) ?></p>
            <?php endif; ?>
            <p class="product-detail__price">
                <?php if ($priceFrom > 0): ?>
                <?= e(\App\Support\Money::format($priceFrom)) ?>
                <?php if ($priceTo > 0 && $priceTo !== $priceFrom): ?> – <?= e(\App\Support\Money::format($priceTo)) ?><?php endif; ?>
                <?php else: ?>
                Pris på forespørsel
                <?php endif; ?>
            </p>
            <p class="product-detail__actions">
                <form method="post" action="<?= e(url('/handlekurv')) ?>" class="product-detail__add-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_slug" value="<?= e($product['slug'] ?? '') ?>">
                    <input type="hidden" name="qty" value="1">
                    <input type="hidden" name="redirect" value="<?= e(url('/handlekurv')) ?>">
                    <button type="submit" class="btn btn--primary">Legg i handlekurv</button>
                </form>
            </p>
            <?php if (!empty($product['description_short'])): ?>
            <div class="product-detail__short"><?= e($product['description_short']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!empty($product['description_html'])): ?>
    <div class="product-detail__content">
        <h2>Beskrivelse</h2>
        <div class="prose"><?= $product['description_html'] ?></div>
    </div>
    <?php endif; ?>
</div>
