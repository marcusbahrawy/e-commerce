<?php
$menu_key = $menu_key ?? '';
$menu_name = $menu_name ?? '';
$items = $items ?? [];
$pages = $pages ?? [];
?>
<p><a href="<?= url('/admin/menyer') ?>">← Tilbake til menyer</a></p>
<h2><?= e($menu_name) ?></h2>
<form method="post" action="<?= e(url('/admin/menyer/' . $menu_key)) ?>">
    <?= csrf_field() ?>
    <p>Legg til rader: label, URL (eller side-slug). La label stå tom for å fjerne.</p>
    <table style="width:100%; max-width:600px; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align:left; padding: 0.5rem;">Label</th>
                <th style="text-align:left; padding: 0.5rem;">URL / side-slug</th>
                <th style="text-align:left; padding: 0.5rem;">Type</th>
            </tr>
        </thead>
        <tbody id="menu-rows">
            <?php foreach ($items as $item): ?>
            <tr>
                <td style="padding: 0.25rem;"><input type="text" name="item_label[]" value="<?= e($item['label'] ?? '') ?>" style="width:100%;"></td>
                <td style="padding: 0.25rem;"><input type="text" name="item_url[]" value="<?= e($item['url'] ?? '') ?>" style="width:100%;" placeholder="/side/om-oss eller https://..."></td>
                <td style="padding: 0.25rem;"><select name="item_type[]"><option value="url" <?= (($item['type'] ?? '') === 'url') ? 'selected' : '' ?>>URL</option><option value="page" <?= (($item['type'] ?? '') === 'page') ? 'selected' : '' ?>>Side</option></select></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td style="padding: 0.25rem;"><input type="text" name="item_label[]" value="" style="width:100%;" placeholder="Ny rad"></td>
                <td style="padding: 0.25rem;"><input type="text" name="item_url[]" value="" style="width:100%;"></td>
                <td style="padding: 0.25rem;"><select name="item_type[]"><option value="url">URL</option><option value="page">Side</option></select></td>
            </tr>
        </tbody>
    </table>
    <p style="margin-top: 1rem;">
        <button type="submit" class="btn btn--primary">Lagre meny</button>
    </p>
</form>
