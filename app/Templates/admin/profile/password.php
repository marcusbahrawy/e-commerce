<?php $error = $error ?? null; $success = $success ?? null; ?>
<?php if ($error): ?><div class="admin-error" style="margin-bottom: var(--space-4);"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert--success" style="margin-bottom: var(--space-4);"><?= e($success) ?></div><?php endif; ?>
<form method="post" action="<?= url('/admin/passord') ?>" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="current-password" class="required">Nåværende passord</label>
        <input type="password" id="current-password" name="current_password" required class="input" autocomplete="current-password">
    </div>
    <div class="form-group">
        <label for="new-password" class="required">Nytt passord</label>
        <input type="password" id="new-password" name="new_password" required minlength="8" class="input" autocomplete="new-password">
        <span class="form-hint">Minst 8 tegn.</span>
    </div>
    <div class="form-group">
        <label for="new-password-confirm" class="required">Bekreft nytt passord</label>
        <input type="password" id="new-password-confirm" name="new_password_confirm" required minlength="8" class="input" autocomplete="new-password">
    </div>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary">Bytt passord</button>
    </div>
</form>
