<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_admin();

$summary = db()->query('SELECT u.id, u.name, u.mobile_number, COUNT(l.id) AS login_count FROM users u LEFT JOIN login_logs l ON l.user_id = u.id GROUP BY u.id, u.name, u.mobile_number ORDER BY login_count DESC, u.name')->fetchAll();
$detailsStmt = db()->query('SELECT l.id, l.user_id, u.name, u.mobile_number, l.login_at, l.logout_at, l.ip_address FROM login_logs l JOIN users u ON u.id = l.user_id ORDER BY l.id DESC');
$allLogs = $detailsStmt->fetchAll();
$logsByUser = [];
foreach ($allLogs as $log) {
    $logsByUser[$log['user_id']][] = $log;
}

render_header('Login Reports');
?>
<h1 class="h3 mb-3">User-wise Login Report</h1>
<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">
<thead><tr><th>User</th><th>Mobile</th><th>Total Logins</th><th>Drill-down</th></tr></thead>
<tbody>
<?php foreach ($summary as $s): ?>
<tr>
    <td><?= esc($s['name']) ?></td>
    <td><?= esc($s['mobile_number']) ?></td>
    <td><?= (int) $s['login_count'] ?></td>
    <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#logs-<?= (int) $s['id'] ?>">View details</button></td>
</tr>
<tr class="collapse" id="logs-<?= (int) $s['id'] ?>">
    <td colspan="4">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Login At</th><th>Logout At</th><th>IP Address</th></tr></thead>
                <tbody>
                <?php foreach (($logsByUser[$s['id']] ?? []) as $log): ?>
                    <tr><td><?= esc($log['login_at']) ?></td><td><?= esc($log['logout_at'] ?? '-') ?></td><td><?= esc($log['ip_address']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php render_footer(); ?>
