<?php
$brand = $brand ?? null;
$error = $error ?? null;
$isEdit = $brand !== null;
?>
<?php if ($error): ?><p style="color:#c00;"><?= e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/merker/' . (int)$brand['id'])) : e(url('/admin/merker')) ?>">
    <?= csrf_field() ?>
    <p>
        <label>Navn *</label>
        <input type="text" name="name" value="<?= e($brand['name'] ?? $_POST['name'] ?? '') ?>" required class="input" style="max-width:300px;">
    </p>
    <p>
        <label>Slug</label>
        <input type="text" name="slug" value="<?= e($brand['slug'] ?? $_POST['slug'] ?? '') ?>" class="input" style="max-width:300px;">
    </p>
    <p>
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/merker') ?>">Avbryt</a>
    </p>
</form>
