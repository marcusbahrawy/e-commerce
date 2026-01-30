<?php $methods = $methods ?? []; ?>
<p><a href="<?= url('/admin') ?>">← Dashboard</a> | <a href="<?= url('/admin/frakt/ny') ?>">Ny fraktmetode</a></p>
<table class="admin-table" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Kode</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Navn</th>
            <th style="text-align:right; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Pris</th>
            <th style="text-align:right; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Gratis over</th>
            <th style="text-align:center; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Aktiv</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handling</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($methods as $m): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($m['code'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($m['name'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: right;"><?= e(\App\Support\Money::format((int)($m['price_ore'] ?? 0))) ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: right;"><?= ($m['free_over_ore'] ?? null) !== null ? e(\App\Support\Money::format((int)$m['free_over_ore'])) : '—' ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee; text-align: center;"><?= !empty($m['is_active']) ? 'Ja' : 'Nei' ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">
                <a href="<?= url('/admin/frakt/' . (int)($m['id']) . '/rediger') ?>">Rediger</a>
                <form method="post" action="<?= url('/admin/frakt/' . (int)($m['id']) . '/slett') ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" class="btn btn--ghost" style="color:#c00;">Slett</button></form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($methods)): ?>
<p>Ingen fraktmetoder. <a href="<?= url('/admin/frakt/ny') ?>">Legg til en</a>.</p>
<?php endif; ?>
