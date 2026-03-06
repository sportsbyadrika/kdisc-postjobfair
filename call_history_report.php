<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

$user = current_user();

db()->query(
    "CREATE TABLE IF NOT EXISTS candidate_call_purpose (
        id INT AUTO_INCREMENT PRIMARY KEY,
        purpose_name VARCHAR(255) NOT NULL UNIQUE,
        active_status TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$hasPurposeIdColumnStmt = db()->query("SHOW COLUMNS FROM candidate_call_history LIKE 'purpose_id'");
$hasPurposeIdColumnRows = $hasPurposeIdColumnStmt->fetchAll();
$hasPurposeIdColumn = $hasPurposeIdColumnRows !== [];
if (!$hasPurposeIdColumn) {
    db()->query("ALTER TABLE candidate_call_history ADD COLUMN purpose_id INT DEFAULT NULL AFTER stage");
    db()->query("ALTER TABLE candidate_call_history ADD INDEX idx_candidate_call_history_purpose_id (purpose_id)");
    db()->query(
        "ALTER TABLE candidate_call_history
            ADD CONSTRAINT fk_candidate_call_history_purpose
                FOREIGN KEY (purpose_id) REFERENCES candidate_call_purpose(id)
                ON DELETE SET NULL"
    );
}

$crmMemberRows = db()->query(
    "SELECT DISTINCT CRM_Member
     FROM job_fair_result
     WHERE CRM_Member IS NOT NULL AND TRIM(CRM_Member) <> ''
     ORDER BY CRM_Member"
)->fetchAll();

$crmMemberOptions = [];
foreach ($crmMemberRows as $crmMemberRow) {
    $crmMemberValue = $crmMemberRow;
    if (is_array($crmMemberRow)) {
        $crmMemberValue = $crmMemberRow['CRM_Member'] ?? reset($crmMemberRow);
    }

    $crmMemberValue = trim((string) $crmMemberValue);
    if ($crmMemberValue !== '') {
        $crmMemberOptions[] = $crmMemberValue;
    }
}

$selectedMember = trim((string) ($_GET['crm_member'] ?? ''));
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$hasDateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1;
$hasDateTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) === 1;

$baseConditions = ["j.CRM_Member IS NOT NULL", "TRIM(j.CRM_Member) <> ''"];
$baseParams = [];
if ($selectedMember !== '') {
    $baseConditions[] = 'j.CRM_Member = ?';
    $baseParams[] = $selectedMember;
}
if ($hasDateFrom) {
    $baseConditions[] = 'h.call_datetime >= ?';
    $baseParams[] = $dateFrom . ' 00:00:00';
}
if ($hasDateTo) {
    $baseConditions[] = 'h.call_datetime <= ?';
    $baseParams[] = $dateTo . ' 23:59:59';
}
$whereSql = ' WHERE ' . implode(' AND ', $baseConditions);

$summarySql = "
    SELECT
        j.CRM_Member,
        COUNT(h.id) AS total_calls,
        SUM(CASE WHEN h.stage = 'Employer Connect' THEN 1 ELSE 0 END) AS employer_connect_calls,
        SUM(CASE WHEN h.stage = 'Candidate Connect' THEN 1 ELSE 0 END) AS candidate_connect_calls,
        SUM(CASE WHEN h.stage = 'Aggregator Contact' THEN 1 ELSE 0 END) AS aggregator_contact_calls,
        SUM(CASE WHEN h.call_status = 'Attended' THEN 1 ELSE 0 END) AS attended_calls,
        SUM(CASE WHEN h.call_status = 'Not attended' THEN 1 ELSE 0 END) AS not_attended_calls,
        SUM(CASE WHEN h.call_status = 'Invalid number' THEN 1 ELSE 0 END) AS invalid_number_calls
    FROM candidate_call_history h
    INNER JOIN job_fair_result j ON j.id = h.candidate_id
    LEFT JOIN candidate_call_purpose p ON p.id = h.purpose_id
    " . $whereSql . "
    GROUP BY j.CRM_Member
    ORDER BY total_calls DESC, j.CRM_Member";

$summaryStmt = db()->prepare($summarySql);
$summaryStmt->execute($baseParams);
$summaryRows = $summaryStmt->fetchAll();

