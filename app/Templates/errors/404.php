<?php
$title = 'Siden finnes ikke — Motorleaks';
?>
<!DOCTYPE html>
<html lang="nb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <?php if (function_exists('asset')): ?><link rel="stylesheet" href="<?= e(asset('app.css')) ?>"><?php endif; ?>
    <style>
        .error-page { max-width: 32rem; margin: 4rem auto; padding: 2rem; text-align: center; }
        .error-page h1 { font-size: 1.5rem; margin-bottom: 1rem; }
        .error-page p { color: #555; margin-bottom: 1.5rem; }
        .error-page a { color: #0066cc; }
    </style>
</head>
<body>
    <div class="error-page">
        <h1>404 — Siden finnes ikke</h1>
        <p>Adressen du søkte etter ble ikke funnet.</p>
        <p><a href="<?= function_exists('url') ? e(url('/')) : '/' ?>">Gå til forsiden</a></p>
    </div>
</body>
</html>
