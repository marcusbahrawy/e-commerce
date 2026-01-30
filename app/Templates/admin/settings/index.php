<?php
$settings = $settings ?? [];
$keys = $keys ?? [];
?>
<?php if (isset($_GET['ok'])): ?><p style="color:#080;">Innstillinger er lagret.</p><?php endif; ?>
<form method="post" action="<?= url('/admin/innstillinger') ?>">
    <?= csrf_field() ?>
    <?php foreach ($keys as $key => $config): ?>
    <p>
        <label><?= e($config['label']) ?> (<?= e($key) ?>)</label>
        <?php if (($config['type'] ?? 'text') === 'textarea'): ?>
        <textarea name="setting_<?= e($key) ?>" class="input" style="max-width:500px; min-height:80px;"><?= e($settings[$key] ?? '') ?></textarea>
        <?php else: ?>
        <input type="<?= e($config['type'] ?? 'text') ?>" name="setting_<?= e($key) ?>" value="<?= e($settings[$key] ?? '') ?>" class="input" style="max-width:400px;">
        <?php endif; ?>
    </p>
    <?php endforeach; ?>
    <p>
        <button type="submit" class="btn btn--primary">Lagre</button>
    </p>
</form>
<p style="font-size:0.875rem; color:#666;">Disse verdiene lagres i databasen (tabell <code>settings</code>). Bruk dem i maler eller app-konfigurasjon etter behov.</p>
