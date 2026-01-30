<?php
$cart = $cart ?? [];
$items = $items ?? [];
$subtotal_ore = $subtotal_ore ?? 0;
$item_count = $item_count ?? 0;
?>
<div class="cart-page">
    <div class="container">
        <h1 class="cart-page__title">Handlekurv</h1>
        <?php if (empty($items)): ?>
        <p class="cart-page__empty">Handlekurven er tom.</p>
        <p><a href="<?= url('/') ?>" class="btn btn--primary">Fortsett å handle</a></p>
        <?php else: ?>
        <div class="cart-page__grid">
            <ul class="cart-list">
                <?php foreach ($items as $item): ?>
                <li class="cart-list__item">
                    <div class="cart-list__info">
                        <a href="<?= e(url('/produkt/' . ($item['product_slug'] ?? ''))) ?>" class="cart-list__title"><?= e($item['title_snapshot'] ?? '') ?></a>
                        <p class="cart-list__price"><?= e(\App\Support\Money::format((int) ($item['price_ore_snapshot'] ?? 0))) ?> × <?= (int) $item['qty'] ?></p>
                    </div>
                    <div class="cart-list__total"><?= e(\App\Support\Money::format((int) $item['price_ore_snapshot'] * (int) $item['qty'])) ?></div>
                    <form method="post" action="<?= e(url('/handlekurv/fjern')) ?>" class="cart-list__remove">
                        <?= csrf_field() ?>
                        <input type="hidden" name="line_id" value="<?= (int) $item['id'] ?>">
                        <button type="submit" class="btn btn--ghost" aria-label="Fjern">Fjern</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="cart-summary">
                <p class="cart-summary__row">
                    <span>Sum</span>
                    <strong><?= e(\App\Support\Money::format($subtotal_ore)) ?></strong>
                </p>
                <p class="cart-summary__note">Frakt beregnes i kassen.</p>
                <a href="<?= url('/kasse') ?>" class="btn btn--primary btn--block">Til kassen</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
