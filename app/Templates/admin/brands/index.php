<?php $brands = $brands ?? []; $productCounts = $productCounts ?? []; ?>
<div class="admin-page-actions">
    <a href="<?= url('/admin/merker/ny') ?>" class="btn btn--primary">Nytt merke</a>
</div>
<?php if (empty($brands)): ?>
<p class="admin-empty">Ingen merker ennå. <a href="<?= url('/admin/merker/ny') ?>">Opprett merke</a> for å kunne velge merke på produkter.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Navn</th>
                <th>Slug</th>
                <th>Produkter</th>
                <th class="table__actions">Handlinger</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($brands as $b): ?>
            <tr>
                <td><?= (int) $b['id'] ?></td>
                <td><?= e($b['name'] ?? '') ?></td>
                <td><?= e($b['slug'] ?? '') ?></td>
                <td><?= (int) ($productCounts[(int)$b['id']] ?? 0) ?></td>
                <td class="table__actions">
                    <a href="<?= url('/admin/merker/' . (int)$b['id'] . '/rediger') ?>" class="btn btn--ghost btn--sm">Rediger</a>
                    <?php if (($productCounts[(int)$b['id']] ?? 0) === 0): ?>
                    <form method="post" action="<?= url('/admin/merker/' . (int)$b['id'] . '/slett') ?>"><?= csrf_field() ?><button type="submit" class="btn btn--danger btn--sm" onclick="return confirm('Slette dette merket?');">Slett</button></form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
