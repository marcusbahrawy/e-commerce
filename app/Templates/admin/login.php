<?php
$error = $error ?? null;
?>
<div class="admin-login admin-order-card" style="max-width: 400px; margin: var(--space-8) auto;">
    <h1 class="h2" style="margin: 0 0 var(--space-5);">Admin — Logg inn</h1>
    <?php if ($error): ?>
    <div class="admin-error" style="margin-bottom: var(--space-4);"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(url('/admin/login')) ?>" class="admin-form">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="email">E-post</label>
            <input type="email" id="email" name="email" required class="input">
        </div>
        <div class="form-group">
            <label for="password">Passord</label>
            <input type="password" id="password" name="password" required class="input">
        </div>
        <div class="admin-form-actions">
            <button type="submit" class="btn btn--primary">Logg inn</button>
        </div>
    </form>
</div>
