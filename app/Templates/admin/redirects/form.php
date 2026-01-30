<?php $redirect = $redirect ?? null; $error = $error ?? null; ?>
<?php if ($error): ?><p style="color:#c00;"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="<?= url('/admin/omdirigeringer') ?>">
    <?= csrf_field() ?>
    <p>
        <label>Gammel sti (fra URL) *</label>
        <input type="text" name="old_path" value="<?= e($_POST['old_path'] ?? '') ?>" required placeholder="/produktkategori/gammel-slug" class="input" style="max-width:400px;">
        <span style="font-size:0.875rem; color:#666;">F.eks. /produktkategori/dekk eller /gammel-side</span>
    </p>
    <p>
        <label>Ny sti eller full URL *</label>
        <input type="text" name="new_path" value="<?= e($_POST['new_path'] ?? '') ?>" required placeholder="/kategori/dekk" class="input" style="max-width:400px;">
        <span style="font-size:0.875rem; color:#666;">Relativ sti (f.eks. /kategori/slug) eller full URL</span>
    </p>
    <p>
        <label>HTTP-status</label>
        <select name="status_code" class="input" style="max-width:100px;">
            <option value="301">301 (permanent)</option>
            <option value="302">302 (midlertidig)</option>
        </select>
    </p>
    <p>
        <button type="submit" class="btn btn--primary">Opprett</button>
        <a href="<?= url('/admin/omdirigeringer') ?>">Avbryt</a>
    </p>
</form>
