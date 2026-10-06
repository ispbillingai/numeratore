<?php
/** Admin: the last 300 entries of the activity log (logins, settings, users). */
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$rows = getDBConnection()->query("
    SELECT a.created_at, a.action, a.details, a.ip_address, u.full_name
    FROM activity_log a LEFT JOIN users u ON u.id = a.user_id
    ORDER BY a.id DESC LIMIT 300")->fetchAll();

$labels = [
    'login' => 'Accesso', 'logout' => 'Uscita',
    'user_created' => 'Utente creato', 'user_updated' => 'Utente modificato', 'user_toggled' => 'Utente attivato/disattivato',
    'queue_service_saved' => 'Servizio salvato', 'queue_settings_updated' => 'Impostazioni eliminacode', 'queue_reset' => 'Coda azzerata',
];

$pageTitle = 'Registro attività';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-header"><h1><i class="fas fa-history"></i> Registro attività</h1></div>
<div class="card">
    <div class="card-body" style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <tr><th style="text-align:left;padding:8px">Quando</th><th style="text-align:left;padding:8px">Chi</th><th style="text-align:left;padding:8px">Cosa</th><th style="text-align:left;padding:8px">Dettagli</th><th style="text-align:left;padding:8px">IP</th></tr>
            <?php foreach ($rows as $r): ?>
                <tr style="border-top:1px solid var(--border,#eee)">
                    <td style="padding:8px;white-space:nowrap"><?= h(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
                    <td style="padding:8px"><?= h($r['full_name'] ?? '—') ?></td>
                    <td style="padding:8px"><?= h($labels[$r['action']] ?? $r['action']) ?></td>
                    <td style="padding:8px" class="text-muted"><?= h($r['details'] !== null ? trim((string) $r['details'], '"') : '') ?></td>
                    <td style="padding:8px" class="text-muted"><?= h($r['ip_address']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" style="padding:8px" class="text-muted">Nessuna attività registrata.</td></tr><?php endif; ?>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
