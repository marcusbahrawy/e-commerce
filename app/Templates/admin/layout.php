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
    <link rel="stylesheet" href="<?= asset('app.css') ?>">
    <style>
    .admin-layout { display: flex; min-height: 100vh; }
    .admin-sidebar { width: 220px; background: #1a1a1a; color: #fff; padding: 1.5rem; }
    .admin-sidebar a { color: #ccc; text-decoration: none; display: block; padding: 0.5rem 0; }
    .admin-sidebar a:hover { color: #fff; }
    .admin-main { flex: 1; padding: 1.5rem; }
    .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e5e5e5; padding-bottom: 1rem; }
    .admin-header h1 { margin: 0; font-size: 1.25rem; }
    </style>
</head>
<body>
    <?php if (\App\Support\Auth::check()): ?>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <p><strong>Motorleaks Admin</strong></p>
            <nav>
                <a href="<?= url('/admin') ?>">Dashboard</a>
                <a href="<?= url('/admin/produkter') ?>">Produkter</a>
                <a href="<?= url('/admin/kategorier') ?>">Kategorier</a>
                <a href="<?= url('/admin/sider') ?>">CMS-sider</a>
                <a href="<?= url('/admin/menyer') ?>">Menyer</a>
                <a href="<?= url('/admin/frakt') ?>">Fraktmetoder</a>
                <a href="<?= url('/admin/ordrer') ?>">Ordrer</a>
                <form method="post" action="<?= url('/admin/logout') ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" class="btn btn--ghost" style="color:#ccc;border:none;background:none;cursor:pointer;">Logg ut</button></form>
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
