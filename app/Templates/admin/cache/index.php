<?php if (isset($_GET['ok'])): ?>
<p style="color:#080;">Sidecache er tømt.</p>
<?php endif; ?>
<p>Sidecache lagrer HTML for forsiden, kategorier, produkter og CMS-sider i 15 minutter. Ved oppdatering av produkter, kategorier eller sider tømes cachen automatisk.</p>
<p>Du kan tømme cachen manuelt når du vil:</p>
<form method="post" action="<?= url('/admin/cache/purge') ?>">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn--primary">Tøm sidecache</button>
</form>
