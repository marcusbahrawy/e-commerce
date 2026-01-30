<?php $orders = $orders ?? []; ?>
<div class="admin-page-actions">
    <a href="<?= url('/admin') ?>" class="btn btn--ghost">← Dashboard</a>
</div>
<?php if (empty($orders)): ?>
<p class="admin-empty">Ingen ordre ennå.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Ordre</th>
                <th>E-post</th>
                <th>Status / Betaling</th>
                <th style="text-align: right;">Total</th>
                <th>Dato</th>
                <th class="table__actions">Handling</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= e($o['public_id'] ?? '') ?></td>
                <td><?= e($o['email'] ?? '') ?></td>
                <td><?= e($o['status'] ?? '') ?> / <?= e($o['payment_status'] ?? '') ?></td>
                <td style="text-align: right;"><?= e(\App\Support\Money::format((int)($o['total_ore'] ?? 0))) ?></td>
                <td><?= e($o['created_at'] ?? '') ?></td>
                <td class="table__actions">
                    <a href="<?= url('/admin/ordrer/' . e($o['public_id'] ?? '')) ?>" class="btn btn--ghost btn--sm">Vis</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
