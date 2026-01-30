<?php
$user = $user ?? null;
$error = $error ?? null;
$isEdit = $user !== null;
?>
<?php if ($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/brukere/' . (int)$user['id'])) : e(url('/admin/brukere')) ?>" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="user-email" class="required">E-post</label>
        <input type="email" id="user-email" name="email" value="<?= e($user['email'] ?? $_POST['email'] ?? '') ?>" required class="input" <?= $isEdit ? '' : 'autocomplete="off"' ?>>
    </div>
    <div class="form-group">
        <label for="user-firstname">Fornavn</label>
        <input type="text" id="user-firstname" name="first_name" value="<?= e($user['first_name'] ?? $_POST['first_name'] ?? '') ?>" class="input">
    </div>
    <?php if (!$isEdit): ?>
    <div class="form-group">
        <label for="user-password" class="required">Passord</label>
        <input type="password" id="user-password" name="password" required minlength="8" class="input" autocomplete="new-password">
        <span class="form-hint">Minst 8 tegn.</span>
    </div>
    <?php endif; ?>
    <?php if ($isEdit): ?>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" <?= (($user['is_active'] ?? 1) ? 'checked' : '') ?>> Aktiv</label>
    </div>
    <?php endif; ?>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/brukere') ?>">Avbryt</a>
    </div>
</form>
