<?php $logs = $logs ?? []; $users = $users ?? []; ?>
<p class="text-muted text-small" style="margin-bottom: var(--space-4);">Siste admin-handlinger. Bruk for sporbarhet.</p>
<?php if (empty($logs)): ?>
<p class="admin-empty">Ingen loggføringer ennå.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="table" style="font-size: var(--fs-sm);">
        <thead>
            <tr>
                <th>Tid</th>
                <th>Bruker</th>
                <th>Handling</th>
                <th>Entitet</th>
                <th>Detaljer</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['created_at'] ?? '') ?></td>
                <td><?= isset($log['user_id']) && $log['user_id'] !== null ? e($users[(int)$log['user_id']] ?? '#' . $log['user_id']) : '—' ?></td>
                <td><?= e($log['action'] ?? '') ?></td>
                <td><?= e($log['entity_type'] ?? '') ?><?= !empty($log['entity_id']) ? ' #' . e($log['entity_id']) : '' ?></td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;"><?= e($log['details'] ?? '') ?></td>
                <td><?= e($log['ip'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
