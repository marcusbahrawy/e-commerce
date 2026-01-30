<?php
$user = $user ?? [];
$error = $error ?? null;
$success = $success ?? null;
?>
<div class="account-page">
    <div class="container" style="max-width: 500px; margin: 2rem auto;">
        <h1 class="account-page__title">Min profil</h1>
        <p><a href="<?= url('/konto/ordre') ?>">← Mine ordre</a> | <a href="<?= url('/konto/passord') ?>">Bytt passord</a></p>
        <?php if ($error): ?>
        <p class="account-page__error"><?= e($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
        <p class="account-page__success"><?= e($success) ?></p>
        <?php endif; ?>
        <form method="post" action="<?= url('/konto/profil') ?>">
            <?= csrf_field() ?>
            <p>
                <label for="first_name">Fornavn</label>
                <input type="text" id="first_name" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" class="input" style="width:100%;">
            </p>
            <p>
                <label for="last_name">Etternavn</label>
                <input type="text" id="last_name" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" class="input" style="width:100%;">
            </p>
            <p>
                <label for="email">E-post *</label>
                <input type="email" id="email" name="email" value="<?= e($user['email'] ?? '') ?>" required class="input" style="width:100%;">
            </p>
            <p>
                <button type="submit" class="btn btn--primary">Lagre</button>
            </p>
        </form>
    </div>
</div>
