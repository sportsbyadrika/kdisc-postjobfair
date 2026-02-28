<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

$candidateId = (int) ($_GET['candidate_id'] ?? 0);
$returnQuery = trim((string) ($_GET['return_query'] ?? ''));
$returnUrl = '/job_fair_results.php' . ($returnQuery !== '' ? ('?' . $returnQuery) : '');

if ($candidateId <= 0) {
    header('Location: ' . $returnUrl);
    exit;
}

$candidateStmt = db()->prepare('SELECT id, Candidate_Name, DWMS_ID, Selection_Status FROM job_fair_result WHERE id = ? LIMIT 1');
$candidateStmt->execute([$candidateId]);
$candidate = $candidateStmt->fetch();
if (!$candidate) {
    header('Location: ' . $returnUrl);
    exit;
}

render_header('Manage Candidate');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">Manage Candidate</h1>
        <div class="text-muted small"><?= esc($candidate['Candidate_Name'] ?: 'N/A') ?> (<?= esc($candidate['DWMS_ID'] ?: 'N/A') ?>)</div>
    </div>
    <a class="btn btn-outline-secondary" href="<?= esc($returnUrl) ?>">← Back to Results</a>
</div>

<form method="post" action="/job_fair_results.php" id="manageCandidateForm">
    <input type="hidden" name="candidate_id" value="<?= (int) $candidateId ?>" id="candidateId">
    <input type="hidden" name="modal_candidate_id" value="<?= (int) $candidateId ?>">
    <input type="hidden" name="modal_active_tab" value="" id="activeTabInput">
    <input type="hidden" name="return_to" value="manage_candidate.php">
    <input type="hidden" name="return_query" value="<?= esc($returnQuery) ?>">

    <div class="card mb-3">
        <div class="card-header">Candidate Details</div>
        <div class="card-body">
            <div class="row g-3" id="candidateDetailPanel"></div>
        </div>
    </div>

    <div id="dynamicPanels"></div>
</form>

