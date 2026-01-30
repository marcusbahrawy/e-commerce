<?php
$footer1Items = $footer1Items ?? [];
$footer2Items = $footer2Items ?? [];
?>
<footer class="footer">
    <div class="container footer__inner">
        <div class="footer__col">
            <h3 class="footer__title">Kontakt</h3>
            <p>E-post og telefon via butikken.</p>
        </div>
        <?php if (!empty($footer1Items)): ?>
        <div class="footer__col">
            <h3 class="footer__title">Snarveier</h3>
            <ul>
                <?php foreach ($footer1Items as $item): ?>
                <?php if (empty($item['is_active'])) continue; ?>
                <?php $href = !empty($item['url']) ? $item['url'] : '#'; if (strpos($href, 'http') !== 0 && $href !== '#') { $href = url($href); } ?>
                <li><a href="<?= e($href) ?>"><?= e($item['label'] ?? '') ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (!empty($footer2Items)): ?>
        <div class="footer__col">
            <h3 class="footer__title">Informasjon</h3>
            <ul>
                <?php foreach ($footer2Items as $item): ?>
                <?php if (empty($item['is_active'])) continue; ?>
                <?php $href = !empty($item['url']) ? $item['url'] : '#'; if (strpos($href, 'http') !== 0 && $href !== '#') { $href = url($href); } ?>
                <li><a href="<?= e($href) ?>"><?= e($item['label'] ?? '') ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (empty($footer1Items) && empty($footer2Items)): ?>
        <div class="footer__col">
            <h3 class="footer__title">Snarveier</h3>
            <ul>
                <li><a href="<?= url('/side/om-oss') ?>">Om oss</a></li>
                <li><a href="<?= url('/side/kjopsbetingelser') ?>">Kjøpsbetingelser</a></li>
                <li><a href="<?= url('/side/angrerett-retur') ?>">Angrerett og retur</a></li>
                <li><a href="<?= url('/side/personvern') ?>">Personvern</a></li>
            </ul>
        </div>
        <?php endif; ?>
        <div class="footer__bottom">
            <p>&copy; <?= date('Y') ?> Motorleaks. Delebestilling på nett.</p>
        </div>
    </div>
</footer>
