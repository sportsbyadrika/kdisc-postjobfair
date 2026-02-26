<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

$user = current_user();

db()->exec(
    "CREATE TABLE IF NOT EXISTS candidate_call_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        candidate_id INT NOT NULL,
        stage ENUM('Employer Connect','Candidate Connect') NOT NULL,
        call_datetime DATETIME NOT NULL,
        call_status ENUM('Attended','Not attended','Invalid number') NOT NULL,
        call_remarks TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_candidate_call_history_candidate_id (candidate_id),
        CONSTRAINT fk_candidate_call_history_candidate
            FOREIGN KEY (candidate_id) REFERENCES job_fair_result(id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$editableFieldConfig = [
    [
        'panel_label' => 'Shortlist/Onhold',
        'field_name' => 'Shortlist_Prepratory_Call_Date',
        'field_type' => 'label',
        'group_label' => 'Shortlist Process',
        'row_position' => 1,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Shortlist/Onhold',
        'field_name' => 'Shortlist_Preparatory_Call_Status',
        'field_type' => "enum('Yes','No','Pending')",
        'group_label' => 'Shortlist Process',
        'row_position' => 1,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Shortlist/Onhold',
        'field_name' => 'Shortlist_Next_Process',
        'field_type' => 'varchar',
        'group_label' => 'Shortlist Process',
        'row_position' => 2,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Shortlist/Onhold',
        'field_name' => 'Shortlist_Number_of_Rounds',
        'field_type' => 'varchar',
        'group_label' => 'Shortlist Process',
        'row_position' => 3,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Shortlist/Onhold',
        'field_name' => 'Shortlist_Process_Deadline_Date',
        'field_type' => 'Date time textbox',
        'group_label' => 'Shortlist Process',
        'row_position' => 3,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Shortlist/Onhold',
        'field_name' => 'Shortlist_Current_Call_Status',
        'field_type' => "enum('Yes','No','Pending')",
        'group_label' => 'Shortlist Process',
        'row_position' => 4,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Shortlist/Onhold',
        'field_name' => 'Shortlist_Current_Process_Status',
        'field_type' => "enum('Completed','Pending')",
        'group_label' => 'Shortlist Process',
        'row_position' => 4,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Shortlist/Onhold',
        'field_name' => 'Shortlist_Candidate_Status',
        'field_type' => "enum('Shortlisted','Selected','Rejected','Onhold')",
        'group_label' => 'Shortlist Process',
        'row_position' => 5,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'First_Call_Date',
        'field_type' => 'label',
        'group_label' => 'First Call',
        'row_position' => 1,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'First_Call_Done',
        'field_type' => "enum('Yes','No','Pending')",
        'group_label' => 'First Call',
        'row_position' => 1,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Offer_Letter_Generated',
        'field_type' => "enum('Yes','No','Pending')",
        'group_label' => 'Offer Letter Generation',
        'row_position' => 2,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Offer_Letter_Generated_Date',
        'field_type' => 'Date time textbox',
        'group_label' => 'Offer Letter Generation',
        'row_position' => 2,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Link_to_Offer_letter',
        'field_type' => 'varchar(1000)',
        'group_label' => 'Offer Letter Link',
        'row_position' => 3,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Link_to_Offer_letter_verified',
        'field_type' => "enum('Yes','No')",
        'group_label' => 'Offer Letter Link',
        'row_position' => 4,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Confirm_Offer_Letter_Receipt_by_Candidate',
        'field_type' => "enum('Yes','No','Pending')",
        'group_label' => 'Offer Letter Link',
        'row_position' => 4,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'confirmation_date',
        'field_type' => 'Date time textbox',
        'group_label' => 'Offer Letter Link',
        'row_position' => 4,
        'column_position' => 3,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Offer_Letter_Join_Date',
        'field_type' => 'Date time textbox',
        'group_label' => 'Offer Letter Link',
        'row_position' => 5,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Willing_to_Join',
        'field_type' => "enum('Yes','No')",
        'group_label' => 'Offer Letter Link',
        'row_position' => 5,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'response_from_employer',
        'field_type' => 'varchar(1000)',
        'group_label' => 'Employer Response',
        'row_position' => 6,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Challenge_Type',
        'field_type' => 'varchar',
        'group_label' => 'Challenges to report',
        'row_position' => 7,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Challenge_to_be_addressed',
        'field_type' => 'varchar',
        'group_label' => 'Challenges to report',
        'row_position' => 7,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Candidate_Joined_Status',
        'field_type' => "enum('Yes','No','Pending','Not Applicable')",
        'group_label' => 'Candidate Joined details',
        'row_position' => 8,
        'column_position' => 1,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Candidate_Joined_Date',
        'field_type' => 'Date time textbox',
        'group_label' => 'Candidate Joined details',
        'row_position' => 8,
        'column_position' => 2,
    ],
    [
        'panel_label' => 'Selected',
        'field_name' => 'Remarks_Candidate_Join',
        'field_type' => 'varchar',
        'group_label' => 'Candidate Joined details',
        'row_position' => 9,
        'column_position' => 1,
    ],
];

$editableFieldMap = [];
foreach ($editableFieldConfig as $config) {
    $editableFieldMap[$config['field_name']] = $config;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateId = (int) ($_POST['candidate_id'] ?? 0);

    if ($candidateId > 0) {
        $setClauses = [];
        $updateValues = [];

        foreach ($editableFieldMap as $fieldName => $fieldConfig) {
            if (($fieldConfig['field_type'] ?? '') === 'label') {
                continue;
            }

            $value = trim((string) ($_POST[$fieldName] ?? ''));
            $value = $value === '' ? null : $value;
            $setClauses[] = "$fieldName = ?";
            $updateValues[] = $value;
        }

        if ($setClauses !== []) {
            $updateValues[] = $candidateId;
            $updateSql = 'UPDATE job_fair_result SET ' . implode(', ', $setClauses) . ' WHERE id = ?';
            $updateStmt = db()->prepare($updateSql);
            $updateStmt->execute($updateValues);
        }

        $callHistoryStage = trim((string) ($_POST['call_history_stage'] ?? ''));
        $callHistoryDateTime = trim((string) ($_POST['call_history_call_datetime'] ?? ''));
        $callHistoryStatus = trim((string) ($_POST['call_history_call_status'] ?? ''));
        $callHistoryRemarks = trim((string) ($_POST['call_history_call_remarks'] ?? ''));

        if ($callHistoryStage !== '' && $callHistoryStatus !== '' && $callHistoryDateTime !== '') {
            $callHistoryStmt = db()->prepare(
                'INSERT INTO candidate_call_history (candidate_id, stage, call_datetime, call_status, call_remarks) VALUES (?, ?, ?, ?, ?)'
            );
            $callHistoryStmt->execute([
                $candidateId,
                $callHistoryStage,
                str_replace('T', ' ', $callHistoryDateTime),
                $callHistoryStatus,
                $callHistoryRemarks === '' ? null : $callHistoryRemarks,
            ]);
        }
    }

    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    $redirectTarget = '/job_fair_results.php' . ($queryString !== '' ? ('?' . $queryString) : '');
    header('Location: ' . $redirectTarget);
    exit;
}

if (isset($_GET['candidate_call_history'])) {
    $candidateId = (int) ($_GET['candidate_call_history'] ?? 0);
    $historyStmt = db()->prepare('SELECT id, stage, call_datetime, call_status, call_remarks FROM candidate_call_history WHERE candidate_id = ? ORDER BY call_datetime DESC, id DESC');
    $historyStmt->execute([$candidateId]);

    header('Content-Type: application/json');
    echo json_encode($historyStmt->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$selectionStatusFilter = trim($_GET['selection_status'] ?? '');
$jobFairNoFilter = trim($_GET['job_fair_no'] ?? '');
$employerNameFilter = trim($_GET['employer_name'] ?? '');
$crmMemberFilter = trim($_GET['crm_member'] ?? '');
$dsmMember1Filter = trim($_GET['dsm_member_1'] ?? '');
$dsmMember2Filter = trim($_GET['dsm_member_2'] ?? '');
$page = max((int) ($_GET['page'] ?? 1), 1);
$perPage = 25;

$selectionStatuses = db()->query("SELECT DISTINCT Selection_Status FROM job_fair_result WHERE Selection_Status IS NOT NULL AND Selection_Status <> '' ORDER BY Selection_Status")->fetchAll();
$jobFairNos = db()->query("SELECT DISTINCT Job_Fair_No FROM job_fair_result WHERE Job_Fair_No IS NOT NULL AND Job_Fair_No <> '' ORDER BY Job_Fair_No")->fetchAll();
$employerNames = db()->query("SELECT DISTINCT Employer_Name FROM job_fair_result WHERE Employer_Name IS NOT NULL AND Employer_Name <> '' ORDER BY Employer_Name")->fetchAll();
$crmMembers = db()->query("SELECT DISTINCT CRM_Member FROM job_fair_result WHERE CRM_Member IS NOT NULL AND CRM_Member <> '' ORDER BY CRM_Member")->fetchAll();
$dsmMember1s = db()->query("SELECT DISTINCT DSM_Member_1 FROM job_fair_result WHERE DSM_Member_1 IS NOT NULL AND DSM_Member_1 <> '' ORDER BY DSM_Member_1")->fetchAll();
$dsmMember2s = db()->query("SELECT DISTINCT DSM_Member_2 FROM job_fair_result WHERE DSM_Member_2 IS NOT NULL AND DSM_Member_2 <> '' ORDER BY DSM_Member_2")->fetchAll();

$whereSql = ' FROM job_fair_result WHERE 1=1';
$params = [];

if ($selectionStatusFilter !== '') {
    $whereSql .= ' AND Selection_Status = ?';
    $params[] = $selectionStatusFilter;
}
if ($jobFairNoFilter !== '') {
    $whereSql .= ' AND Job_Fair_No = ?';
    $params[] = $jobFairNoFilter;
}
if ($employerNameFilter !== '') {
    $whereSql .= ' AND Employer_Name = ?';
    $params[] = $employerNameFilter;
}
if ($crmMemberFilter !== '') {
    $whereSql .= ' AND CRM_Member = ?';
    $params[] = $crmMemberFilter;
}
if ($dsmMember1Filter !== '') {
    $whereSql .= ' AND DSM_Member_1 = ?';
    $params[] = $dsmMember1Filter;
}
if ($dsmMember2Filter !== '') {
    $whereSql .= ' AND DSM_Member_2 = ?';
    $params[] = $dsmMember2Filter;
}
$countStmt = db()->prepare('SELECT COUNT(*)' . $whereSql);
$countStmt->execute($params);
$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = max((int) ceil($totalRecords / $perPage), 1);
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = 'SELECT *' . $whereSql . ' ORDER BY Data_uploaded_date DESC, id DESC LIMIT ? OFFSET ?';
$stmt = db()->prepare($sql);
$queryParams = [...$params, $perPage, $offset];
$stmt->execute($queryParams);
$rows = $stmt->fetchAll();

render_header('Job fair result data');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Job fair result data</h1>
    <div class="d-flex align-items-center gap-2">
        <?php if ($user['role'] === 'administrator'): ?>
            <a class="btn btn-sm btn-outline-primary" href="/job_fair_result_upload.php">Upload CSV</a>
        <?php endif; ?>
        <span class="badge bg-primary-subtle text-primary-emphasis">Records: <?= $totalRecords ?></span>
    </div>
</div>

<?php
$baseParams = $_GET;
unset($baseParams['page'], $baseParams['candidate_call_history']);
?>

<?php if ($totalPages > 1): ?>
    <nav aria-label="Job fair result pagination" class="mb-4">
        <ul class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php $pageUrl = '/job_fair_results.php?' . http_build_query([...$baseParams, 'page' => $p]); ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= esc($pageUrl) ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

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
                        <th>Job Fair / Status</th>
                        <th>Candidate</th>
                        <th>Employer</th>
                        <th>Job</th>
                        <th>Days since Job Fair Date</th>
                        <th>Offer Letter Generated</th>
                        <th>Offer Letter Verified</th>
                        <th>Offer Receipt Confirmed</th>
                        <th>Candidate Joined Status</th>
                        <th>Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No results found for the selected filters.</td>
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
                            <td>
                                <div><?= esc($row['Job_Fair_No'] ?: 'N/A') ?></div>
                                <div class="small text-muted">Status: <?= esc($row['Selection_Status'] ?: 'N/A') ?></div>
                            </td>
                            <td>
                                <div><?= esc($row['DWMS_ID'] ?: 'N/A') ?></div>
                                <div class="small text-muted"><?= esc($row['Candidate_Name'] ?: 'N/A') ?></div>
                            </td>
                            <td>
                                <div><?= esc($row['Employer_ID'] ?: 'N/A') ?></div>
                                <div class="small text-muted"><?= esc($row['Employer_Name'] ?: 'N/A') ?></div>
                            </td>
                            <td>
                                <div><?= esc($row['Job_Id'] ?: 'N/A') ?></div>
                                <div class="small text-muted"><?= esc($row['Job_Title_Name'] ?: 'N/A') ?></div>
                            </td>
                            <td><?= $daySinceJobFair !== null ? $daySinceJobFair : 'N/A' ?></td>
                            <td><?= esc($row['Offer_Letter_Generated'] ?: 'N/A') ?></td>
                            <td><?= esc($row['Link_to_Offer_letter_verified'] ?: 'N/A') ?></td>
                            <td><?= esc($row['Confirm_Offer_Letter_Receipt_by_Candidate'] ?: 'N/A') ?></td>
                            <td><?= esc($row['Candidate_Joined_Status'] ?: 'N/A') ?></td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary edit-row-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#manageCandidateModal"
                                    data-row='<?= esc(json_encode($row, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG | JSON_HEX_QUOT)) ?>'
                                    aria-label="Edit candidate"
                                >
                                    ✏️
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
body.modal-open {
    overflow-y: auto !important;
}
</style>

<div class="modal fade" id="manageCandidateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" id="manageCandidateForm">
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Manage Candidate</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="candidate_id" id="modalCandidateId">
                    <div class="card mb-3">
                        <div class="card-header">Candidate Details</div>
                        <div class="card-body">
                            <div class="row g-3" id="candidateDetailPanel"></div>
                        </div>
                    </div>
                    <div id="dynamicPanels"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Update</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const fieldConfig = <?= json_encode($editableFieldConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const callHistoryStageOptions = ['Employer Connect', 'Candidate Connect'];
const callHistoryStatusOptions = ['Attended', 'Not attended', 'Invalid number'];

function toInputDatetime(value) {
    if (!value) return '';
    return String(value).replace(' ', 'T').slice(0, 16);
}

function formatLabel(fieldName) {
    return fieldName.replaceAll('_', ' ');
}

function enumValues(type) {
    const match = type.match(/^enum\((.+)\)$/i);
    if (!match) return [];
    return match[1].split(',').map((value) => value.trim().replace(/^'+|'+$/g, ''));
}

function renderFieldControl(config, row) {
    const value = row[config.field_name] ?? '';
    const labelHtml = `<label class="form-label">${formatLabel(config.field_name)}</label>`;

    if (config.field_type === 'label') {
        return `${labelHtml}<div class="form-control bg-light">${value || 'N/A'}</div>`;
    }

    if (config.field_type.toLowerCase().startsWith('enum(')) {
        const options = enumValues(config.field_type)
            .map((option) => `<option value="${option}" ${option === value ? 'selected' : ''}>${option}</option>`)
            .join('');
        return `${labelHtml}<select class="form-select" name="${config.field_name}"><option value="">Select</option>${options}</select>`;
    }

    if (config.field_type.toLowerCase().includes('date time')) {
        return `${labelHtml}<input type="datetime-local" class="form-control" name="${config.field_name}" value="${toInputDatetime(value)}">`;
    }

    return `${labelHtml}<input type="text" class="form-control" name="${config.field_name}" value="${value || ''}">`;
}

function renderPanels(row) {
    const dynamicPanels = document.getElementById('dynamicPanels');
    const detailPanel = document.getElementById('candidateDetailPanel');

    const details = [
        ['Job_Fair_No', row.Job_Fair_No],
        ['Selection_Status', row.Selection_Status],
        ['DWMS_ID', row.DWMS_ID],
        ['Candidate_Name', row.Candidate_Name],
        ['Employer_ID', row.Employer_ID],
        ['Employer_Name', row.Employer_Name],
        ['Job_Id', row.Job_Id],
        ['Job_Title_Name', row.Job_Title_Name],
        ['CRM_Member', row.CRM_Member],
        ['DSM_Member_1', row.DSM_Member_1],
        ['DSM_Member_2', row.DSM_Member_2],
        ['Job_Fair_Date', row.Job_Fair_Date]
    ];

    detailPanel.innerHTML = details
        .map(([name, value]) => `<div class="col-12 col-md-4"><label class="form-label text-muted small">${formatLabel(name)}</label><div class="form-control bg-light">${value || 'N/A'}</div></div>`)
        .join('');

    const availablePanels = row.Selection_Status === 'Selected'
        ? ['Selected', 'Call History']
        : ['Shortlist/Onhold', 'Selected', 'Call History'];

    const panelTabs = availablePanels.map((panel, index) => `
        <li class="nav-item" role="presentation">
            <button class="nav-link ${index === 0 ? 'active' : ''}" data-bs-toggle="tab" data-bs-target="#panel-${panel.replace(/[^a-zA-Z0-9]/g, '')}" type="button">${panel}</button>
        </li>
    `).join('');

    const panelBodies = availablePanels.map((panel, index) => {
        const panelKey = panel.replace(/[^a-zA-Z0-9]/g, '');
        if (panel === 'Call History') {
            return `
                <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" id="panel-${panelKey}">
                    <div class="card mb-3">
                        <div class="card-header">Add Call Detail</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Stage</label>
                                    <select class="form-select" name="call_history_stage">
                                        <option value="">Select</option>
                                        ${callHistoryStageOptions.map((option) => `<option value="${option}">${option}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Call Date time</label>
                                    <input type="datetime-local" class="form-control" name="call_history_call_datetime" value="${toInputDatetime(new Date().toISOString().slice(0, 19).replace('T', ' '))}" readonly>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Call Status</label>
                                    <select class="form-select" name="call_history_call_status">
                                        <option value="">Select</option>
                                        ${callHistoryStatusOptions.map((option) => `<option value="${option}">${option}</option>`).join('')}
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Call Remarks</label>
                                    <textarea class="form-control" name="call_history_call_remarks" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">Call History</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Sl no</th>
                                            <th>Stage</th>
                                            <th>Date time</th>
                                            <th>Call status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="callHistoryBody"><tr><td colspan="5" class="text-center text-muted">Loading call history...</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        const panelFields = fieldConfig.filter((field) => field.panel_label === panel);
        const groups = [...new Set(panelFields.map((field) => field.group_label))];

        const groupHtml = groups.map((group) => {
            const fields = panelFields
                .filter((field) => field.group_label === group)
                .sort((a, b) => (a.row_position - b.row_position) || (a.column_position - b.column_position));

            const maxColumns = Math.max(...fields.map((field) => field.column_position));
            const colClass = `col-md-${Math.max(12 / maxColumns, 3)}`;

            return `
                <div class="card mb-3">
                    <div class="card-header">${group}</div>
                    <div class="card-body">
                        <div class="row g-3">
                            ${fields.map((field) => `<div class="col-12 ${colClass}">${renderFieldControl(field, row)}</div>`).join('')}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="tab-pane fade ${index === 0 ? 'show active' : ''}" id="panel-${panelKey}">
                ${groupHtml}
            </div>
        `;
    }).join('');

    dynamicPanels.innerHTML = `
        <ul class="nav nav-tabs mb-3" role="tablist">${panelTabs}</ul>
        <div class="tab-content">${panelBodies}</div>
    `;
}

function renderCallHistoryRows(historyRows) {
    const body = document.getElementById('callHistoryBody');
    if (!body) {
        return;
    }

    if (!historyRows.length) {
        body.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No call history found.</td></tr>';
        return;
    }

    body.innerHTML = historyRows.map((item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${item.stage || 'N/A'}</td>
            <td>${item.call_datetime || 'N/A'}</td>
            <td>${item.call_status || 'N/A'}</td>
            <td>${item.call_remarks || 'N/A'}</td>
        </tr>
    `).join('');
}

function loadCallHistory(candidateId) {
    fetch(`/job_fair_results.php?candidate_call_history=${candidateId}`)
        .then((response) => response.json())
        .then((historyRows) => {
            renderCallHistoryRows(Array.isArray(historyRows) ? historyRows : []);
        })
        .catch(() => {
            renderCallHistoryRows([]);
        });
}

document.querySelectorAll('.edit-row-btn').forEach((button) => {
    button.addEventListener('click', () => {
        const row = JSON.parse(button.dataset.row);
        document.getElementById('modalCandidateId').value = row.id;
        renderPanels(row);
        loadCallHistory(row.id);
    });
});
</script>
<?php render_footer(); ?>
