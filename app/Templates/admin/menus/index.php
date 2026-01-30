<?php $menus = $menus ?? []; ?>
<p class="text-muted" style="margin-bottom: var(--space-4);">Velg en meny for å redigere.</p>
<ul class="admin-menu-list">
    <?php foreach ($menus as $m): ?>
    <li>
        <a href="<?= url('/admin/menyer/' . e($m['key'])) ?>" class="btn btn--secondary btn--block" style="justify-content: flex-start;">
            <?= e($m['name']) ?>
            <span class="text-muted" style="margin-left: auto;"><?= (int)($m['item_count'] ?? 0) ?> punkter</span>
        </a>
    </li>
    <?php endforeach; ?>
</ul>
