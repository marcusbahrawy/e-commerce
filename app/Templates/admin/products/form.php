<?php
$product = $product ?? null;
$categories = $categories ?? [];
$brands = $brands ?? [];
$images = $images ?? [];
$error = $error ?? null;
$isEdit = $product !== null;
$uploadError = $_GET['error'] ?? null;
?>
<?php if ($error): ?><div class="admin-error"><?= e($error) ?></div><?php endif; ?>
<?php if ($uploadError === 'upload'): ?><div class="admin-error">Kunne ikke laste opp fil.</div><?php endif; ?>
<?php if ($uploadError === 'type'): ?><div class="admin-error">Kun bilder (JPEG, PNG, WebP, GIF) er tillatt.</div><?php endif; ?>
<?php if ($uploadError === 'size'): ?><div class="admin-error">Filen er for stor (maks 5 MB).</div><?php endif; ?>
<?php if ($uploadError === 'save'): ?><div class="admin-error">Kunne ikke lagre filen.</div><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/produkter/' . (int)$product['id'])) : e(url('/admin/produkter')) ?>" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="product-title" class="required">Tittel</label>
        <input type="text" id="product-title" name="title" value="<?= e($product['title'] ?? $_POST['title'] ?? '') ?>" required class="input">
    </div>
    <div class="form-group">
        <label for="product-slug">Slug</label>
        <input type="text" id="product-slug" name="slug" value="<?= e($product['slug'] ?? $_POST['slug'] ?? '') ?>" class="input">
    </div>
    <div class="form-group">
        <label for="product-sku">SKU</label>
        <input type="text" id="product-sku" name="sku" value="<?= e($product['sku'] ?? $_POST['sku'] ?? '') ?>" class="input" style="max-width: 12rem;">
    </div>
    <div class="form-group">
        <label for="product-price">Pris (øre)</label>
        <input type="number" id="product-price" name="price_from_ore" value="<?= e($product['price_from_ore'] ?? $_POST['price_from_ore'] ?? '0') ?>" min="0" class="input" style="max-width: 8rem;">
    </div>
    <div class="form-group">
        <label for="product-brand">Merke</label>
        <select id="product-brand" name="brand_id" class="input">
            <option value="">— Ingen —</option>
            <?php foreach ($brands as $b): ?>
            <option value="<?= (int)$b['id'] ?>" <?= (isset($product['brand_id']) && (int)$product['brand_id'] === (int)$b['id']) ? 'selected' : '' ?>><?= e($b['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="product-category">Kategori</label>
        <select id="product-category" name="primary_category_id" class="input">
            <option value="">— Ingen —</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (isset($product['primary_category_id']) && (int)$product['primary_category_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="product-short">Kort beskrivelse</label>
        <textarea id="product-short" name="description_short" rows="2" class="input"><?= e($product['description_short'] ?? $_POST['description_short'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="product-html">Beskrivelse (HTML)</label>
        <textarea id="product-html" name="description_html" rows="6" class="input"><?= e($product['description_html'] ?? $_POST['description_html'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_active" value="1" <?= (($product['is_active'] ?? $_POST['is_active'] ?? 1) ? 'checked' : '') ?>> Aktiv</label>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_featured" value="1" <?= (($product['is_featured'] ?? $_POST['is_featured'] ?? 0) ? 'checked' : '') ?>> Utvalgt</label>
    </div>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/produkter') ?>">Avbryt</a>
    </div>
</form>

<?php if ($isEdit && $product): ?>
<hr class="admin-form-divider">
<h2 class="h3" style="margin-bottom: var(--space-4);">Bilder</h2>
<?php if ($images !== []): ?>
<div class="admin-image-grid">
    <?php foreach ($images as $img): ?>
    <div class="admin-image-card card">
        <div class="admin-image-card__img">
            <img src="<?= e(url('/' . ltrim($img['path_original'] ?? $img['path_webp'] ?? '', '/'))) ?>" alt="" width="120" height="120">
        </div>
        <div class="admin-image-card__actions">
            <?= (int)($product['primary_image_id'] ?? 0) === (int)$img['id'] ? '<span class="badge badge--primary">Hovedbilde</span>' : '' ?>
            <?php if ((int)($product['primary_image_id'] ?? 0) !== (int)$img['id']): ?>
            <form method="post" action="<?= url('/admin/produkter/' . (int)$product['id'] . '/bilde/primar') ?>"><?= csrf_field() ?><input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>"><button type="submit" class="btn btn--ghost btn--sm">Hovedbilde</button></form>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/produkter/' . (int)$product['id'] . '/bilde/slett') ?>" onsubmit="return confirm('Slette dette bildet?');"><?= csrf_field() ?><input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>"><button type="submit" class="btn btn--danger btn--sm">Slett</button></form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<form method="post" action="<?= url('/admin/produkter/' . (int)$product['id'] . '/bilde') ?>" enctype="multipart/form-data" class="admin-form">
    <?= csrf_field() ?>
    <div class="form-group">
        <label for="product-image">Last opp bilde (JPEG, PNG, WebP, GIF, maks 5 MB)</label>
        <input type="file" id="product-image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required class="input">
    </div>
    <div class="admin-form-actions">
        <button type="submit" class="btn btn--primary">Last opp</button>
    </div>
</form>
<?php endif; ?>
