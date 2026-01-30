<?php $categories = $categories ?? []; ?>
<p>Kategoriadministrasjon. Hovedkategorier:</p>
<ul>
    <?php foreach ($categories as $c): ?>
    <li><?= e($c['name'] ?? '') ?> (<?= e($c['slug'] ?? '') ?>)</li>
    <?php endforeach; ?>
</ul>
<?php if (empty($categories)): ?>
<p>Ingen kategorier ennå.</p>
<?php endif; ?>