$detailSql = "
    SELECT
        h.id,
        h.stage,
        h.call_datetime,
        COALESCE(p.purpose_name, '') AS purpose_name,
        h.call_status,
        h.call_remarks,
        j.CRM_Member,
        j.Job_Fair_No,
        j.Candidate_Name,
        j.Mobile_Number,
        j.Selection_Status
    FROM candidate_call_history h
    INNER JOIN job_fair_result j ON j.id = h.candidate_id
    LEFT JOIN candidate_call_purpose p ON p.id = h.purpose_id
    " . $whereSql . "
    ORDER BY j.CRM_Member, h.call_datetime DESC, h.id DESC";
$detailStmt = db()->prepare($detailSql);
$detailStmt->execute($baseParams);
$details = $detailStmt->fetchAll();

$detailsByMember = [];
foreach ($details as $detail) {
    $member = (string) ($detail['CRM_Member'] ?? 'Unassigned');
    $detailsByMember[$member][] = $detail;
}

render_header('Call History Report');
?>
<h1 class="h3 mb-3">User-wise Call History Report</h1>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-lg-3">
                <label for="crm_member" class="form-label">Filter by User</label>
                <select id="crm_member" name="crm_member" class="form-select">
                    <option value="">All Users</option>
                    <?php foreach ($crmMemberOptions as $memberOption): ?>
                        <option value="<?= esc((string) $memberOption) ?>" <?= $selectedMember === (string) $memberOption ? 'selected' : '' ?>>
                            <?= esc((string) $memberOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label for="date_from" class="form-label">Date from</label>
                <input type="date" id="date_from" name="date_from" class="form-control" value="<?= esc($hasDateFrom ? $dateFrom : '') ?>">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label for="date_to" class="form-label">Date to</label>
                <input type="date" id="date_to" name="date_to" class="form-control" value="<?= esc($hasDateTo ? $dateTo : '') ?>">
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="/call_history_report.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>User</th>
                <th>Total Calls</th>
                <th>Employer Connect</th>
                <th>Candidate Connect</th>
                <th>Aggregator Contact</th>
                <th>Attended</th>
                <th>Not attended</th>
                <th>Invalid number</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($summaryRows === []): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted">No call history records found.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($summaryRows as $row): ?>
                <?php $memberKey = (string) ($row['CRM_Member'] ?? 'Unassigned'); ?>
                <tr>
                    <td><?= esc($memberKey) ?></td>
                    <td><?= (int) $row['total_calls'] ?></td>
                    <td><?= (int) $row['employer_connect_calls'] ?></td>
                    <td><?= (int) $row['candidate_connect_calls'] ?></td>
                    <td><?= (int) $row['aggregator_contact_calls'] ?></td>
                    <td><?= (int) $row['attended_calls'] ?></td>
                    <td><?= (int) $row['not_attended_calls'] ?></td>
                    <td><?= (int) $row['invalid_number_calls'] ?></td>
                    <td>
                        <button
                            class="btn btn-sm btn-outline-primary px-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#call-history-<?= md5($memberKey) ?>"
                            aria-label="View details for <?= esc($memberKey) ?>"
                            title="View details"
                        >
                            <span aria-hidden="true">▾</span>
                            <span class="visually-hidden">View details</span>
                        </button>
                    </td>
                </tr>
                <tr class="collapse" id="call-history-<?= md5($memberKey) ?>">
                    <td colspan="9">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Call Date/Time</th>
                                        <th>Candidate</th>
                                        <th>Mobile</th>
                                        <th>Job Fair No</th>
                                        <th>Selection Status</th>
                                        <th>Stage</th>
                                        <th>Purpose</th>
                                        <th>Call Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (($detailsByMember[$memberKey] ?? []) as $detail): ?>
                                        <tr>
                                            <td><?= esc((string) $detail['call_datetime']) ?></td>
                                            <td><?= esc((string) ($detail['Candidate_Name'] ?? '-')) ?></td>
                                            <td><?= esc((string) ($detail['Mobile_Number'] ?? '-')) ?></td>
                                            <td><?= esc((string) ($detail['Job_Fair_No'] ?? '-')) ?></td>
                                            <td><?= esc((string) ($detail['Selection_Status'] ?? '-')) ?></td>
                                            <td><?= esc((string) $detail['stage']) ?></td>
                                            <td><?= esc((string) ($detail['purpose_name'] ?? '-')) ?></td>
                                            <td><?= esc((string) $detail['call_status']) ?></td>
                                            <td><?= esc((string) ($detail['call_remarks'] ?? '-')) ?></td>
                                        </tr>
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
