<?php
$error = $error ?? null;
$success = $success ?? null;
$token = $token ?? null;
?>
<div class="account-page">
    <div class="container" style="max-width: 400px; margin: 2rem auto;">
        <h1 class="account-page__title">Tilbakestill passord</h1>
        <?php if ($error): ?>
        <p class="account-page__error"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
        <p class="account-page__success"><?= e($success) ?></p>
        <p><a href="<?= url('/konto/login') ?>">Logg inn</a></p>
        <?php elseif ($token): ?>
        <form method="post" action="<?= e(url('/konto/tilbakestill-passord')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <p>
                <label for="new_password">Nytt passord *</label>
                <input type="password" id="new_password" name="new_password" required minlength="8" class="input" style="width:100%;">
                <small>Minst 8 tegn.</small>
            </p>
            <p>
                <label for="new_password_confirm">Bekreft passord *</label>
                <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8" class="input" style="width:100%;">
            </p>
            <p>
                <button type="submit" class="btn btn--primary">Sett nytt passord</button>
            </p>
        </form>
        <p><a href="<?= url('/konto/glemt-passord') ?>">Be om ny lenke</a></p>
        <?php else: ?>
        <p><a href="<?= url('/konto/glemt-passord') ?>">Be om ny lenke for å tilbakestille passord</a></p>
        <p><a href="<?= url('/konto/login') ?>">Logg inn</a></p>
        <?php endif; ?>
    </div>
</div>
