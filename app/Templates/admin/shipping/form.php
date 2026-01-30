<?php
$method = $method ?? null;
$error = $error ?? null;
$input = $input ?? [];
$prefill = $method !== null ? $method : $input;
?>
<p><a href="<?= url('/admin/frakt') ?>">← Fraktmetoder</a></p>
<?php if ($error): ?>
<p class="error"><?= e($error) ?></p>
<?php endif; ?>
<form method="post" action="<?= $method !== null ? url('/admin/frakt/' . (int)$method['id']) : url('/admin/frakt') ?>">
    <?= csrf_field() ?>
    <p>
        <label for="code">Kode</label>
        <input type="text" id="code" name="code" value="<?= e($prefill['code'] ?? '') ?>" required class="input" style="width:100%; max-width:200px;">
        <small>F.eks. standard, express</small>
    </p>
    <p>
        <label for="name">Navn</label>
        <input type="text" id="name" name="name" value="<?= e($prefill['name'] ?? '') ?>" required class="input" style="width:100%; max-width:300px;">
    </p>
    <p>
        <label for="price_ore">Pris (øre)</label>
        <input type="number" id="price_ore" name="price_ore" value="<?= e($prefill['price_ore'] ?? '0') ?>" min="0" class="input" style="width:120px;">
        <small>Vises som <?= e(\App\Support\Money::format((int)($prefill['price_ore'] ?? 0))) ?></small>
    </p>
    <p>
        <label for="free_over_ore">Gratis fra (øre)</label>
        <input type="number" id="free_over_ore" name="free_over_ore" value="<?= ($v = $prefill['free_over_ore'] ?? '') !== '' && $v !== null ? (int)$v : '' ?>" min="0" class="input" style="width:120px;" placeholder="Valgfritt">
    </p>
    <p>
        <label><input type="checkbox" name="is_active" value="1" <?= !isset($prefill['is_active']) || !empty($prefill['is_active']) ? 'checked' : '' ?>> Aktiv</label>
    </p>
    <p>
        <label for="sort_order">Rekkefølge</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= e($prefill['sort_order'] ?? '0') ?>" class="input" style="width:80px;">
    </p>
    <p>
        <button type="submit" class="btn btn--primary"><?= $method !== null ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/frakt') ?>">Avbryt</a>
    </p>
</form>
