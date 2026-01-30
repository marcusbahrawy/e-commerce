<?php $products = $products ?? []; ?>
<p><a href="<?= url('/admin/produkter/ny') ?>" class="btn btn--primary">Nytt produkt</a></p>
<table class="admin-table" style="width:100%; border-collapse: collapse; margin-top: 1rem;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">ID</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Tittel</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Slug</th>
            <th style="text-align:right; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Pris</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Status</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handlinger</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int) $p['id'] ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($p['title'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($p['slug'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: right;"><?= e(\App\Support\Money::format((int)($p['price_from_ore'] ?? 0))) ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= !empty($p['deleted_at']) ? 'Slettet' : ((int)($p['is_active'] ?? 0) ? 'Aktiv' : 'Inaktiv') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                <?php if (empty($p['deleted_at'])): ?>
                <a href="<?= url('/admin/produkter/' . (int)$p['id'] . '/rediger') ?>">Rediger</a>
                <form method="post" action="<?= url('/admin/produkter/' . (int)$p['id'] . '/slett') ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;padding:0;">Slett</button></form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($products)): ?>
<p>Ingen produkter ennå.</p>
<?php endif; ?>
