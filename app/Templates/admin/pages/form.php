<?php
$page = $page ?? null;
$error = $error ?? null;
$isEdit = $page !== null;
?>
<?php if ($error): ?><p style="color:#c00;"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/sider/' . (int)$page['id'])) : e(url('/admin/sider')) ?>">
    <?= csrf_field() ?>
    <p>
        <label>Tittel *</label>
        <input type="text" name="title" value="<?= e($page['title'] ?? $_POST['title'] ?? '') ?>" required class="input" style="max-width:400px;">
    </p>
    <p>
        <label>Slug</label>
        <input type="text" name="slug" value="<?= e($page['slug'] ?? $_POST['slug'] ?? '') ?>" class="input" style="max-width:400px;">
    </p>
    <p>
        <label>Meta tittel</label>
        <input type="text" name="meta_title" value="<?= e($page['meta_title'] ?? $_POST['meta_title'] ?? '') ?>" class="input" style="max-width:400px;">
    </p>
    <p>
        <label>Meta beskrivelse</label>
        <textarea name="meta_description" rows="2" class="input" style="max-width:500px;"><?= e($page['meta_description'] ?? $_POST['meta_description'] ?? '') ?></textarea>
    </p>
    <p>
        <label>Innhold (HTML)</label>
        <textarea name="content_html" rows="12" class="input" style="max-width:700px;"><?= e($page['content_html'] ?? $_POST['content_html'] ?? '') ?></textarea>
    </p>
    <p>
        <label><input type="checkbox" name="is_active" value="1" <?= (($page['is_active'] ?? $_POST['is_active'] ?? 1) ? 'checked' : '') ?>> Aktiv</label>
    </p>
    <p>
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/sider') ?>">Avbryt</a>
    </p>
</form>
