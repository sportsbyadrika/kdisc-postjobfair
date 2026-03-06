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

$selectedCount = (int) db()->query("SELECT COUNT(*) FROM job_fair_result WHERE LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'selected'")->fetchColumn();
$shortlistedCount = (int) db()->query("SELECT COUNT(*) FROM job_fair_result WHERE LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted'")->fetchColumn();
$onHoldCount = (int) db()->query("SELECT COUNT(*) FROM job_fair_result WHERE LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'onhold'")->fetchColumn();
$totalJoinedCount = (int) db()->query("SELECT COUNT(*) FROM job_fair_result WHERE LOWER(TRIM(Candidate_Joined_Status)) = 'yes'")->fetchColumn();


$pivotRows = db()->query('SELECT Job_Fair_No, Selection_Status, COUNT(*) AS total_count FROM job_fair_result GROUP BY Job_Fair_No, Selection_Status ORDER BY Job_Fair_No, Selection_Status')->fetchAll();
$pivotStatuses = [];
$pivotData = [];
$statusOrder = ['Selected', 'Shortlisted', 'OnHold'];
$statusAliases = [
    'onhold' => 'OnHold',
    'on hold' => 'OnHold',
];
foreach ($pivotRows as $pivotRow) {
    $jobFairNo = (string) ($pivotRow['Job_Fair_No'] ?? '');
    $rawStatus = (string) ($pivotRow['Selection_Status'] ?? 'Unknown');
    $statusKey = strtolower(trim($rawStatus));
    $status = $statusAliases[$statusKey] ?? $rawStatus;
    $total = (int) ($pivotRow['total_count'] ?? 0);
    if (!in_array($status, $pivotStatuses, true)) {
        $pivotStatuses[] = $status;
    }
    if (!isset($pivotData[$jobFairNo])) {
        $pivotData[$jobFairNo] = [];
    }
    $pivotData[$jobFairNo][$status] = ($pivotData[$jobFairNo][$status] ?? 0) + $total;
}
$orderedStatuses = [];
foreach ($statusOrder as $statusLabel) {
    if (in_array($statusLabel, $pivotStatuses, true)) {
        $orderedStatuses[] = $statusLabel;
    }
}
$remainingStatuses = array_values(array_diff($pivotStatuses, $orderedStatuses));
sort($remainingStatuses);
$pivotStatuses = [...$orderedStatuses, ...$remainingStatuses];
ksort($pivotData);
render_header('Dashboard');
?>
<h1 class="h3 mb-4">Welcome, <?= esc($user['name']) ?></h1>
<div class="row g-3 mb-3">
    <?php if ($user['role'] === 'administrator'): ?>
        <div class="col-12 col-md-6 col-lg-3"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">Active Users</p><h2 class="h4"><?= $totalUsers ?></h2><a href="/users.php">Manage</a></div></div></div>
    <?php endif; ?>
    <div class="col-12 col-md-6 col-lg-3"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">Selected</p><h2 class="h4"><?= $selectedCount ?></h2></div></div></div>
    <div class="col-12 col-md-6 col-lg-3"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">Shortlisted</p><h2 class="h4"><?= $shortlistedCount ?></h2></div></div></div>
    <div class="col-12 col-md-6 col-lg-3"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">On hold</p><h2 class="h4"><?= $onHoldCount ?></h2></div></div></div>
    <div class="col-12 col-md-6 col-lg-3"><div class="card card-stat"><div class="card-body"><p class="text-muted mb-1">Total Joined</p><h2 class="h4"><?= $totalJoinedCount ?></h2></div></div></div>
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

<div class="card mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h5 mb-0">Job Fair wise Status</h2>
            <a class="btn btn-sm btn-outline-primary" href="/job_fair_results.php">Open Job fair result data</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Job Fair No</th>
                        <?php foreach ($pivotStatuses as $pivotStatus): ?>
                            <th><?= esc($pivotStatus) ?></th>
                        <?php endforeach; ?>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($pivotData === []): ?>
                    <tr><td colspan="<?= count($pivotStatuses) + 2 ?>" class="text-center text-muted">No job fair result data available.</td></tr>
                <?php endif; ?>
                <?php $columnTotals = array_fill_keys($pivotStatuses, 0); $grandTotal = 0; ?>
                <?php foreach ($pivotData as $jobFairNo => $statusCounts): ?>
                    <?php $rowTotal = 0; ?>
                    <tr>
                        <td><?= esc($jobFairNo) ?></td>
                        <?php foreach ($pivotStatuses as $pivotStatus): ?>
                            <?php $value = (int) ($statusCounts[$pivotStatus] ?? 0); $rowTotal += $value; $columnTotals[$pivotStatus] += $value; ?>
                            <td><?= $value ?></td>
                        <?php endforeach; ?>
                        <td><strong><?= $rowTotal ?></strong></td>
                    </tr>
                    <?php $grandTotal += $rowTotal; ?>
                <?php endforeach; ?>
                <?php if ($pivotData !== []): ?>
                    <tr>
                        <td><strong>Total</strong></td>
                        <?php foreach ($pivotStatuses as $pivotStatus): ?>
                            <td><strong><?= $columnTotals[$pivotStatus] ?></strong></td>
                        <?php endforeach; ?>
                        <td><strong><?= $grandTotal ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer(); ?>
