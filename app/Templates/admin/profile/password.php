<?php $error = $error ?? null; $success = $success ?? null; ?>
<?php if ($error): ?><p style="color:#c00;"><?= e($error) ?></p><?php endif; ?>
<?php if ($success): ?><p style="color:#080;"><?= e($success) ?></p><?php endif; ?>
<form method="post" action="<?= url('/admin/passord') ?>">
    <?= csrf_field() ?>
    <p>
        <label>Nåværende passord *</label>
        <input type="password" name="current_password" required class="input" style="max-width:280px;" autocomplete="current-password">
    </p>
    <p>
        <label>Nytt passord *</label>
        <input type="password" name="new_password" required minlength="8" class="input" style="max-width:280px;" autocomplete="new-password">
        <span style="font-size:0.875rem;color:#666;">Minst 8 tegn.</span>
    </p>
    <p>
        <label>Bekreft nytt passord *</label>
        <input type="password" name="new_password_confirm" required minlength="8" class="input" style="max-width:280px;" autocomplete="new-password">
    </p>
    <p>
        <button type="submit" class="btn btn--primary">Bytt passord</button>
    </p>
</form>
