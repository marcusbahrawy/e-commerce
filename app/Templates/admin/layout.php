<?php
$content = $content ?? '';
$title = $title ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> — Motorleaks Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/tokens.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
    <?php if (\App\Support\Auth::check()): ?>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <p><strong>Motorleaks Admin</strong></p>
            <nav>
                <a href="<?= url('/admin') ?>">Dashboard</a>
                <a href="<?= url('/admin/produkter') ?>">Produkter</a>
                <a href="<?= url('/admin/merker') ?>">Merker</a>
                <a href="<?= url('/admin/kategorier') ?>">Kategorier</a>
                <a href="<?= url('/admin/sider') ?>">CMS-sider</a>
                <a href="<?= url('/admin/menyer') ?>">Menyer</a>
                <a href="<?= url('/admin/frakt') ?>">Fraktmetoder</a>
                <a href="<?= url('/admin/ordrer') ?>">Ordrer</a>
                <a href="<?= url('/admin/brukere') ?>">Brukere</a>
                <a href="<?= url('/admin/innstillinger') ?>">Innstillinger</a>
                <a href="<?= url('/admin/omdirigeringer') ?>">301-omdirigeringer</a>
                <a href="<?= url('/admin/audit') ?>">Audit-logg</a>
                <a href="<?= url('/admin/cache') ?>">Cache</a>
                <?php if (\App\Support\Env::string('APP_ENV', 'production') === 'local'): ?>
                <a href="<?= url('/admin/ui') ?>">UI-komponenter</a>
                <?php endif; ?>
                <a href="<?= url('/admin/passord') ?>">Bytt passord</a>
                <form method="post" action="<?= url('/admin/logout') ?>"><?= csrf_field() ?><button type="submit" class="btn btn--ghost">Logg ut</button></form>
            </nav>
        </aside>
        <main class="admin-main">
            <div class="admin-header">
                <h1><?= e($title) ?></h1>
            </div>
            <?= $content ?>
        </main>
    </div>
    <?php else: ?>
    <?= $content ?>
    <?php endif; ?>
</body>
</html>
