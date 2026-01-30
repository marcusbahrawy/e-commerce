<?php
$error = $error ?? null;
?>
<div class="admin-login" style="max-width: 400px; margin: 4rem auto; padding: 2rem; border: 1px solid #e5e5e5; border-radius: 8px;">
    <h1 style="margin: 0 0 1.5rem;">Admin — Logg inn</h1>
    <?php if ($error): ?>
    <p style="color: #c00; margin: 0 0 1rem;"><?= e($error) ?></p>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/admin/login')) ?>">
        <?= csrf_field() ?>
        <p>
            <label for="email">E-post</label>
            <input type="email" id="email" name="email" required style="width: 100%; padding: 0.5rem; margin-top: 0.25rem;" class="input">
        </p>
        <p>
            <label for="password">Passord</label>
            <input type="password" id="password" name="password" required style="width: 100%; padding: 0.5rem; margin-top: 0.25rem;" class="input">
        </p>
        <p>
            <button type="submit" class="btn btn--primary">Logg inn</button>
        </p>
    </form>
</div>
