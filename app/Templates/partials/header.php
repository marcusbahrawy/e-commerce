<?php
$headerMenuItems = $headerMenuItems ?? [];
$siteName = $siteName ?? 'Motorleaks';
$headerCategories = $headerCategories ?? [];
?>
<header class="header">
    <div class="container header__inner">
        <a href="<?= url('/') ?>" class="header__logo" aria-label="<?= e($siteName) ?> hjem"><?= e($siteName) ?></a>
        <nav class="header__nav" aria-label="Hovednavigasjon">
            <a href="<?= url('/') ?>">Hjem</a>
            <?php if (!empty($headerCategories)): ?>
            <div class="header__nav-dropdown">
                <button type="button" class="header__nav-dropdown-btn" aria-expanded="false" aria-haspopup="true" data-dropdown="categories">Kategorier</button>
                <div class="header__nav-dropdown-panel" id="dropdown-categories" hidden>
                    <ul class="header__nav-megamenu">
                        <?php foreach ($headerCategories as $root): ?>
                        <li class="header__nav-megamenu-item">
                            <a href="<?= url('/kategori/' . e($root['slug'])) ?>"><?= e($root['name']) ?></a>
                            <?php if (!empty($root['children'])): ?>
                            <ul>
                                <?php foreach ($root['children'] as $child): ?>
                                <li><a href="<?= url('/kategori/' . e($root['slug']) . '/' . e($child['slug'])) ?>"><?= e($child['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            <div class="header__search-wrap">
                <form method="get" action="<?= url('/sok') ?>" class="header__search-form" role="search">
                    <label for="header-search" class="visually-hidden">Søk etter deler</label>
                    <input type="search" id="header-search" name="q" class="header__search-input" placeholder="Søk..." autocomplete="off" data-typeahead>
                    <button type="submit" class="header__search-btn">Søk</button>
                </form>
                <div class="header__typeahead" id="header-typeahead" aria-live="polite" hidden></div>
            </div>
            <a href="<?= url('/sok') ?>">Alle søk</a>
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
