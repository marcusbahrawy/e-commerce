<?php $orders = $orders ?? []; ?>
<p><a href="<?= url('/admin') ?>">← Dashboard</a></p>
<table class="admin-table" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Ordre</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">E-post</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Status / Betaling</th>
            <th style="text-align:right; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Total</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Dato</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handling</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($o['public_id'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($o['email'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($o['status'] ?? '') ?> / <?= e($o['payment_status'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: right;"><?= e(\App\Support\Money::format((int)($o['total_ore'] ?? 0))) ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($o['created_at'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><a href="<?= url('/admin/ordrer/' . e($o['public_id'] ?? '')) ?>">Vis</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($orders)): ?>
<p>Ingen ordre ennå.</p>
<?php endif; ?>
