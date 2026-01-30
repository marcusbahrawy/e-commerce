<?php $users = $users ?? []; ?>
<div class="admin-page-actions">
    <a href="<?= url('/admin/brukere/ny') ?>" class="btn btn--primary">Ny adminbruker</a>
</div>
<?php if (isset($_GET['ok']) && $_GET['ok'] === 'passord'): ?>
<div class="alert alert--success" style="margin-bottom: var(--space-4);">Passord er oppdatert.</div>
<?php endif; ?>
<?php if (empty($users)): ?>
<p class="admin-empty">Ingen adminbrukere.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>E-post</th>
                <th>Navn</th>
                <th>Status</th>
                <th>Sist innlogget</th>
                <th class="table__actions">Handlinger</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= (int) $u['id'] ?></td>
                <td><?= e($u['email'] ?? '') ?></td>
                <td><?= e($u['first_name'] ?? '') ?></td>
                <td>
                    <?php if ((int)($u['is_active'] ?? 0)): ?>
                    <span class="badge badge--success">Aktiv</span>
                    <?php else: ?>
                    <span class="badge badge--neutral">Inaktiv</span>
                    <?php endif; ?>
                </td>
                <td><?= !empty($u['last_login_at']) ? e($u['last_login_at']) : '—' ?></td>
                <td class="table__actions">
                    <a href="<?= url('/admin/brukere/' . (int)$u['id'] . '/rediger') ?>" class="btn btn--ghost btn--sm">Rediger</a>
                    <a href="<?= url('/admin/brukere/' . (int)$u['id'] . '/passord') ?>" class="btn btn--ghost btn--sm">Sett passord</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
