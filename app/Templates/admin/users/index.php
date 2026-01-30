<?php $users = $users ?? []; ?>
<p><a href="<?= url('/admin/brukere/ny') ?>" class="btn btn--primary">Ny adminbruker</a></p>
<table class="admin-table" style="width:100%; border-collapse: collapse; margin-top: 1rem;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">ID</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">E-post</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Navn</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Status</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Sist innlogget</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handlinger</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int) $u['id'] ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($u['email'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($u['first_name'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= (int)($u['is_active'] ?? 0) ? 'Aktiv' : 'Inaktiv' ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= !empty($u['last_login_at']) ? e($u['last_login_at']) : '—' ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                <a href="<?= url('/admin/brukere/' . (int)$u['id'] . '/rediger') ?>">Rediger</a>
                |
                <a href="<?= url('/admin/brukere/' . (int)$u['id'] . '/passord') ?>">Sett passord</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (isset($_GET['ok']) && $_GET['ok'] === 'passord'): ?>
<p style="color:#080;">Passord er oppdatert.</p>
<?php endif; ?>
<?php if (empty($users)): ?>
<p>Ingen adminbrukere.</p>
<?php endif; ?>
