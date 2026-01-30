<?php
$page = $page ?? [];
?>
<article class="cms-page">
    <header class="cms-page__header">
        <h1 class="cms-page__title"><?= e($page['title'] ?? '') ?></h1>
    </header>
    <div class="cms-page__content prose">
        <?= $page['content_html'] ?? '' ?>
    </div>
</article>
