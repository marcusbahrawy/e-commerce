<p class="text-muted" style="margin-bottom: var(--space-5);">Velkommen til admin-panelet.</p>
<?php if (!empty($stats)): ?>
<div class="admin-cards">
    <?php if (isset($stats['orders_today'])): ?>
    <div class="admin-card">
        <div class="admin-card__value"><?= (int) $stats['orders_today'] ?></div>
        <div class="admin-card__label">Ordrer i dag</div>
    </div>
    <?php endif; ?>
    <?php if (isset($stats['orders_pending'])): ?>
    <div class="admin-card">
        <div class="admin-card__value"><?= (int) $stats['orders_pending'] ?></div>
        <div class="admin-card__label">Ventende ordrer</div>
    </div>
    <?php endif; ?>
    <?php if (isset($stats['products_count'])): ?>
    <div class="admin-card">
        <div class="admin-card__value"><?= (int) $stats['products_count'] ?></div>
        <div class="admin-card__label">Produkter</div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<div class="admin-form-actions">
    <a href="<?= url('/') ?>" class="btn btn--primary">Gå til butikken</a>
</div>
