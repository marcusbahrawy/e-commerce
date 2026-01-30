<?php
$user = $user ?? null;
$error = $error ?? null;
$isEdit = $user !== null;
?>
<?php if ($error): ?><p style="color:#c00;"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/brukere/' . (int)$user['id'])) : e(url('/admin/brukere')) ?>">
    <?= csrf_field() ?>
    <p>
        <label>E-post *</label>
        <input type="email" name="email" value="<?= e($user['email'] ?? $_POST['email'] ?? '') ?>" required class="input" style="max-width:300px;" <?= $isEdit ? '' : 'autocomplete="off"' ?>>
    </p>
    <p>
        <label>Fornavn</label>
        <input type="text" name="first_name" value="<?= e($user['first_name'] ?? $_POST['first_name'] ?? '') ?>" class="input" style="max-width:300px;">
    </p>
    <?php if (!$isEdit): ?>
    <p>
        <label>Passord *</label>
        <input type="password" name="password" required minlength="8" class="input" style="max-width:280px;" autocomplete="new-password">
        <span style="font-size:0.875rem;color:#666;">Minst 8 tegn.</span>
    </p>
    <?php endif; ?>
    <?php if ($isEdit): ?>
    <p>
        <label><input type="checkbox" name="is_active" value="1" <?= (($user['is_active'] ?? 1) ? 'checked' : '') ?>> Aktiv</label>
    </p>
    <?php endif; ?>
    <p>
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/brukere') ?>">Avbryt</a>
    </p>
</form>
