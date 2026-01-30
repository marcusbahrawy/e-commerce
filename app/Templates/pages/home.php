<section class="hero">
    <div class="container">
        <h1 class="hero__title">Delebestilling på nett</h1>
        <p class="hero__lead">Scooter, moped, lett MC, ATV, hage, dekk og utstyr.</p>
        <div class="hero__cta">
            <a href="<?= url('/kategori') ?>" class="btn btn--primary">Se katalog</a>
            <a href="<?= url('/sok') ?>" class="btn btn--ghost">Søk etter deler</a>
        </div>
    </div>
</section>
<?php if (!empty($categories)): ?>
<section class="section">
    <div class="container">
        <h2 class="section__title">Kategorier</h2>
        <ul class="category-grid category-grid--home">
            <?php foreach ($categories as $cat): ?>
            <li><a href="<?= e(url('/kategori/' . ($cat['slug'] ?? ''))) ?>" class="category-card"><?= e($cat['name'] ?? '') ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>
<?php if (!empty($featured) && isset($catalog)): ?>
<section class="section">
    <div class="container">
        <h2 class="section__title">Utvalgte produkter</h2>
        <ul class="product-grid">
            <?php foreach ($featured as $product): ?>
            <li><?php $product = $product; require dirname(__DIR__) . '/components/product-card.php'; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>
<section class="section section--welcome">
    <div class="container">
        <h2 class="section__title">Velkommen til Motorleaks</h2>
        <p class="section__intro">Nettbutikken tilbyr rask levering og enkel bestilling av deler til scooter, moped, lett MC, ATV og mer.</p>
        <div class="welcome-card">
            <p>Finn deler til din sykkel eller maskin, bestill enkelt og få rask levering. Har du spørsmål? Ta gjerne kontakt via butikken.</p>
        </div>
    </div>
</section>
