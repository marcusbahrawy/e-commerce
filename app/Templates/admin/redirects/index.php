<?php $redirects = $redirects ?? []; ?>
<p><a href="<?= url('/admin/omdirigeringer/ny') ?>" class="btn btn--primary">Ny omdirigering</a></p>
<p style="font-size:0.875rem; color:#666;">Legg til gamle URL-er her for 301-omdirigering til nye sider. Nyttig ved migrering fra gammel side.</p>
<table class="admin-table" style="width:100%; border-collapse: collapse; margin-top: 1rem;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Gammel sti</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Ny sti / URL</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Status</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Treff</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handling</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($redirects as $r): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><code><?= e($r['old_path'] ?? '') ?></code></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><code><?= e($r['new_path'] ?? '') ?></code></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int) ($r['status_code'] ?? 301) ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int) ($r['hits'] ?? 0) ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                <form method="post" action="<?= url('/admin/omdirigeringer/' . (int)$r['id'] . '/slett') ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;padding:0;" onclick="return confirm('Slette denne omdirigeringen?');">Slett</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($redirects)): ?>
<p>Ingen omdirigeringer. <a href="<?= url('/admin/omdirigeringer/ny') ?>">Legg til</a> for å mappe gamle URL-er til nye.</p>
<?php endif; ?>
