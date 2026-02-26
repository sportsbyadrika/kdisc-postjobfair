<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

$user = current_user();

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
    }

    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    $redirectTarget = '/job_fair_results.php' . ($queryString !== '' ? ('?' . $queryString) : '');
    header('Location: ' . $redirectTarget);
    exit;
}

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

<div class="modal fade" id="manageCandidateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
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
        .map(([name, value]) => `<div class="col-12 col-md-6"><label class="form-label text-muted small">${formatLabel(name)}</label><div class="form-control bg-light">${value || 'N/A'}</div></div>`)
        .join('');

    const availablePanels = row.Selection_Status === 'Selected'
        ? ['Selected']
        : ['Shortlist/Onhold', 'Selected'];

    const panelTabs = availablePanels.map((panel, index) => `
        <li class="nav-item" role="presentation">
            <button class="nav-link ${index === 0 ? 'active' : ''}" data-bs-toggle="tab" data-bs-target="#panel-${panel.replace(/[^a-zA-Z0-9]/g, '')}" type="button">${panel}</button>
        </li>
    `).join('');

    const panelBodies = availablePanels.map((panel, index) => {
        const panelKey = panel.replace(/[^a-zA-Z0-9]/g, '');
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

document.querySelectorAll('.edit-row-btn').forEach((button) => {
    button.addEventListener('click', () => {
        const row = JSON.parse(button.dataset.row);
        document.getElementById('modalCandidateId').value = row.id;
        renderPanels(row);
    });
});
</script>
<?php render_footer(); ?>
