<?php $error = $error ?? null; $success = $success ?? null; ?>
<div class="account-page">
    <div class="container" style="max-width: 400px; margin: 2rem auto;">
        <h1 class="account-page__title">Glemt passord</h1>
        <?php if ($error): ?>
        <p class="account-page__error"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
        <p class="account-page__success"><?= e($success) ?></p>
        <p><a href="<?= url('/konto/login') ?>">Logg inn</a></p>
        <?php else: ?>
        <p>Skriv inn e-postadressen din. Vi sender deg en lenke for å tilbakestille passordet (gyldig i 1 time).</p>
        <form method="post" action="<?= e(url('/konto/glemt-passord')) ?>">
            <?= csrf_field() ?>
            <p>
                <label for="email">E-post *</label>
                <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="input" style="width:100%;">
            </p>
            <p>
                <button type="submit" class="btn btn--primary">Send lenke</button>
            </p>
        </form>
        <p><a href="<?= url('/konto/login') ?>">Tilbake til innlogging</a></p>
        <?php endif; ?>
    </div>
</div>
