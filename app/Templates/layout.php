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
$settingsRepo = new \App\Repositories\SettingsRepository();
$settings = $settingsRepo->all();
$siteName = $settings['site_name'] ?? 'Motorleaks';
$contactEmail = $settings['contact_email'] ?? '';
$contactPhone = $settings['contact_phone'] ?? '';
$footerText = $settings['footer_text'] ?? '';
$categoryRepo = new \App\Repositories\CategoryRepository();
$headerCategories = $categoryRepo->getRootCategories();
foreach ($headerCategories as &$root) {
    $root['children'] = $categoryRepo->getChildren((int) $root['id']);
}
unset($root);
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title === 'Motorleaks' ? $siteName : $title . ' — ' . $siteName) ?></title>
    <?php if ($meta_description !== ''): ?>
    <meta name="description" content="<?= e($meta_description) ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('app.css') ?>">
    <script>window.APP_BASE = <?= json_encode(rtrim(parse_url(url('/'), PHP_URL_PATH) ?: '', '/')) ?>;</script>
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
