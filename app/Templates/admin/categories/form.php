<?php
$category = $category ?? null;
$parents = $parents ?? [];
$error = $error ?? null;
$isEdit = $category !== null;
?>
<?php if ($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/kategorier/' . (int)$category['id'])) : e(url('/admin/kategorier')) ?>" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="cat-name" class="required">Navn</label>
        <input type="text" id="cat-name" name="name" value="<?= e($category['name'] ?? $_POST['name'] ?? '') ?>" required class="input">
    </div>
    <div class="form-group">
        <label for="cat-slug">Slug</label>
        <input type="text" id="cat-slug" name="slug" value="<?= e($category['slug'] ?? $_POST['slug'] ?? '') ?>" class="input">
    </div>
    <div class="form-group">
        <label for="cat-parent">Forelderkategori</label>
        <select id="cat-parent" name="parent_id" class="input">
            <option value="">— Ingen (rot) —</option>
            <?php foreach ($parents as $p): ?>
            <?php if ($isEdit && (int)$p['id'] === (int)$category['id']) continue; ?>
            <option value="<?= (int)$p['id'] ?>" <?= (isset($category['parent_id']) && (int)$category['parent_id'] === (int)$p['id']) ? 'selected' : '' ?>><?= e($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="cat-sort">Sort rekkefølge</label>
        <input type="number" id="cat-sort" name="sort_order" value="<?= e($category['sort_order'] ?? $_POST['sort_order'] ?? '0') ?>" min="0" class="input" style="max-width: 5rem;">
    </div>
    <div class="form-group">
        <label for="cat-desc">Beskrivelse (HTML)</label>
        <textarea id="cat-desc" name="description_html" rows="4" class="input"><?= e($category['description_html'] ?? $_POST['description_html'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" <?= (($category['is_active'] ?? $_POST['is_active'] ?? 1) ? 'checked' : '') ?>> Aktiv</label>
    </div>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/kategorier') ?>">Avbryt</a>
    </div>
</form>
