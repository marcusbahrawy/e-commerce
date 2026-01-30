<?php $pages = $pages ?? []; ?>
<p><a href="<?= url('/admin/sider/ny') ?>" class="btn btn--primary">Ny side</a></p>
<table class="admin-table" style="width:100%; border-collapse: collapse; margin-top: 1rem;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">ID</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Tittel</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Slug</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Status</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handlinger</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pages as $p): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int) $p['id'] ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($p['title'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($p['slug'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int)($p['is_active'] ?? 0) ? 'Aktiv' : 'Inaktiv' ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                <a href="<?= url('/admin/sider/' . (int)$p['id'] . '/rediger') ?>">Rediger</a>
                <form method="post" action="<?= url('/admin/sider/' . (int)$p['id'] . '/slett') ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;padding:0;">Slett</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($pages)): ?>
<p>Ingen sider ennå.</p>
<?php endif; ?>
