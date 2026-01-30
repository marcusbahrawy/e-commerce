<?php
$page = $page ?? null;
$error = $error ?? null;
$isEdit = $page !== null;
?>
<?php if ($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/sider/' . (int)$page['id'])) : e(url('/admin/sider')) ?>" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="page-title" class="required">Tittel</label>
        <input type="text" id="page-title" name="title" value="<?= e($page['title'] ?? $_POST['title'] ?? '') ?>" required class="input">
    </div>
    <div class="form-group">
        <label for="page-slug">Slug</label>
        <input type="text" id="page-slug" name="slug" value="<?= e($page['slug'] ?? $_POST['slug'] ?? '') ?>" class="input">
    </div>
    <div class="form-group">
        <label for="page-meta-title">Meta tittel</label>
        <input type="text" id="page-meta-title" name="meta_title" value="<?= e($page['meta_title'] ?? $_POST['meta_title'] ?? '') ?>" class="input">
    </div>
    <div class="form-group">
        <label for="page-meta-desc">Meta beskrivelse</label>
        <textarea id="page-meta-desc" name="meta_description" rows="2" class="input"><?= e($page['meta_description'] ?? $_POST['meta_description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="page-content">Innhold (HTML)</label>
        <textarea id="page-content" name="content_html" rows="12" class="input"><?= e($page['content_html'] ?? $_POST['content_html'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" <?= (($page['is_active'] ?? $_POST['is_active'] ?? 1) ? 'checked' : '') ?>> Aktiv</label>
    </div>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/sider') ?>">Avbryt</a>
    </div>
</form>
