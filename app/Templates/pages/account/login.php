<?php $error = $error ?? null; ?>
<div class="account-page">
    <div class="container" style="max-width: 400px; margin: 2rem auto;">
        <h1 class="account-page__title">Logg inn — Min konto</h1>
        <?php if ($error): ?>
        <p class="account-page__error"><?= e($error) ?></p>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/konto/login')) ?>">
            <?= csrf_field() ?>
            <p>
                <label for="email">E-post</label>
                <input type="email" id="email" name="email" required class="input" style="width:100%;">
            </p>
            <p>
                <label for="password">Passord</label>
                <input type="password" id="password" name="password" required class="input" style="width:100%;">
            </p>
            <p>
                <button type="submit" class="btn btn--primary">Logg inn</button>
            </p>
        </form>
        <p><a href="<?= url('/konto/registrer') ?>">Opprett konto</a> | <a href="<?= url('/konto/glemt-passord') ?>">Glemt passord?</a> | <a href="<?= url('/') ?>">Tilbake til forsiden</a></p>
    </div>
</div>
