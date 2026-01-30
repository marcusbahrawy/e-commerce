<?php $methods = $methods ?? []; ?>
<div class="admin-page-actions">
    <a href="<?= url('/admin') ?>" class="btn btn--ghost">← Dashboard</a>
    <a href="<?= url('/admin/frakt/ny') ?>" class="btn btn--primary">Ny fraktmetode</a>
</div>
<?php if (empty($methods)): ?>
<p class="admin-empty">Ingen fraktmetoder. <a href="<?= url('/admin/frakt/ny') ?>">Legg til en</a>.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Navn</th>
                <th style="text-align: right;">Pris</th>
                <th style="text-align: right;">Gratis over</th>
                <th style="text-align: center;">Aktiv</th>
                <th class="table__actions">Handling</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($methods as $m): ?>
            <tr>
                <td><?= e($m['code'] ?? '') ?></td>
                <td><?= e($m['name'] ?? '') ?></td>
                <td style="text-align: right;"><?= e(\App\Support\Money::format((int)($m['price_ore'] ?? 0))) ?></td>
                <td style="text-align: right;"><?= ($m['free_over_ore'] ?? null) !== null ? e(\App\Support\Money::format((int)$m['free_over_ore'])) : '—' ?></td>
                <td style="text-align: center;"><?= !empty($m['is_active']) ? 'Ja' : 'Nei' ?></td>
                <td class="table__actions">
                    <a href="<?= url('/admin/frakt/' . (int)($m['id']) . '/rediger') ?>" class="btn btn--ghost btn--sm">Rediger</a>
                    <form method="post" action="<?= url('/admin/frakt/' . (int)($m['id']) . '/slett') ?>"><?= csrf_field() ?><button type="submit" class="btn btn--danger btn--sm" onclick="return confirm('Slette denne fraktmetoden?');">Slett</button></form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
