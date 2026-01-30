<?php $categories = $categories ?? []; ?>
<div class="admin-page-actions">
    <a href="<?= url('/admin/kategorier/ny') ?>" class="btn btn--primary">Ny kategori</a>
</div>
<?php if (empty($categories)): ?>
<p class="admin-empty">Ingen kategorier ennå.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Navn</th>
                <th>Slug</th>
                <th>Forelder</th>
                <th>Status</th>
                <th class="table__actions">Handlinger</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td><?= (int) $c['id'] ?></td>
                <td><?= e($c['name'] ?? '') ?></td>
                <td><?= e($c['slug'] ?? '') ?></td>
                <td><?= empty($c['parent_id']) ? '—' : (int) $c['parent_id'] ?></td>
                <td>
                    <?php if ((int)($c['is_active'] ?? 0)): ?>
                    <span class="badge badge--success">Aktiv</span>
                    <?php else: ?>
                    <span class="badge badge--neutral">Inaktiv</span>
                    <?php endif; ?>
                </td>
                <td class="table__actions">
                    <a href="<?= url('/admin/kategorier/' . (int)$c['id'] . '/rediger') ?>" class="btn btn--ghost btn--sm">Rediger</a>
                    <form method="post" action="<?= url('/admin/kategorier/' . (int)$c['id'] . '/slett') ?>"><?= csrf_field() ?><button type="submit" class="btn btn--danger btn--sm" onclick="return confirm('Slette denne kategorien?');">Slett</button></form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
