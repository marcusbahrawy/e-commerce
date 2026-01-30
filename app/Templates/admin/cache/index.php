<?php if (isset($_GET['ok'])): ?>
<div class="alert alert--success" style="margin-bottom: var(--space-4);">Sidecache er tømt.</div>
<?php endif; ?>
<p class="text-muted" style="margin-bottom: var(--space-4);">Sidecache lagrer HTML for forsiden, kategorier, produkter og CMS-sider i 15 minutter. Ved oppdatering av produkter, kategorier eller sider tømes cachen automatisk.</p>
<p style="margin-bottom: var(--space-4);">Du kan tømme cachen manuelt når du vil:</p>
<form method="post" action="<?= url('/admin/cache/purge') ?>">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn--primary">Tøm sidecache</button>
</form>
