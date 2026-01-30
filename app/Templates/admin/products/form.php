<?php
$product = $product ?? null;
$categories = $categories ?? [];
$brands = $brands ?? [];
$images = $images ?? [];
$error = $error ?? null;
$isEdit = $product !== null;
$uploadError = $_GET['error'] ?? null;
?>
<?php if ($error): ?><p style="color:#c00;"><?= e($error) ?></p><?php endif; ?>
<?php if ($uploadError === 'upload'): ?><p style="color:#c00;">Kunne ikke laste opp fil.</p><?php endif; ?>
<?php if ($uploadError === 'type'): ?><p style="color:#c00;">Kun bilder (JPEG, PNG, WebP, GIF) er tillatt.</p><?php endif; ?>
<?php if ($uploadError === 'size'): ?><p style="color:#c00;">Filen er for stor (maks 5 MB).</p><?php endif; ?>
<?php if ($uploadError === 'save'): ?><p style="color:#c00;">Kunne ikke lagre filen.</p><?php endif; ?>
<form method="post" action="<?= $isEdit ? e(url('/admin/produkter/' . (int)$product['id'])) : e(url('/admin/produkter')) ?>">
    <?= csrf_field() ?>
    <p>
        <label>Tittel *</label>
        <input type="text" name="title" value="<?= e($product['title'] ?? $_POST['title'] ?? '') ?>" required class="input" style="max-width:400px;">
    </p>
    <p>
        <label>Slug</label>
        <input type="text" name="slug" value="<?= e($product['slug'] ?? $_POST['slug'] ?? '') ?>" class="input" style="max-width:400px;">
    </p>
    <p>
        <label>SKU</label>
        <input type="text" name="sku" value="<?= e($product['sku'] ?? $_POST['sku'] ?? '') ?>" class="input" style="max-width:200px;">
    </p>
    <p>
        <label>Pris (øre)</label>
        <input type="number" name="price_from_ore" value="<?= e($product['price_from_ore'] ?? $_POST['price_from_ore'] ?? '0') ?>" min="0" class="input" style="max-width:120px;">
    </p>
    <p>
        <label>Merke</label>
        <select name="brand_id" class="input" style="max-width:300px;">
            <option value="">— Ingen —</option>
            <?php foreach ($brands as $b): ?>
            <option value="<?= (int)$b['id'] ?>" <?= (isset($product['brand_id']) && (int)$product['brand_id'] === (int)$b['id']) ? 'selected' : '' ?>><?= e($b['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label>Kategori</label>
        <select name="primary_category_id" class="input" style="max-width:300px;">
            <option value="">— Ingen —</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (isset($product['primary_category_id']) && (int)$product['primary_category_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label>Kort beskrivelse</label>
        <textarea name="description_short" rows="2" class="input" style="max-width:500px;"><?= e($product['description_short'] ?? $_POST['description_short'] ?? '') ?></textarea>
    </p>
    <p>
        <label>Beskrivelse (HTML)</label>
        <textarea name="description_html" rows="6" class="input" style="max-width:600px;"><?= e($product['description_html'] ?? $_POST['description_html'] ?? '') ?></textarea>
    </p>
    <p>
        <label><input type="checkbox" name="is_active" value="1" <?= (($product['is_active'] ?? $_POST['is_active'] ?? 1) ? 'checked' : '') ?>> Aktiv</label>
    </p>
    <p>
        <label><input type="checkbox" name="is_featured" value="1" <?= (($product['is_featured'] ?? $_POST['is_featured'] ?? 0) ? 'checked' : '') ?>> Utvalgt</label>
    </p>
    <p>
        <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Lagre' : 'Opprett' ?></button>
        <a href="<?= url('/admin/produkter') ?>">Avbryt</a>
    </p>
</form>

<?php if ($isEdit && $product): ?>
<hr style="margin: 2rem 0;">
<h3>Bilder</h3>
<?php if ($images !== []): ?>
<div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
    <?php foreach ($images as $img): ?>
    <div style="border: 1px solid #eee; padding: 0.5rem; text-align: center;">
        <img src="<?= e(url('/' . ltrim($img['path_original'] ?? $img['path_webp'] ?? '', '/'))) ?>" alt="" style="max-width: 120px; max-height: 120px; display: block;">
        <p style="margin: 0.5rem 0 0; font-size: 0.85rem;">
            <?= (int)($product['primary_image_id'] ?? 0) === (int)$img['id'] ? '<strong>Hovedbilde</strong>' : '' ?>
            <?php if ((int)($product['primary_image_id'] ?? 0) !== (int)$img['id']): ?>
            <form method="post" action="<?= url('/admin/produkter/' . (int)$product['id'] . '/bilde/primar') ?>" style="display:inline;"><?= csrf_field() ?><input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>"><button type="submit" class="btn btn--ghost" style="font-size:0.8rem;">Hovedbilde</button></form>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/produkter/' . (int)$product['id'] . '/bilde/slett') ?>" style="display:inline;" onsubmit="return confirm('Slette dette bildet?');"><?= csrf_field() ?><input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>"><button type="submit" class="btn btn--ghost" style="font-size:0.8rem; color:#c00;">Slett</button></form>
        </p>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<form method="post" action="<?= url('/admin/produkter/' . (int)$product['id'] . '/bilde') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <p>
        <label>Last opp bilde (JPEG, PNG, WebP, GIF, maks 5 MB)</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required>
        <button type="submit" class="btn btn--primary">Last opp</button>
    </p>
</form>
<?php endif; ?>
