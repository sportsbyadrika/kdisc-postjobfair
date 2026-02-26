<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

$user = current_user();

$selectionStatusFilter = trim($_GET['selection_status'] ?? '');
$jobFairNoFilter = trim($_GET['job_fair_no'] ?? '');
$employerNameFilter = trim($_GET['employer_name'] ?? '');
$crmMemberFilter = trim($_GET['crm_member'] ?? '');
$dsmMember1Filter = trim($_GET['dsm_member_1'] ?? '');
$dsmMember2Filter = trim($_GET['dsm_member_2'] ?? '');

$selectionStatuses = db()->query("SELECT DISTINCT Selection_Status FROM job_fair_result WHERE Selection_Status IS NOT NULL AND Selection_Status <> '' ORDER BY Selection_Status")->fetchAll();
$jobFairNos = db()->query("SELECT DISTINCT Job_Fair_No FROM job_fair_result WHERE Job_Fair_No IS NOT NULL AND Job_Fair_No <> '' ORDER BY Job_Fair_No")->fetchAll();
$crmMembers = db()->query("SELECT DISTINCT CRM_Member FROM job_fair_result WHERE CRM_Member IS NOT NULL AND CRM_Member <> '' ORDER BY CRM_Member")->fetchAll();
$dsmMember1s = db()->query("SELECT DISTINCT DSM_Member_1 FROM job_fair_result WHERE DSM_Member_1 IS NOT NULL AND DSM_Member_1 <> '' ORDER BY DSM_Member_1")->fetchAll();
$dsmMember2s = db()->query("SELECT DISTINCT DSM_Member_2 FROM job_fair_result WHERE DSM_Member_2 IS NOT NULL AND DSM_Member_2 <> '' ORDER BY DSM_Member_2")->fetchAll();

$sql = 'SELECT * FROM job_fair_result WHERE 1=1';
$params = [];

if ($selectionStatusFilter !== '') {
    $sql .= ' AND Selection_Status = ?';
    $params[] = $selectionStatusFilter;
}
if ($jobFairNoFilter !== '') {
    $sql .= ' AND Job_Fair_No = ?';
    $params[] = $jobFairNoFilter;
}
if ($employerNameFilter !== '') {
    $sql .= ' AND Employer_Name LIKE ?';
    $params[] = '%' . $employerNameFilter . '%';
}
if ($crmMemberFilter !== '') {
    $sql .= ' AND CRM_Member = ?';
    $params[] = $crmMemberFilter;
}
if ($dsmMember1Filter !== '') {
    $sql .= ' AND DSM_Member_1 = ?';
    $params[] = $dsmMember1Filter;
}
if ($dsmMember2Filter !== '') {
    $sql .= ' AND DSM_Member_2 = ?';
    $params[] = $dsmMember2Filter;
}

$sql .= ' ORDER BY Data_uploaded_date DESC, id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pivotRows = db()->query('SELECT Job_Fair_No, Selection_Status, COUNT(*) AS total_count FROM job_fair_result GROUP BY Job_Fair_No, Selection_Status ORDER BY Job_Fair_No, Selection_Status')->fetchAll();
$pivotStatuses = [];
$pivotData = [];

foreach ($pivotRows as $pivotRow) {
    $jobFairNo = (string) ($pivotRow['Job_Fair_No'] ?? '');
    $status = (string) ($pivotRow['Selection_Status'] ?? 'Unknown');
    $total = (int) ($pivotRow['total_count'] ?? 0);

    if (!in_array($status, $pivotStatuses, true)) {
        $pivotStatuses[] = $status;
    }

    if (!isset($pivotData[$jobFairNo])) {
        $pivotData[$jobFairNo] = [];
    }

    $pivotData[$jobFairNo][$status] = $total;
}

sort($pivotStatuses);
ksort($pivotData);

render_header('Job fair result data');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Job fair result data</h1>
    <span class="badge bg-primary-subtle text-primary-emphasis">Records: <?= count($rows) ?></span>
</div>

