<?php $brands = $brands ?? []; $productCounts = $productCounts ?? []; ?>
<p><a href="<?= url('/admin/merker/ny') ?>" class="btn btn--primary">Nytt merke</a></p>
<table class="admin-table" style="width:100%; border-collapse: collapse; margin-top: 1rem;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">ID</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Navn</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Slug</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Produkter</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handlinger</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($brands as $b): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int) $b['id'] ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($b['name'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($b['slug'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int) ($productCounts[(int)$b['id']] ?? 0) ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                <a href="<?= url('/admin/merker/' . (int)$b['id'] . '/rediger') ?>">Rediger</a>
                <?php if (($productCounts[(int)$b['id']] ?? 0) === 0): ?>
                <form method="post" action="<?= url('/admin/merker/' . (int)$b['id'] . '/slett') ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;padding:0;" onclick="return confirm('Slette dette merket?');">Slett</button></form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($brands)): ?>
<p>Ingen merker ennå. <a href="<?= url('/admin/merker/ny') ?>">Opprett merke</a> for å kunne velge merke på produkter.</p>
<?php endif; ?>
