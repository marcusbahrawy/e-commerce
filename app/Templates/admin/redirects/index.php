<?php $redirects = $redirects ?? []; ?>
<div class="admin-page-actions">
    <a href="<?= url('/admin/omdirigeringer/ny') ?>" class="btn btn--primary">Ny omdirigering</a>
</div>
<p class="text-muted text-small" style="margin-bottom: var(--space-4);">Legg til gamle URL-er her for 301-omdirigering til nye sider. Nyttig ved migrering fra gammel side.</p>
<?php if (empty($redirects)): ?>
<p class="admin-empty">Ingen omdirigeringer. <a href="<?= url('/admin/omdirigeringer/ny') ?>">Legg til</a> for å mappe gamle URL-er til nye.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Gammel sti</th>
                <th>Ny sti / URL</th>
                <th>Status</th>
                <th>Treff</th>
                <th class="table__actions">Handling</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($redirects as $r): ?>
            <tr>
                <td><code class="admin-code"><?= e($r['old_path'] ?? '') ?></code></td>
                <td><code class="admin-code"><?= e($r['new_path'] ?? '') ?></code></td>
                <td><?= (int) ($r['status_code'] ?? 301) ?></td>
                <td><?= (int) ($r['hits'] ?? 0) ?></td>
                <td class="table__actions">
                    <form method="post" action="<?= url('/admin/omdirigeringer/' . (int)$r['id'] . '/slett') ?>"><?= csrf_field() ?><button type="submit" class="btn btn--danger btn--sm" onclick="return confirm('Slette denne omdirigeringen?');">Slett</button></form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
