<?php
$cart = $cart ?? [];
$items = $items ?? [];
$subtotal_ore = (int) ($subtotal_ore ?? 0);
$shipping_methods = $shipping_methods ?? [];
$selected_shipping_id = $selected_shipping_id ?? null;
$shipping_ore = (int) ($shipping_ore ?? 0);
$total_ore = (int) ($total_ore ?? $subtotal_ore + $shipping_ore);
$error = $error ?? null;
?>
<div class="checkout-page">
    <div class="container">
        <h1 class="checkout-page__title">Kasse</h1>
        <?php if ($error): ?>
        <p class="checkout-page__error"><?= e($error) ?></p>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/kasse')) ?>" class="checkout-form" id="checkout-form">
            <?= csrf_field() ?>
            <div class="checkout-form__grid">
                <div class="checkout-form__main">
                    <h2>Kontaktinformasjon</h2>
                    <div class="form-group">
                        <label for="email" class="required">E-post</label>
                        <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="input">
                    </div>
                    <div class="form-group">
                        <label for="name">Navn</label>
                        <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" class="input">
                    </div>
                    <h2>Leveringsadresse</h2>
                    <div class="form-group">
                        <label for="address1">Adresse</label>
                        <input type="text" id="address1" name="address1" value="<?= e($_POST['address1'] ?? '') ?>" class="input">
                    </div>
                    <div class="checkout-form__row">
                        <div class="form-group">
                            <label for="postal_code">Postnummer</label>
                            <input type="text" id="postal_code" name="postal_code" value="<?= e($_POST['postal_code'] ?? '') ?>" class="input" maxlength="10">
                        </div>
                        <div class="form-group">
                            <label for="city">Sted</label>
                            <input type="text" id="city" name="city" value="<?= e($_POST['city'] ?? '') ?>" class="input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="country">Land</label>
                        <input type="text" id="country" name="country" value="<?= e($_POST['country'] ?? 'NO') ?>" class="input" maxlength="2">
                    </div>
                    <?php if ($shipping_methods !== []): ?>
                    <h2>Frakt</h2>
                    <div class="checkout-shipping">
                        <?php foreach ($shipping_methods as $sm): ?>
                        <?php
                        $freeOver = isset($sm['free_over_ore']) && $sm['free_over_ore'] !== null ? (int) $sm['free_over_ore'] : null;
                        $price = (int) ($sm['price_ore'] ?? 0);
                        $isSelected = ($selected_shipping_id !== null && (int) $sm['id'] === (int) $selected_shipping_id);
                        ?>
                        <label class="checkout-shipping__option">
                            <input type="radio" name="shipping_method_id" value="<?= (int) $sm['id'] ?>" <?= $isSelected ? 'checked' : '' ?>
                                data-price="<?= $price ?>"
                                data-free-over="<?= $freeOver !== null ? $freeOver : '' ?>">
                            <span><?= e($sm['name'] ?? '') ?></span>
                            <?php if ($freeOver !== null && $freeOver > 0): ?>
                            <small>— <?= e(\App\Support\Money::format($price)) ?> (gratis over <?= e(\App\Support\Money::format($freeOver)) ?>)</small>
                            <?php else: ?>
                            <small>— <?= e(\App\Support\Money::format($price)) ?></small>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="form-group" style="margin-top: var(--space-5);">
                        <button type="submit" class="btn btn--primary btn--lg">Fullfør bestilling</button>
                    </div>
                </div>
                <aside class="checkout-form__summary">
                    <h2>Oppsummering</h2>
                    <ul class="checkout-summary-list">
                        <?php foreach ($items as $item): ?>
                        <li>
                            <?= e($item['title_snapshot'] ?? '') ?> × <?= (int) $item['qty'] ?>
                            <strong><?= e(\App\Support\Money::format((int) $item['price_ore_snapshot'] * (int) $item['qty'])) ?></strong>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="checkout-summary__row">
                        <span>Sum varer</span>
                        <strong id="checkout-subtotal"><?= e(\App\Support\Money::format($subtotal_ore)) ?></strong>
                    </p>
                    <?php if ($shipping_methods !== []): ?>
                    <p class="checkout-summary__row">
                        <span>Frakt</span>
                        <strong id="checkout-shipping"><?= e(\App\Support\Money::format($shipping_ore)) ?></strong>
                    </p>
                    <?php endif; ?>
                    <p class="checkout-summary__total">
                        <span>Total</span>
                        <strong id="checkout-total"><?= e(\App\Support\Money::format($total_ore)) ?></strong>
                    </p>
                </aside>
            </div>
        </form>
    </div>
</div>
<?php if ($shipping_methods !== []): ?>
<script>
(function() {
    var form = document.getElementById('checkout-form');
    if (!form) return;
    var subtotalOre = <?= (int) $subtotal_ore ?>;
    var radios = form.querySelectorAll('input[name="shipping_method_id"]');
    var shippingEl = document.getElementById('checkout-shipping');
    var totalEl = document.getElementById('checkout-total');
    function formatOre(ore) {
        return new Intl.NumberFormat('nb-NO', { style: 'currency', currency: 'NOK' }).format(ore / 100);
    }
    function updateTotals() {
        var shippingOre = 0;
        radios.forEach(function(r) {
            if (!r.checked) return;
            var price = parseInt(r.dataset.price || '0', 10);
            var freeOver = r.dataset.freeOver !== '' ? parseInt(r.dataset.freeOver || '0', 10) : null;
            if (freeOver !== null && freeOver > 0 && subtotalOre >= freeOver) shippingOre = 0;
            else shippingOre = price;
        });
        var totalOre = subtotalOre + shippingOre;
        if (shippingEl) shippingEl.textContent = formatOre(shippingOre);
        if (totalEl) totalEl.textContent = formatOre(totalOre);
    }
    radios.forEach(function(r) { r.addEventListener('change', updateTotals); });
})();
</script>
<?php endif; ?>
