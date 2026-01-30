<?php
$order = $order ?? [];
$items = $items ?? [];
$shippingAddress = $shippingAddress ?? [];
$billingAddress = $billingAddress ?? [];
?>
<div class="admin-page-actions">
    <a href="<?= url('/admin/ordrer') ?>" class="btn btn--ghost">← Tilbake til ordreliste</a>
</div>

<div class="admin-order-grid">
    <div class="admin-order-card">
        <h2>Ordre <?= e($order['public_id'] ?? '') ?></h2>
        <p><strong>E-post:</strong> <?= e($order['email'] ?? '') ?></p>
        <p><strong>Status:</strong> <?= e($order['status'] ?? '') ?> | <strong>Betaling:</strong> <?= e($order['payment_status'] ?? '') ?> | <strong>Levering:</strong> <?= e($order['fulfillment_status'] ?? '') ?></p>
        <p><strong>Dato:</strong> <?= e($order['created_at'] ?? '') ?></p>
        <p><strong>Subtotal:</strong> <?= e(\App\Support\Money::format((int)($order['subtotal_ore'] ?? 0))) ?> | <strong>Frakt:</strong> <?= e(\App\Support\Money::format((int)($order['shipping_ore'] ?? 0))) ?> | <strong>Total:</strong> <?= e(\App\Support\Money::format((int)($order['total_ore'] ?? 0))) ?></p>
    </div>
    <div class="admin-order-card">
        <h3>Oppdater status</h3>
        <form method="post" action="<?= url('/admin/ordrer/' . e($order['public_id'] ?? '') . '/status') ?>" class="admin-order-status-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="order-status">Ordrestatus</label>
                <select id="order-status" name="status" class="input">
                    <option value="">— ikke endre —</option>
                    <option value="pending" <?= ($order['status'] ?? '') === 'pending' ? 'selected' : '' ?>>pending</option>
                    <option value="confirmed" <?= ($order['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>confirmed</option>
                    <option value="cancelled" <?= ($order['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <label for="order-payment">Betaling</label>
                <select id="order-payment" name="payment_status" class="input">
                    <option value="">— ikke endre —</option>
                    <option value="unpaid" <?= ($order['payment_status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>unpaid</option>
                    <option value="paid" <?= ($order['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>paid</option>
                    <option value="refunded" <?= ($order['payment_status'] ?? '') === 'refunded' ? 'selected' : '' ?>>refunded</option>
                </select>
            </div>
            <div class="form-group">
                <label for="order-fulfillment">Levering</label>
                <select id="order-fulfillment" name="fulfillment_status" class="input">
                    <option value="">— ikke endre —</option>
                    <option value="unfulfilled" <?= ($order['fulfillment_status'] ?? '') === 'unfulfilled' ? 'selected' : '' ?>>unfulfilled</option>
                    <option value="shipped" <?= ($order['fulfillment_status'] ?? '') === 'shipped' ? 'selected' : '' ?>>shipped</option>
                    <option value="delivered" <?= ($order['fulfillment_status'] ?? '') === 'delivered' ? 'selected' : '' ?>>delivered</option>
                </select>
            </div>
            <button type="submit" class="btn btn--primary">Lagre</button>
        </form>
    </div>
</div>

<?php if (!empty($shippingAddress) || !empty($billingAddress)): ?>
<div class="admin-order-grid">
    <?php if (!empty($shippingAddress)): ?>
    <div class="admin-order-card">
        <h3>Leveringsadresse</h3>
        <p><?= e($shippingAddress['name'] ?? '') ?><br>
        <?= e($shippingAddress['address_line1'] ?? $shippingAddress['address1'] ?? '') ?><br>
        <?php if (!empty($shippingAddress['address_line2'])): ?><?= e($shippingAddress['address_line2']) ?><br><?php endif; ?>
        <?= e($shippingAddress['postal_code'] ?? '') ?> <?= e($shippingAddress['city'] ?? '') ?><br>
        <?= e($shippingAddress['country'] ?? '') ?></p>
    </div>
    <?php endif; ?>
    <?php if (!empty($billingAddress)): ?>
    <div class="admin-order-card">
        <h3>Fakturaadresse</h3>
        <p><?= e($billingAddress['name'] ?? '') ?><br>
        <?= e($billingAddress['address_line1'] ?? $billingAddress['address1'] ?? '') ?><br>
        <?php if (!empty($billingAddress['address_line2'])): ?><?= e($billingAddress['address_line2']) ?><br><?php endif; ?>
        <?= e($billingAddress['postal_code'] ?? '') ?> <?= e($billingAddress['city'] ?? '') ?><br>
        <?= e($billingAddress['country'] ?? '') ?></p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<h3 class="h3" style="margin-bottom: var(--space-4);">Ordrelinjer</h3>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Produkt</th>
                <th style="text-align: right;">Pris</th>
                <th style="text-align: center;">Antall</th>
                <th style="text-align: right;">Sum</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= e($item['title_snapshot'] ?? '') ?></td>
                <td style="text-align: right;"><?= e(\App\Support\Money::format((int)($item['unit_price_ore'] ?? 0))) ?></td>
                <td style="text-align: center;"><?= (int)($item['qty'] ?? 0) ?></td>
                <td style="text-align: right;"><?= e(\App\Support\Money::format((int)($item['line_total_ore'] ?? 0))) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