<form method="get" class="card mb-4">
    <div class="card-body">
        <h2 class="h6 mb-3">Filters</h2>
        <div class="row g-2">
            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label">Selection Status</label>
                <select class="form-select" name="selection_status">
                    <option value="">All</option>
                    <?php foreach ($selectionStatuses as $status): ?>
                        <option value="<?= esc($status['Selection_Status']) ?>" <?= $selectionStatusFilter === $status['Selection_Status'] ? 'selected' : '' ?>><?= esc($status['Selection_Status']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label">Job Fair No</label>
                <select class="form-select" name="job_fair_no">
                    <option value="">All</option>
                    <?php foreach ($jobFairNos as $jobFairNo): ?>
                        <option value="<?= esc($jobFairNo['Job_Fair_No']) ?>" <?= $jobFairNoFilter === $jobFairNo['Job_Fair_No'] ? 'selected' : '' ?>><?= esc($jobFairNo['Job_Fair_No']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label">Employer Name</label>
                <input type="text" name="employer_name" class="form-control" value="<?= esc($employerNameFilter) ?>" placeholder="Employer name">
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label">CRM Member</label>
                <select class="form-select" name="crm_member">
                    <option value="">All</option>
                    <?php foreach ($crmMembers as $crmMember): ?>
                        <option value="<?= esc($crmMember['CRM_Member']) ?>" <?= $crmMemberFilter === $crmMember['CRM_Member'] ? 'selected' : '' ?>><?= esc($crmMember['CRM_Member']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label">DSM Member 1</label>
                <select class="form-select" name="dsm_member_1">
                    <option value="">All</option>
                    <?php foreach ($dsmMember1s as $dsmMember1): ?>
                        <option value="<?= esc($dsmMember1['DSM_Member_1']) ?>" <?= $dsmMember1Filter === $dsmMember1['DSM_Member_1'] ? 'selected' : '' ?>><?= esc($dsmMember1['DSM_Member_1']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label">DSM Member 2</label>
                <select class="form-select" name="dsm_member_2">
                    <option value="">All</option>
                    <?php foreach ($dsmMember2s as $dsmMember2): ?>
                        <option value="<?= esc($dsmMember2['DSM_Member_2']) ?>" <?= $dsmMember2Filter === $dsmMember2['DSM_Member_2'] ? 'selected' : '' ?>><?= esc($dsmMember2['DSM_Member_2']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-3">
                <button class="btn btn-primary" type="submit">Apply filters</button>
                <a class="btn btn-outline-secondary" href="/job_fair_results.php">Reset</a>
            </div>
        </div>
    </div>
</form>

<div class="row g-3 mb-4">
    <?php if ($rows === []): ?>
        <div class="col-12"><div class="alert alert-info mb-0">No results found for the selected filters.</div></div>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
        <?php
        $daySinceJobFair = null;
        if (!empty($row['Job_Fair_Date'])) {
            $jobFairDate = new DateTime($row['Job_Fair_Date']);
            $today = new DateTime();
            $daySinceJobFair = (int) $jobFairDate->diff($today)->format('%a');
        }
        ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 job-fair-result-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h2 class="h6 mb-0"><?= esc($row['Candidate_Name'] ?: 'N/A') ?></h2>
                        <span class="badge bg-secondary"><?= esc($row['Selection_Status'] ?: 'N/A') ?></span>
                    </div>
                    <ul class="list-unstyled small mb-0">
                        <li><strong>Job Fair No:</strong> <?= esc($row['Job_Fair_No']) ?></li>
                        <li><strong>DWMS ID:</strong> <?= esc($row['DWMS_ID']) ?></li>
                        <li><strong>Days since Job Fair Date:</strong> <?= $daySinceJobFair !== null ? $daySinceJobFair : 'N/A' ?></li>
                        <li><strong>Offer Letter Generated:</strong> <?= esc($row['Offer_Letter_Generated'] ?: 'N/A') ?></li>
                        <li><strong>Offer Letter Verified:</strong> <?= esc($row['Link_to_Offer_letter_verified'] ?: 'N/A') ?></li>
                        <li><strong>Offer Receipt Confirmed:</strong> <?= esc($row['Confirm_Offer_Letter_Receipt_by_Candidate'] ?: 'N/A') ?></li>
                        <li><strong>Candidate Joined Status:</strong> <?= esc($row['Candidate_Joined_Status'] ?: 'N/A') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h5">Job fair vs selection status pivot</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>Job Fair No</th>
                        <?php foreach ($pivotStatuses as $pivotStatus): ?>
                            <th><?= esc($pivotStatus) ?></th>
                        <?php endforeach; ?>
                        <th>Row Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pivotData === []): ?>
                        <tr>
                            <td colspan="<?= count($pivotStatuses) + 2 ?>" class="text-center text-muted">No pivot data available.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($pivotData as $jobFairNo => $statusCounts): ?>
                        <?php $rowTotal = 0; ?>
                        <tr>
                            <td><?= esc($jobFairNo) ?></td>
                            <?php foreach ($pivotStatuses as $pivotStatus): ?>
                                <?php $value = (int) ($statusCounts[$pivotStatus] ?? 0); $rowTotal += $value; ?>
                                <td><?= $value ?></td>
                            <?php endforeach; ?>
                            <td><strong><?= $rowTotal ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer(); ?>
