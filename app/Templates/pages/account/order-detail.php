<?php
$order = $order ?? [];
$items = $items ?? [];
?>
<div class="account-page">
    <div class="container">
        <h1 class="account-page__title">Ordre <?= e($order['public_id'] ?? '') ?></h1>
        <p><a href="<?= url('/konto/ordre') ?>">← Tilbake til mine ordre</a></p>
        <p><strong>Status:</strong> <?= e($order['status'] ?? '') ?> / <?= e($order['payment_status'] ?? '') ?></p>
        <p><strong>Dato:</strong> <?= e($order['created_at'] ?? '') ?></p>
        <p><strong>Total:</strong> <?= e(\App\Support\Money::format((int)($order['total_ore'] ?? 0))) ?></p>
        <h2>Ordrelinjer</h2>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Produkt</th>
                    <th style="text-align:right; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Pris</th>
                    <th style="text-align:center; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Antall</th>
                    <th style="text-align:right; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Sum</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($item['title_snapshot'] ?? '') ?></td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: right;"><?= e(\App\Support\Money::format((int)($item['unit_price_ore'] ?? 0))) ?></td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: center;"><?= (int)($item['qty'] ?? 0) ?></td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: right;"><?= e(\App\Support\Money::format((int)($item['line_total_ore'] ?? 0))) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
