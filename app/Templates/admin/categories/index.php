<?php $categories = $categories ?? []; ?>
<p><a href="<?= url('/admin/kategorier/ny') ?>" class="btn btn--primary">Ny kategori</a></p>
<table class="admin-table" style="width:100%; border-collapse: collapse; margin-top: 1rem;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">ID</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Navn</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Slug</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Forelder</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Status</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handlinger</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $c): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int) $c['id'] ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($c['name'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($c['slug'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= empty($c['parent_id']) ? '—' : (int) $c['parent_id'] ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int)($c['is_active'] ?? 0) ? 'Aktiv' : 'Inaktiv' ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                <a href="<?= url('/admin/kategorier/' . (int)$c['id'] . '/rediger') ?>">Rediger</a>
                <form method="post" action="<?= url('/admin/kategorier/' . (int)$c['id'] . '/slett') ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;padding:0;">Slett</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($categories)): ?>
<p>Ingen kategorier ennå.</p>
<?php endif; ?>
