<?php $orders = $orders ?? []; ?>
<div class="account-page">
    <div class="container">
        <h1 class="account-page__title">Mine ordre</h1>
        <p>
            <a href="<?= url('/konto/profil') ?>">Min profil</a> |
            <a href="<?= url('/konto/passord') ?>">Bytt passord</a> |
            <form method="post" action="<?= url('/konto/logout') ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" class="btn btn--ghost">Logg ut</button></form>
        </p>
        <?php if (empty($orders)): ?>
        <p>Du har ingen ordre ennå.</p>
        <p><a href="<?= url('/') ?>">Fortsett å handle</a></p>
        <?php else: ?>
        <table class="admin-table" style="width:100%; border-collapse: collapse; margin-top: 1rem;">
            <thead>
                <tr>
                    <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Ordre</th>
                    <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Dato</th>
                    <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Status</th>
                    <th style="text-align:right; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Total</th>
                    <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handling</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($o['public_id'] ?? '') ?></td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($o['created_at'] ?? '') ?></td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($o['status'] ?? '') ?> / <?= e($o['payment_status'] ?? '') ?></td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: right;"><?= e(\App\Support\Money::format((int)($o['total_ore'] ?? 0))) ?></td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><a href="<?= url('/konto/ordre/' . e($o['public_id'] ?? '')) ?>">Vis</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
