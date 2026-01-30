<?php $menus = $menus ?? []; ?>
<p>Velg en meny for å redigere.</p>
<ul style="list-style:none; padding:0;">
    <?php foreach ($menus as $m): ?>
    <li style="margin-bottom: 0.5rem;">
        <a href="<?= url('/admin/menyer/' . e($m['key'])) ?>"><?= e($m['name']) ?></a>
        <span style="color:#666;">(<?= (int)($m['item_count'] ?? 0) ?> punkter)</span>
    </li>
    <?php endforeach; ?>
</ul>
