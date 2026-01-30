<?php
$category = $category ?? null;
$parents = $parents ?? [];
$error = $error ?? null;
$isEdit = $category !== null;
?>
<?php if ($error): ?><p style="color:#c00;"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/kategorier/' . (int)$category['id'])) : e(url('/admin/kategorier')) ?>">
    <?= csrf_field() ?>
    <p>
        <label>Navn *</label>
        <input type="text" name="name" value="<?= e($category['name'] ?? $_POST['name'] ?? '') ?>" required class="input" style="max-width:300px;">
    </p>
    <p>
        <label>Slug</label>
        <input type="text" name="slug" value="<?= e($category['slug'] ?? $_POST['slug'] ?? '') ?>" class="input" style="max-width:300px;">
    </p>
    <p>
        <label>Forelderkategori</label>
        <select name="parent_id" class="input" style="max-width:300px;">
            <option value="">— Ingen (rot) —</option>
            <?php foreach ($parents as $p): ?>
            <?php if ($isEdit && (int)$p['id'] === (int)$category['id']) continue; ?>
            <option value="<?= (int)$p['id'] ?>" <?= (isset($category['parent_id']) && (int)$category['parent_id'] === (int)$p['id']) ? 'selected' : '' ?>><?= e($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label>Sort rekkefølge</label>
        <input type="number" name="sort_order" value="<?= e($category['sort_order'] ?? $_POST['sort_order'] ?? '0') ?>" min="0" class="input" style="max-width:80px;">
    </p>
    <p>
        <label>Beskrivelse (HTML)</label>
        <textarea name="description_html" rows="4" class="input" style="max-width:500px;"><?= e($category['description_html'] ?? $_POST['description_html'] ?? '') ?></textarea>
    </p>
    <p>
        <label><input type="checkbox" name="is_active" value="1" <?= (($category['is_active'] ?? $_POST['is_active'] ?? 1) ? 'checked' : '') ?>> Aktiv</label>
    </p>
    <p>
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/kategorier') ?>">Avbryt</a>
    </p>
</form>
