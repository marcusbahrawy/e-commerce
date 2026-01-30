<?php $logs = $logs ?? []; $users = $users ?? []; ?>
<p style="font-size:0.875rem; color:#666;">Siste admin-handlinger. Bruk for sporbarhet.</p>
<table class="admin-table" style="width:100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.875rem;">
    <thead>
        <tr>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Tid</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Bruker</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Handling</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Entitet</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">Detaljer</th>
            <th style="text-align:left; padding: 0.5rem; border-bottom: 1px solid #e5e5e5;">IP</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($log['created_at'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= isset($log['user_id']) && $log['user_id'] !== null ? e($users[(int)$log['user_id']] ?? '#' . $log['user_id']) : '—' ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($log['action'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($log['entity_type'] ?? '') ?><?= !empty($log['entity_id']) ? ' #' . e($log['entity_id']) : '' ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee; max-width: 200px; overflow: hidden; text-overflow: ellipsis;"><?= e($log['details'] ?? '') ?></td>
            <td style="padding: 0.5rem; border-bottom: 1px solid #eee;"><?= e($log['ip'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (empty($logs)): ?>
<p>Ingen loggføringer ennå.</p>
<?php endif; ?>
