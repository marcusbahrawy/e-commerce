<?php
$settings = $settings ?? [];
$keys = $keys ?? [];
?>
<?php if (isset($_GET['ok'])): ?><div class="alert alert--success" style="margin-bottom: var(--space-4);">Innstillinger er lagret.</div><?php endif; ?>
<form method="post" action="<?= url('/admin/innstillinger') ?>" class="admin-form">
    <?= csrf_field() ?>
    <?php foreach ($keys as $key => $config): ?>
    <div class="form-group">
        <label for="setting-<?= e($key) ?>"><?= e($config['label']) ?> (<?= e($key) ?>)</label>
        <?php if (($config['type'] ?? 'text') === 'textarea'): ?>
        <textarea id="setting-<?= e($key) ?>" name="setting_<?= e($key) ?>" class="input" rows="4"><?= e($settings[$key] ?? '') ?></textarea>
        <?php else: ?>
        <input type="<?= e($config['type'] ?? 'text') ?>" id="setting-<?= e($key) ?>" name="setting_<?= e($key) ?>" value="<?= e($settings[$key] ?? '') ?>" class="input">
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary">Lagre</button>
    </div>
</form>
<p class="text-muted text-small" style="margin-top: var(--space-4);">Disse verdiene lagres i databasen (tabell <code>settings</code>). Bruk dem i maler eller app-konfigurasjon etter behov.</p>
