<?php $redirect = $redirect ?? null; $error = $error ?? null; ?>
<?php if ($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= url('/admin/omdirigeringer') ?>" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="old-path" class="required">Gammel sti (fra URL)</label>
        <input type="text" id="old-path" name="old_path" value="<?= e($_POST['old_path'] ?? '') ?>" required placeholder="/produktkategori/gammel-slug" class="input">
        <span class="form-hint">F.eks. /produktkategori/dekk eller /gammel-side</span>
    </div>
    <div class="form-group">
        <label for="new-path" class="required">Ny sti eller full URL</label>
        <input type="text" id="new-path" name="new_path" value="<?= e($_POST['new_path'] ?? '') ?>" required placeholder="/kategori/dekk" class="input">
        <span class="form-hint">Relativ sti (f.eks. /kategori/slug) eller full URL</span>
    </div>
    <div class="form-group">
        <label for="status-code">HTTP-status</label>
        <select id="status-code" name="status_code" class="input" style="max-width: 10rem;">
            <option value="301">301 (permanent)</option>
            <option value="302">302 (midlertidig)</option>
        </select>
    </div>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary">Opprett</button>
        <a href="<?= url('/admin/omdirigeringer') ?>">Avbryt</a>
    </div>
</form>
