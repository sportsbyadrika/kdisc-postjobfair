<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

$user = current_user();
$uid = $user['id'];

$totalUsers = 0;
if ($user['role'] === 'administrator') {
    $totalUsers = (int) db()->query('SELECT COUNT(*) FROM users WHERE active_status = 1')->fetchColumn();
}

$activitySql = $user['role'] === 'administrator'
    ? 'SELECT COUNT(*) FROM activities WHERE active_status = 1'
    : 'SELECT COUNT(*) FROM activities WHERE active_status = 1 AND owner_user_id = ?';
$stmt = db()->prepare($activitySql);
$stmt->execute($user['role'] === 'administrator' ? [] : [$uid]);
$totalActivities = (int) $stmt->fetchColumn();

$projectCount = (int) db()->query("SELECT COUNT(*) FROM activities WHERE module_name='project' AND active_status = 1")->fetchColumn();
$crmCount = (int) db()->query("SELECT COUNT(*) FROM activities WHERE module_name='crm' AND active_status = 1")->fetchColumn();
$reportCount = (int) db()->query("SELECT COUNT(*) FROM activities WHERE module_name='report' AND active_status = 1")->fetchColumn();

render_header('Dashboard');
?>
<h1 class="h3 mb-4">Welcome, <?= esc($user['name']) ?></h1>
<div class="row g-3 mb-3">
    <?php if ($user['role'] === 'administrator'): ?>
        <div class="col-12 col-md-6 col-lg-3"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">Active Users</p><h2 class="h4"><?= $totalUsers ?></h2><a href="/users.php">Manage</a></div></div></div>
    <?php endif; ?>
    <div class="col-12 col-md-6 col-lg-3"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">Activities</p><h2 class="h4"><?= $totalActivities ?></h2><a href="/activities.php">Open</a></div></div></div>
    <div class="col-12 col-md-6 col-lg-2"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">Project</p><h2 class="h4"><?= $projectCount ?></h2></div></div></div>
    <div class="col-12 col-md-6 col-lg-2"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">CRM</p><h2 class="h4"><?= $crmCount ?></h2></div></div></div>
    <div class="col-12 col-md-6 col-lg-2"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">Reports</p><h2 class="h4"><?= $reportCount ?></h2></div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h5">Quick navigation</h2>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary btn-sm" href="/activities.php">Activities</a>
            <?php if ($user['role'] === 'administrator'): ?>
                <a class="btn btn-outline-secondary btn-sm" href="/users.php">Users</a>
                <a class="btn btn-outline-success btn-sm" href="/reports.php">Login Reports</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php render_footer(); ?>