<style>
.status-chip { display:inline-block; padding:0.2rem 0.55rem; border-radius:999px; font-size:0.75rem; font-weight:600; border:1px solid transparent; }
.status-selected { color:#146c43; background:#d1e7dd; border-color:#a3cfbb; }
.status-shortlisted { color:#7a3f00; background:#ffe5cc; border-color:#ffca99; }
.status-onhold { color:#084298; background:#cfe2ff; border-color:#9ec5fe; }
.status-rejected { color:#dc3545; background:#f8d7da; border-color:#f1aeb5; }
</style>

<script>
const candidateId = <?= json_encode($candidateId) ?>;
const detailPanel = document.getElementById('candidateDetailPanel');
const dynamicPanels = document.getElementById('dynamicPanels');
const activeTabInput = document.getElementById('activeTabInput');

let fieldConfig = [];
let callHistoryPurposeOptions = [];
let currentRow = null;

function escapeHtml(value) { return String(value ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;'); }
function formatLabel(name) { return String(name || '').replaceAll('_', ' '); }
function toInputDatetime(value) { return value ? String(value).replace(' ', 'T').slice(0,16) : ''; }
function enumValues(type) {
    const m = String(type || '').match(/^enum\((.+)\)$/i);
    if (!m) return [];
    return m[1].split(',').map((v) => v.trim().replace(/^'+|'+$/g, ''));
}

function renderFieldControl(config, row) {
    const value = row[config.field_name] ?? '';
    if (config.field_type === 'label') {
        return `<label class="form-label">${formatLabel(config.field_name)}</label><div class="form-control bg-light">${escapeHtml(value || 'N/A')}</div>`;
    }
    if (String(config.field_type).toLowerCase().startsWith('enum(')) {
        const options = enumValues(config.field_type).map((opt) => `<option value="${escapeHtml(opt)}" ${opt === value ? 'selected' : ''}>${escapeHtml(opt)}</option>`).join('');
        return `<label class="form-label">${formatLabel(config.field_name)}</label><select class="form-select" name="${config.field_name}"><option value="">Select</option>${options}</select>`;
    }
    if (String(config.field_type).toLowerCase().includes('date time')) {
        return `<label class="form-label">${formatLabel(config.field_name)}</label><input class="form-control" type="datetime-local" name="${config.field_name}" value="${toInputDatetime(value)}">`;
    }
    return `<label class="form-label">${formatLabel(config.field_name)}</label><input class="form-control" type="text" name="${config.field_name}" value="${escapeHtml(value || '')}">`;
}

function renderCallHistoryRows(rows) {
    const body = document.getElementById('callHistoryBody');
    if (!body) return;
    if (!rows.length) {
        body.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No call history found.</td></tr>';
        return;
    }
    body.innerHTML = rows.map((r, i) => `<tr><td>${i+1}</td><td>${escapeHtml(r.stage || 'N/A')}</td><td>${escapeHtml(r.purpose_name || 'N/A')}</td><td>${escapeHtml(r.call_datetime || 'N/A')}</td><td>${escapeHtml(r.call_status || 'N/A')}</td><td>${escapeHtml(r.call_remarks || 'N/A')}</td></tr>`).join('');
}

function renderActivityRows(rows) {
    const body = document.getElementById('activityLogBody');
    if (!body) return;
    if (!rows.length) {
        body.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No activity log found.</td></tr>';
        return;
    }
    body.innerHTML = rows.map((r, i) => `<tr><td>${i+1}</td><td>${escapeHtml(r.activity_section || 'N/A')}</td><td>${escapeHtml(r.activity_type || 'N/A')}</td><td>${escapeHtml(String(r.activity_details || 'N/A')).replaceAll('\n','<br>')}</td><td>${escapeHtml(r.created_by_name || 'N/A')}</td><td>${escapeHtml(r.created_at || 'N/A')}</td></tr>`).join('');
}

function loadHistory() {
    fetch(`/job_fair_results.php?candidate_call_history=${candidateId}`).then(r => r.json()).then((rows) => renderCallHistoryRows(Array.isArray(rows) ? rows : [])).catch(() => renderCallHistoryRows([]));
    fetch(`/job_fair_results.php?candidate_manage_activity_log=${candidateId}`).then(r => r.json()).then((rows) => renderActivityRows(Array.isArray(rows) ? rows : [])).catch(() => renderActivityRows([]));
}

function renderPanels(row) {
    const details = ['Job_Fair_No','Selection_Status','DWMS_ID','Candidate_Name','Employer_ID','Employer_Name','Job_Id','Job_Title_Name','Aggregator','CRM_Member','DSM_Member_1','DSM_Member_2','Job_Fair_Date'];
    detailPanel.innerHTML = details.map((name) => `<div class="col-12 col-md-4"><label class="form-label text-muted small">${formatLabel(name)}</label><div class="form-control bg-light">${escapeHtml(row[name] || 'N/A')}</div></div>`).join('');

    const panelNames = row.Selection_Status === 'Selected' ? ['Selected', 'Call History'] : ['Shortlist/Onhold', 'Selected', 'Call History'];
    const tabs = panelNames.map((p, i) => `<li class="nav-item"><button class="nav-link ${i===0?'active':''}" data-bs-toggle="tab" data-bs-target="#panel-${p.replace(/[^a-zA-Z0-9]/g,'')}" type="button">${p}</button></li>`).join('');

    const tabBodies = panelNames.map((panel, i) => {
        const panelKey = panel.replace(/[^a-zA-Z0-9]/g,'');
        if (panel === 'Call History') {
            return `<div class="tab-pane fade ${i===0?'show active':''}" id="panel-${panelKey}">
                <div class="card mb-3"><div class="card-header">Add Call Detail</div><div class="card-body"><div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Stage</label><select class="form-select" name="call_history_stage"><option value="">Select</option><option>Employer Connect</option><option>Candidate Connect</option><option>Aggregator Contact</option></select></div>
                    <div class="col-md-4"><label class="form-label">Purpose</label><select class="form-select" name="call_history_purpose_id"><option value="">Select</option>${callHistoryPurposeOptions.map((o) => `<option value="${o.id}">${escapeHtml(o.purpose_name)}</option>`).join('')}</select></div>
                    <div class="col-md-4"><label class="form-label">Call Date time</label><input type="datetime-local" class="form-control" name="call_history_call_datetime" value="${toInputDatetime(new Date().toISOString())}" readonly></div>
                    <div class="col-md-4"><label class="form-label">Call Status</label><select class="form-select" name="call_history_call_status"><option value="">Select</option><option>Attended</option><option>Not attended</option><option>Invalid number</option></select></div>
                    <div class="col-12"><label class="form-label">Call Remarks</label><textarea class="form-control" name="call_history_call_remarks" rows="2"></textarea></div>
                </div></div></div>
                <div class="d-flex justify-content-end mb-3"><button type="submit" class="btn btn-primary btn-sm" name="update_section" value="call_history">Add Call History</button></div>
                <div class="card mb-3"><div class="card-header">Call History</div><div class="card-body"><div class="table-responsive"><table class="table table-bordered table-striped mb-0"><thead><tr><th>Sl no</th><th>Stage</th><th>Purpose</th><th>Date time</th><th>Status</th><th>Remarks</th></tr></thead><tbody id="callHistoryBody"></tbody></table></div></div></div>
                <div class="card"><div class="card-header">Activity Log</div><div class="card-body"><div class="table-responsive"><table class="table table-bordered table-striped mb-0"><thead><tr><th>Sl no</th><th>Section</th><th>Type</th><th>Details</th><th>Updated By</th><th>Updated At</th></tr></thead><tbody id="activityLogBody"></tbody></table></div></div></div>
            </div>`;
        }

        const panelFields = fieldConfig.filter((f) => f.panel_label === panel);
        const groups = [...new Set(panelFields.map((f) => f.group_label))];
        const groupHtml = groups.map((g) => {
            const fields = panelFields.filter((f) => f.group_label === g).sort((a,b) => (a.row_position-b.row_position) || (a.column_position-b.column_position));
            return `<div class="card mb-3"><div class="card-header">${escapeHtml(g)}</div><div class="card-body"><div class="row g-3">${fields.map((f) => `<div class="col-12 col-md-4">${renderFieldControl(f,row)}</div>`).join('')}</div></div></div>`;
        }).join('');
        const updateSection = panel === 'Shortlist/Onhold' ? 'shortlist_onhold' : 'selected';
        return `<div class="tab-pane fade ${i===0?'show active':''}" id="panel-${panelKey}"><div class="d-flex justify-content-end mb-3"><button type="submit" class="btn btn-primary btn-sm" name="update_section" value="${updateSection}">Update ${panel} Details</button></div>${groupHtml}</div>`;
    }).join('');

    dynamicPanels.innerHTML = `<ul class="nav nav-tabs mb-3">${tabs}</ul><div class="tab-content">${tabBodies}</div>`;
    loadHistory();

    dynamicPanels.querySelectorAll('.nav-link').forEach((btn) => {
        btn.addEventListener('shown.bs.tab', () => {
            const target = String(btn.dataset.bsTarget || '').replace('#panel-', '');
            activeTabInput.value = target;
        });
    });

    const firstTab = dynamicPanels.querySelector('.nav-link.active');
    if (firstTab) {
        activeTabInput.value = String(firstTab.dataset.bsTarget || '').replace('#panel-', '');
    }
}

Promise.all([
    fetch(`/job_fair_results.php?manage_candidate_meta=1`).then((r) => r.json()),
    fetch(`/job_fair_results.php?candidate_row=${candidateId}`).then((r) => r.json())
]).then(([meta, row]) => {
    fieldConfig = Array.isArray(meta?.field_config) ? meta.field_config : [];
    callHistoryPurposeOptions = Array.isArray(meta?.call_purpose_options) ? meta.call_purpose_options : [];
    currentRow = row || null;
    if (!currentRow) {
        window.location.href = <?= json_encode($returnUrl) ?>;
        return;
    }
    renderPanels(currentRow);
}).catch(() => {
    window.location.href = <?= json_encode($returnUrl) ?>;
});

document.getElementById('manageCandidateForm').addEventListener('submit', (event) => {
    const submitter = event.submitter;
    if (submitter?.value === 'call_history') {
        activeTabInput.value = 'CallHistory';
    }
});
</script>

<?php render_footer(); ?>
