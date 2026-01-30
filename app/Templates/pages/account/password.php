<?php
$error = $error ?? null;
$success = $success ?? null;
?>
<div class="account-page">
    <div class="container" style="max-width: 400px; margin: 2rem auto;">
        <h1 class="account-page__title">Bytt passord</h1>
        <p><a href="<?= url('/konto/ordre') ?>">← Mine ordre</a> | <a href="<?= url('/konto/profil') ?>">Min profil</a></p>
        <?php if ($error): ?>
        <p class="account-page__error"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
        <p class="account-page__success"><?= e($success) ?></p>
        <?php endif; ?>
        <form method="post" action="<?= url('/konto/passord') ?>">
            <?= csrf_field() ?>
            <p>
                <label for="current_password">Nåværende passord</label>
                <input type="password" id="current_password" name="current_password" required class="input" style="width:100%;">
            </p>
            <p>
                <label for="new_password">Nytt passord</label>
                <input type="password" id="new_password" name="new_password" required minlength="8" class="input" style="width:100%;">
                <small>Minst 8 tegn.</small>
            </p>
            <p>
                <label for="new_password_confirm">Bekreft nytt passord</label>
                <input type="password" id="new_password_confirm" name="new_password_confirm" required minlength="8" class="input" style="width:100%;">
            </p>
            <p>
                <button type="submit" class="btn btn--primary">Bytt passord</button>
            </p>
        </form>
    </div>
</div>
