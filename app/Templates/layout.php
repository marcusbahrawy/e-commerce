<?php
$title = $title ?? 'Motorleaks';
$meta_description = $meta_description ?? '';
$menuRepo = new \App\Repositories\MenuRepository();
$headerMenu = $menuRepo->getByKey('header_main');
$headerMenuItems = $headerMenu ? $menuRepo->getItems((int) $headerMenu['id']) : [];
$footer1Menu = $menuRepo->getByKey('footer_1');
$footer1Items = $footer1Menu ? $menuRepo->getItems((int) $footer1Menu['id']) : [];
$footer2Menu = $menuRepo->getByKey('footer_2');
$footer2Items = $footer2Menu ? $menuRepo->getItems((int) $footer2Menu['id']) : [];
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <?php if ($meta_description !== ''): ?>
    <meta name="description" content="<?= e($meta_description) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= asset('app.css') ?>">
</head>
<body>
    <?php require __DIR__ . '/partials/header.php'; ?>
    <main class="main">
        <?= $content ?>
    </main>
    <?php require __DIR__ . '/partials/footer.php'; ?>
    <script src="<?= asset('app.js') ?>" defer></script>
</body>
</html>
