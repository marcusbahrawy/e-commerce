<?php $products = $products ?? []; ?>
<div class="admin-page-actions">
    <a href="<?= url('/admin/produkter/ny') ?>" class="btn btn--primary">Nytt produkt</a>
</div>
<?php if (empty($products)): ?>
<p class="admin-empty">Ingen produkter ennå.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tittel</th>
                <th>Slug</th>
                <th style="text-align: right;">Pris</th>
                <th>Status</th>
                <th class="table__actions">Handlinger</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= (int) $p['id'] ?></td>
                <td><?= e($p['title'] ?? '') ?></td>
                <td><?= e($p['slug'] ?? '') ?></td>
                <td style="text-align: right;"><?= e(\App\Support\Money::format((int)($p['price_from_ore'] ?? 0))) ?></td>
                <td>
                    <?php if (!empty($p['deleted_at'])): ?>
                    <span class="badge badge--error">Slettet</span>
                    <?php elseif ((int)($p['is_active'] ?? 0)): ?>
                    <span class="badge badge--success">Aktiv</span>
                    <?php else: ?>
                    <span class="badge badge--neutral">Inaktiv</span>
                    <?php endif; ?>
                </td>
                <td class="table__actions">
                    <?php if (empty($p['deleted_at'])): ?>
                    <a href="<?= url('/admin/produkter/' . (int)$p['id'] . '/rediger') ?>" class="btn btn--ghost btn--sm">Rediger</a>
                    <form method="post" action="<?= url('/admin/produkter/' . (int)$p['id'] . '/slett') ?>"><?= csrf_field() ?><button type="submit" class="btn btn--danger btn--sm" onclick="return confirm('Slette dette produktet?');">Slett</button></form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
