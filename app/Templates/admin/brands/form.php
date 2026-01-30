<?php
$brand = $brand ?? null;
$error = $error ?? null;
$isEdit = $brand !== null;
?>
<?php if ($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/merker/' . (int)$brand['id'])) : e(url('/admin/merker')) ?>" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="brand-name" class="required">Navn</label>
        <input type="text" id="brand-name" name="name" value="<?= e($brand['name'] ?? $_POST['name'] ?? '') ?>" required class="input">
    </div>
    <div class="form-group">
        <label for="brand-slug">Slug</label>
        <input type="text" id="brand-slug" name="slug" value="<?= e($brand['slug'] ?? $_POST['slug'] ?? '') ?>" class="input">
    </div>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/merker') ?>">Avbryt</a>
    </div>
</form>
