<?php $error = $error ?? null; ?>
<div class="account-page">
    <div class="container" style="max-width: 400px; margin: 2rem auto;">
        <h1 class="account-page__title">Opprett konto</h1>
        <?php if ($error): ?>
        <p class="account-page__error"><?= e($error) ?></p>
        <?php endif; ?>
        <form method="post" action="<?= e(url('/konto/registrer')) ?>">
            <?= csrf_field() ?>
            <p>
                <label for="email">E-post *</label>
                <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="input" style="width:100%;">
            </p>
            <p>
                <label for="first_name">Fornavn</label>
                <input type="text" id="first_name" name="first_name" value="<?= e($_POST['first_name'] ?? '') ?>" class="input" style="width:100%;">
            </p>
            <p>
                <label for="last_name">Etternavn</label>
                <input type="text" id="last_name" name="last_name" value="<?= e($_POST['last_name'] ?? '') ?>" class="input" style="width:100%;">
            </p>
            <p>
                <label for="password">Passord *</label>
                <input type="password" id="password" name="password" required minlength="8" class="input" style="width:100%;">
                <small>Minst 8 tegn.</small>
            </p>
            <p>
                <label for="password_confirm">Bekreft passord *</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="8" class="input" style="width:100%;">
            </p>
            <p>
                <button type="submit" class="btn btn--primary">Opprett konto</button>
            </p>
        </form>
        <p><a href="<?= url('/konto/login') ?>">Har du allerede konto? Logg inn</a></p>
        <p><a href="<?= url('/') ?>">Tilbake til forsiden</a></p>
    </div>
</div>
