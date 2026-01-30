<?php
$method = $method ?? null;
$error = $error ?? null;
$input = $input ?? [];
$prefill = $method !== null ? $method : $input;
?>
<div class="admin-page-actions">
    <a href="<?= url('/admin/frakt') ?>" class="btn btn--ghost">← Fraktmetoder</a>
</div>
<?php if ($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= $method !== null ? url('/admin/frakt/' . (int)$method['id']) : url('/admin/frakt') ?>" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="code">Kode</label>
        <input type="text" id="code" name="code" value="<?= e($prefill['code'] ?? '') ?>" required class="input" style="max-width: 12rem;">
        <span class="form-hint">F.eks. standard, express</span>
    </div>
    <div class="form-group">
        <label for="name">Navn</label>
        <input type="text" id="name" name="name" value="<?= e($prefill['name'] ?? '') ?>" required class="input">
    </div>
    <div class="form-group">
        <label for="price_ore">Pris (øre)</label>
        <input type="number" id="price_ore" name="price_ore" value="<?= e($prefill['price_ore'] ?? '0') ?>" min="0" class="input" style="max-width: 8rem;">
        <span class="form-hint">Vises som <?= e(\App\Support\Money::format((int)($prefill['price_ore'] ?? 0))) ?></span>
    </div>
    <div class="form-group">
        <label for="free_over_ore">Gratis fra (øre)</label>
        <input type="number" id="free_over_ore" name="free_over_ore" value="<?= ($v = $prefill['free_over_ore'] ?? '') !== '' && $v !== null ? (int)$v : '' ?>" min="0" class="input" style="max-width: 8rem;" placeholder="Valgfritt">
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" <?= !isset($prefill['is_active']) || !empty($prefill['is_active']) ? 'checked' : '' ?>> Aktiv</label>
    </div>
    <div class="form-group">
        <label for="sort_order">Rekkefølge</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= e($prefill['sort_order'] ?? '0') ?>" class="input" style="max-width: 5rem;">
    </div>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary"><?= $method !== null ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/frakt') ?>">Avbryt</a>
    </div>
</form>
