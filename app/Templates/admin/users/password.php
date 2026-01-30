<?php $user = $user ?? null; $error = $error ?? null; ?>
<?php if ($user): ?>
<p>Bruker: <strong><?= e($user['email'] ?? '') ?></strong></p>
<?php endif; ?>
<?php if ($error): ?><p style="color:#c00;"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $user ? e(url('/admin/brukere/' . (int)$user['id'] . '/passord')) : '#' ?>">
    <?= csrf_field() ?>
    <p>
        <label>Nytt passord *</label>
        <input type="password" name="new_password" required minlength="8" class="input" style="max-width:280px;" autocomplete="new-password">
        <span style="font-size:0.875rem;color:#666;">Minst 8 tegn.</span>
    </p>
    <p>
        <label>Bekreft passord *</label>
        <input type="password" name="new_password_confirm" required minlength="8" class="input" style="max-width:280px;" autocomplete="new-password">
    </p>
    <p>
        <button type="submit" class="btn btn--primary">Sett passord</button>
        <a href="<?= url('/admin/brukere') ?>">Avbryt</a>
    </p>
</form>
