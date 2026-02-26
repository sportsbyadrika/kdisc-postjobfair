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
$employerNames = db()->query("SELECT DISTINCT Employer_Name FROM job_fair_result WHERE Employer_Name IS NOT NULL AND Employer_Name <> '' ORDER BY Employer_Name")->fetchAll();
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
    $sql .= ' AND Employer_Name = ?';
    $params[] = $employerNameFilter;
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
    <div class="d-flex align-items-center gap-2">
        <?php if ($user['role'] === 'administrator'): ?>
            <a class="btn btn-sm btn-outline-primary" href="/job_fair_result_upload.php">Upload CSV</a>
        <?php endif; ?>
        <span class="badge bg-primary-subtle text-primary-emphasis">Records: <?= count($rows) ?></span>
    </div>
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
                <select class="form-select" name="employer_name">
                    <option value="">All</option>
                    <?php foreach ($employerNames as $employerName): ?>
                        <option value="<?= esc($employerName['Employer_Name']) ?>" <?= $employerNameFilter === $employerName['Employer_Name'] ? 'selected' : '' ?>><?= esc($employerName['Employer_Name']) ?></option>
                    <?php endforeach; ?>
                </select>
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

<div class="card mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Candidate Name</th>
                        <th>Employer Name</th>
                        <th>Job Title</th>
                        <th>Selection Status</th>
                        <th>Job Fair No</th>
                        <th>DWMS ID</th>
                        <th>Days since Job Fair Date</th>
                        <th>Offer Letter Generated</th>
                        <th>Offer Letter Verified</th>
                        <th>Offer Receipt Confirmed</th>
                        <th>Candidate Joined Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted">No results found for the selected filters.</td>
                        </tr>
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
                        <tr>
                            <td><?= esc($row['Candidate_Name'] ?: 'N/A') ?></td>
                            <td><?= esc($row['Employer_Name'] ?: 'N/A') ?></td>
                            <td><?= esc($row['Job_Title_Name'] ?: 'N/A') ?></td>
                            <td><span class="badge bg-secondary"><?= esc($row['Selection_Status'] ?: 'N/A') ?></span></td>
                            <td><?= esc($row['Job_Fair_No']) ?></td>
                            <td><?= esc($row['DWMS_ID']) ?></td>
                            <td><?= $daySinceJobFair !== null ? $daySinceJobFair : 'N/A' ?></td>
                            <td><?= esc($row['Offer_Letter_Generated'] ?: 'N/A') ?></td>
                            <td><?= esc($row['Link_to_Offer_letter_verified'] ?: 'N/A') ?></td>
                            <td><?= esc($row['Confirm_Offer_Letter_Receipt_by_Candidate'] ?: 'N/A') ?></td>
                            <td><?= esc($row['Candidate_Joined_Status'] ?: 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h5">Job Fair wise Status</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
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
                        <tr>
                            <td colspan="<?= count($pivotStatuses) + 2 ?>" class="text-center text-muted">No pivot data available.</td>
                        </tr>
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
