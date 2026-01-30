<?php $headerMenuItems = $headerMenuItems ?? []; ?>
<header class="header">
    <div class="container header__inner">
        <a href="<?= url('/') ?>" class="header__logo" aria-label="Motorleaks hjem">Motorleaks</a>
        <nav class="header__nav" aria-label="Hovednavigasjon">
            <a href="<?= url('/') ?>">Hjem</a>
            <a href="<?= url('/sok') ?>">Søk</a>
            <?php foreach ($headerMenuItems as $item): ?>
            <?php if (empty($item['is_active'])) continue; ?>
            <?php $href = !empty($item['url']) ? $item['url'] : '#'; if (strpos($href, 'http') !== 0 && $href !== '#') { $href = url($href); } ?>
            <a href="<?= e($href) ?>"><?= e($item['label'] ?? '') ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="header__actions">
            <a href="<?= url('/konto') ?>">Min konto</a>
            <a href="<?= url('/handlekurv') ?>" class="header__cart" aria-label="Handlekurv">
                Handlekurv<?php $cart_count = (int) ($GLOBALS['cart_count'] ?? 0); if ($cart_count > 0): ?> <span class="header__cart-badge"><?= $cart_count ?></span><?php endif; ?>
            </a>
        </div>
    </div>
</header>
