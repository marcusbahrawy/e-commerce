<?php $pages = $pages ?? []; ?>
<div class="admin-page-actions">
    <a href="<?= url('/admin/sider/ny') ?>" class="btn btn--primary">Ny side</a>
</div>
<?php if (empty($pages)): ?>
<p class="admin-empty">Ingen sider ennå.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tittel</th>
                <th>Slug</th>
                <th>Status</th>
                <th class="table__actions">Handlinger</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages as $p): ?>
            <tr>
                <td><?= (int) $p['id'] ?></td>
                <td><?= e($p['title'] ?? '') ?></td>
                <td><?= e($p['slug'] ?? '') ?></td>
                <td>
                    <?php if ((int)($p['is_active'] ?? 0)): ?>
                    <span class="badge badge--success">Aktiv</span>
                    <?php else: ?>
                    <span class="badge badge--neutral">Inaktiv</span>
                    <?php endif; ?>
                </td>
                <td class="table__actions">
                    <a href="<?= url('/admin/sider/' . (int)$p['id'] . '/rediger') ?>" class="btn btn--ghost btn--sm">Rediger</a>
                    <form method="post" action="<?= url('/admin/sider/' . (int)$p['id'] . '/slett') ?>"><?= csrf_field() ?><button type="submit" class="btn btn--danger btn--sm" onclick="return confirm('Slette denne siden?');">Slett</button></form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
