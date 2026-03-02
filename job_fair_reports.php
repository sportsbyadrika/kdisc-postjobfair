<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

function fetch_grouped_status_counts(string $selectColumns, string $groupByColumns): array
{
    $sql = "SELECT $selectColumns,
        SUM(CASE WHEN LOWER(TRIM(Selection_Status)) = 'selected' THEN 1 ELSE 0 END) AS selected_count,
        SUM(CASE WHEN LOWER(TRIM(Selection_Status)) = 'shortlisted' THEN 1 ELSE 0 END) AS shortlisted_count,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'onhold' THEN 1 ELSE 0 END) AS on_hold_count,
        COUNT(*) AS total_count
    FROM job_fair_result
    GROUP BY $groupByColumns
    ORDER BY selected_count DESC, shortlisted_count DESC, on_hold_count DESC, total_count DESC, $groupByColumns";

    return db()->query($sql)->fetchAll();
}

function fetch_aggregator_consolidated_rows(): array
{
    $sql = "SELECT
        COALESCE(NULLIF(TRIM(Aggregator), ''), 'Unknown') AS aggregator_name,
        Job_Fair_Date,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'selected' THEN 1 ELSE 0 END) AS selected_total,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'selected' AND LOWER(TRIM(Offer_Letter_Generated)) = 'yes' THEN 1 ELSE 0 END) AS selected_offer_generated,

        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted' THEN 1 ELSE 0 END) AS shortlisted_total,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted' AND LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'selected' THEN 1 ELSE 0 END) AS shortlisted_selected,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted' AND LOWER(TRIM(Offer_Letter_Generated)) = 'yes' THEN 1 ELSE 0 END) AS shortlisted_offer_generated,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted' AND LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'shortlisted' THEN 1 ELSE 0 END) AS shortlisted_in_progress,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted' AND LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'rejected' THEN 1 ELSE 0 END) AS shortlisted_rejected,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted' AND LOWER(TRIM(Shortlist_Preparatory_Call_Status)) = 'pending' THEN 1 ELSE 0 END) AS shortlisted_not_connected,

        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'onhold' THEN 1 ELSE 0 END) AS on_hold_total,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'onhold' AND LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'selected' THEN 1 ELSE 0 END) AS on_hold_selected,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'onhold' AND LOWER(TRIM(Offer_Letter_Generated)) = 'yes' THEN 1 ELSE 0 END) AS on_hold_offer_generated,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'onhold' AND LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'rejected' THEN 1 ELSE 0 END) AS on_hold_rejected
    FROM job_fair_result
    GROUP BY aggregator_name, Job_Fair_Date
    ORDER BY aggregator_name ASC, Job_Fair_Date DESC";

    $rows = db()->query($sql)->fetchAll();

    foreach ($rows as &$row) {
        $row['total_selected'] = (int) $row['selected_total'] + (int) $row['shortlisted_selected'] + (int) $row['on_hold_selected'];
        $row['total_offer_generated'] = (int) $row['selected_offer_generated'] + (int) $row['shortlisted_offer_generated'] + (int) $row['on_hold_offer_generated'];
    }
    unset($row);

    return $rows;
}

function calculate_totals(array $rows): array
{
    return array_reduce(
        $rows,
        static function (array $carry, array $row): array {
            $carry['selected_count'] += (int) ($row['selected_count'] ?? 0);
            $carry['shortlisted_count'] += (int) ($row['shortlisted_count'] ?? 0);
            $carry['on_hold_count'] += (int) ($row['on_hold_count'] ?? 0);
            $carry['total_count'] += (int) ($row['total_count'] ?? 0);

            return $carry;
        },
        [
            'selected_count' => 0,
            'shortlisted_count' => 0,
            'on_hold_count' => 0,
            'total_count' => 0,
        ]
    );
}

function calculate_consolidated_totals(array $rows): array
{
    $keys = [
        'selected_total',
        'selected_offer_generated',
        'shortlisted_total',
        'shortlisted_selected',
        'shortlisted_offer_generated',
        'shortlisted_in_progress',
        'shortlisted_rejected',
        'shortlisted_not_connected',
        'on_hold_total',
        'on_hold_selected',
        'on_hold_offer_generated',
        'on_hold_rejected',
        'total_selected',
        'total_offer_generated',
    ];

    $totals = array_fill_keys($keys, 0);

    foreach ($rows as $row) {
        foreach ($keys as $key) {
            $totals[$key] += (int) ($row[$key] ?? 0);
        }
    }

    return $totals;
}

function fetch_stage_report_rows(string $baseCondition): array
{
    $sql = "SELECT
        COALESCE(NULLIF(TRIM(Aggregator), ''), 'Unknown') AS aggregator_name,
        COUNT(CASE WHEN $baseCondition THEN 1 END) AS total_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'selected' THEN 1 END) AS selected_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(First_Call_Done)) = 'yes' THEN 1 END) AS first_call_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Offer_Letter_Generated)) = 'yes' THEN 1 END) AS offer_letter_issued_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Link_to_Offer_letter_verified)) = 'yes' THEN 1 END) AS offer_letter_verified_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Confirm_Offer_Letter_Receipt_by_Candidate)) = 'yes' THEN 1 END) AS candidate_confirmed_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Candidate_Joined_Status)) = 'yes' THEN 1 END) AS joining_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'rejected' THEN 1 END) AS rejected_count,
        COUNT(CASE WHEN $baseCondition AND COALESCE(NULLIF(TRIM(Shortlist_Candidate_Status), ''), 'No-Status') = 'No-Status' THEN 1 END) AS no_status_count
    FROM job_fair_result
    GROUP BY aggregator_name
    ORDER BY aggregator_name ASC";

    return db()->query($sql)->fetchAll();
}

function fetch_escalation_report_rows(string $baseCondition): array
{
    $sql = "SELECT
        COALESCE(NULLIF(TRIM(Aggregator), ''), 'Unknown') AS aggregator_name,
        COUNT(CASE WHEN $baseCondition THEN 1 END) AS total_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Shortlist_Current_Process_Status)) = 'pending' THEN 1 END) AS shortlist_process_pending_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'selected' THEN 1 END) AS selected_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(First_Call_Done)) = 'pending' THEN 1 END) AS first_call_pending_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Offer_Letter_Generated)) = 'pending' THEN 1 END) AS offer_letter_pending_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Link_to_Offer_letter_verified)) = 'no' THEN 1 END) AS offer_letter_verification_pending_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Confirm_Offer_Letter_Receipt_by_Candidate)) = 'pending' THEN 1 END) AS candidate_confirmation_pending_count,
        COUNT(CASE WHEN $baseCondition AND LOWER(TRIM(Candidate_Joined_Status)) = 'pending' THEN 1 END) AS joining_status_pending_count
    FROM job_fair_result
    GROUP BY aggregator_name
    ORDER BY aggregator_name ASC";

    return db()->query($sql)->fetchAll();
}

function calculate_stage_report_totals(array $rows): array
{
    $keys = [
        'total_count',
        'selected_count',
        'first_call_count',
        'offer_letter_issued_count',
        'offer_letter_verified_count',
        'candidate_confirmed_count',
        'joining_count',
        'rejected_count',
        'no_status_count',
    ];

    $totals = array_fill_keys($keys, 0);

    foreach ($rows as $row) {
        foreach ($keys as $key) {
            $totals[$key] += (int) ($row[$key] ?? 0);
        }
    }

    return $totals;
}

function calculate_escalation_totals(array $rows): array
{
    $keys = [
        'total_count',
        'shortlist_process_pending_count',
        'selected_count',
        'first_call_pending_count',
        'offer_letter_pending_count',
        'offer_letter_verification_pending_count',
        'candidate_confirmation_pending_count',
        'joining_status_pending_count',
    ];

    $totals = array_fill_keys($keys, 0);

    foreach ($rows as $row) {
        foreach ($keys as $key) {
            $totals[$key] += (int) ($row[$key] ?? 0);
        }
    }

    return $totals;
}

$employerAggregatorRows = fetch_grouped_status_counts(
    "COALESCE(NULLIF(TRIM(Employer_Name), ''), 'Unknown') AS employer_name,
     COALESCE(NULLIF(TRIM(Aggregator), ''), 'Unknown') AS aggregator_name",
    'employer_name, aggregator_name'
);

$aggregatorRows = fetch_grouped_status_counts(
    "COALESCE(NULLIF(TRIM(Aggregator), ''), 'Unknown') AS aggregator_name",
    'aggregator_name'
);

$districtRows = fetch_grouped_status_counts(
    "COALESCE(NULLIF(TRIM(Candidate_District), ''), 'Unknown') AS district_name",
    'district_name'
);

$aggregatorConsolidatedRows = fetch_aggregator_consolidated_rows();
$selectedReportRows = fetch_stage_report_rows("LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'selected'");
$shortlistedToSelectedRows = fetch_stage_report_rows("LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted'");
$onHoldReportRows = fetch_stage_report_rows("LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'onhold'");

$selectedEscalationRows = fetch_escalation_report_rows("LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'selected'");
$shortlistedEscalationRows = fetch_escalation_report_rows("LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'shortlisted'");
$onHoldEscalationRows = fetch_escalation_report_rows("LOWER(REPLACE(TRIM(Selection_Status), ' ', '')) = 'onhold'");

$employerAggregatorTotals = calculate_totals($employerAggregatorRows);
$aggregatorTotals = calculate_totals($aggregatorRows);
$districtTotals = calculate_totals($districtRows);
$aggregatorConsolidatedTotals = calculate_consolidated_totals($aggregatorConsolidatedRows);
$selectedReportTotals = calculate_stage_report_totals($selectedReportRows);
$shortlistedToSelectedTotals = calculate_stage_report_totals($shortlistedToSelectedRows);
$onHoldReportTotals = calculate_stage_report_totals($onHoldReportRows);

$selectedEscalationTotals = calculate_escalation_totals($selectedEscalationRows);
$shortlistedEscalationTotals = calculate_escalation_totals($shortlistedEscalationRows);
$onHoldEscalationTotals = calculate_escalation_totals($onHoldEscalationRows);

render_header('Job fair reports');
?>
<h1 class="h3 mb-4">Job fair reports</h1>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Aggregator consolidated report</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Aggregator</th>
                    <th>Job fair date</th>
                    <th>Selected (Total)</th>
                    <th>Selected - Offer letter generated</th>
                    <th>Shortlisted (Total)</th>
                    <th>Shortlisted - Selected</th>
                    <th>Shortlisted - Offer letter generated</th>
                    <th>Shortlisted - In progress</th>
                    <th>Shortlisted - Rejected</th>
                    <th>Shortlisted - Not connected (Pending)</th>
                    <th>On hold (Total)</th>
                    <th>On hold - Selected</th>
                    <th>On hold - Offer letter generated</th>
                    <th>On hold - Rejected</th>
                    <th>Total selected</th>
                    <th>Total offer letter generated</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($aggregatorConsolidatedRows === []): ?>
                    <tr><td colspan="16" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($aggregatorConsolidatedRows as $row): ?>
                    <tr>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= esc($row['Job_Fair_Date'] ?: '-') ?></td>
                        <td><?= (int) $row['selected_total'] ?></td>
                        <td><?= (int) $row['selected_offer_generated'] ?></td>
                        <td><?= (int) $row['shortlisted_total'] ?></td>
                        <td><?= (int) $row['shortlisted_selected'] ?></td>
                        <td><?= (int) $row['shortlisted_offer_generated'] ?></td>
                        <td><?= (int) $row['shortlisted_in_progress'] ?></td>
                        <td><?= (int) $row['shortlisted_rejected'] ?></td>
                        <td><?= (int) $row['shortlisted_not_connected'] ?></td>
                        <td><?= (int) $row['on_hold_total'] ?></td>
                        <td><?= (int) $row['on_hold_selected'] ?></td>
                        <td><?= (int) $row['on_hold_offer_generated'] ?></td>
                        <td><?= (int) $row['on_hold_rejected'] ?></td>
                        <td><strong><?= (int) $row['total_selected'] ?></strong></td>
                        <td><strong><?= (int) $row['total_offer_generated'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($aggregatorConsolidatedRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td colspan="2">Total</td>
                        <td><?= $aggregatorConsolidatedTotals['selected_total'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['selected_offer_generated'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['shortlisted_total'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['shortlisted_selected'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['shortlisted_offer_generated'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['shortlisted_in_progress'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['shortlisted_rejected'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['shortlisted_not_connected'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['on_hold_total'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['on_hold_selected'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['on_hold_offer_generated'] ?></td>
                        <td><?= $aggregatorConsolidatedTotals['on_hold_rejected'] ?></td>
                        <td><strong><?= $aggregatorConsolidatedTotals['total_selected'] ?></strong></td>
                        <td><strong><?= $aggregatorConsolidatedTotals['total_offer_generated'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Report 2: Selected report</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Aggregator</th>
                    <th>Total</th>
                    <th>Selected</th>
                    <th>First call</th>
                    <th>Offer letter issued</th>
                    <th>Offer letter verified</th>
                    <th>Candidate confirmed</th>
                    <th>Joining</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($selectedReportRows === []): ?>
                    <tr><td colspan="8" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($selectedReportRows as $row): ?>
                    <tr>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= (int) $row['total_count'] ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['first_call_count'] ?></td>
                        <td><?= (int) $row['offer_letter_issued_count'] ?></td>
                        <td><?= (int) $row['offer_letter_verified_count'] ?></td>
                        <td><?= (int) $row['candidate_confirmed_count'] ?></td>
                        <td><strong><?= (int) $row['joining_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($selectedReportRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $selectedReportTotals['total_count'] ?></td>
                        <td><?= $selectedReportTotals['selected_count'] ?></td>
                        <td><?= $selectedReportTotals['first_call_count'] ?></td>
                        <td><?= $selectedReportTotals['offer_letter_issued_count'] ?></td>
                        <td><?= $selectedReportTotals['offer_letter_verified_count'] ?></td>
                        <td><?= $selectedReportTotals['candidate_confirmed_count'] ?></td>
                        <td><strong><?= $selectedReportTotals['joining_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Report 2: Shortlisted to selected</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Aggregator</th>
                    <th>Total shortlisted</th>
                    <th>Selected</th>
                    <th>First call</th>
                    <th>Offer letter issued</th>
                    <th>Offer letter verified</th>
                    <th>Candidate confirmed</th>
                    <th>Joining</th>
                    <th>Rejected</th>
                    <th>No-status count</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($shortlistedToSelectedRows === []): ?>
                    <tr><td colspan="10" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($shortlistedToSelectedRows as $row): ?>
                    <tr>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= (int) $row['total_count'] ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['first_call_count'] ?></td>
                        <td><?= (int) $row['offer_letter_issued_count'] ?></td>
                        <td><?= (int) $row['offer_letter_verified_count'] ?></td>
                        <td><?= (int) $row['candidate_confirmed_count'] ?></td>
                        <td><?= (int) $row['joining_count'] ?></td>
                        <td><?= (int) $row['rejected_count'] ?></td>
                        <td><strong><?= (int) $row['no_status_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($shortlistedToSelectedRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $shortlistedToSelectedTotals['total_count'] ?></td>
                        <td><?= $shortlistedToSelectedTotals['selected_count'] ?></td>
                        <td><?= $shortlistedToSelectedTotals['first_call_count'] ?></td>
                        <td><?= $shortlistedToSelectedTotals['offer_letter_issued_count'] ?></td>
                        <td><?= $shortlistedToSelectedTotals['offer_letter_verified_count'] ?></td>
                        <td><?= $shortlistedToSelectedTotals['candidate_confirmed_count'] ?></td>
                        <td><?= $shortlistedToSelectedTotals['joining_count'] ?></td>
                        <td><?= $shortlistedToSelectedTotals['rejected_count'] ?></td>
                        <td><strong><?= $shortlistedToSelectedTotals['no_status_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Report 2: On hold</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Aggregator</th>
                    <th>Total on hold</th>
                    <th>Selected</th>
                    <th>First call</th>
                    <th>Offer letter issued</th>
                    <th>Offer letter verified</th>
                    <th>Candidate confirmed</th>
                    <th>Joining</th>
                    <th>Rejected</th>
                    <th>No-status count</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($onHoldReportRows === []): ?>
                    <tr><td colspan="10" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($onHoldReportRows as $row): ?>
                    <tr>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= (int) $row['total_count'] ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['first_call_count'] ?></td>
                        <td><?= (int) $row['offer_letter_issued_count'] ?></td>
                        <td><?= (int) $row['offer_letter_verified_count'] ?></td>
                        <td><?= (int) $row['candidate_confirmed_count'] ?></td>
                        <td><?= (int) $row['joining_count'] ?></td>
                        <td><?= (int) $row['rejected_count'] ?></td>
                        <td><strong><?= (int) $row['no_status_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($onHoldReportRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $onHoldReportTotals['total_count'] ?></td>
                        <td><?= $onHoldReportTotals['selected_count'] ?></td>
                        <td><?= $onHoldReportTotals['first_call_count'] ?></td>
                        <td><?= $onHoldReportTotals['offer_letter_issued_count'] ?></td>
                        <td><?= $onHoldReportTotals['offer_letter_verified_count'] ?></td>
                        <td><?= $onHoldReportTotals['candidate_confirmed_count'] ?></td>
                        <td><?= $onHoldReportTotals['joining_count'] ?></td>
                        <td><?= $onHoldReportTotals['rejected_count'] ?></td>
                        <td><strong><?= $onHoldReportTotals['no_status_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Escalation reports: Selected escalation</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Aggregator</th>
                    <th>Total</th>
                    <th>Selected</th>
                    <th>First call pending</th>
                    <th>Offer letter pending</th>
                    <th>Offer letter verification pending</th>
                    <th>Candidate confirmation pending</th>
                    <th>Joining status pending</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($selectedEscalationRows === []): ?>
                    <tr><td colspan="8" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($selectedEscalationRows as $row): ?>
                    <tr>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= (int) $row['total_count'] ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['first_call_pending_count'] ?></td>
                        <td><?= (int) $row['offer_letter_pending_count'] ?></td>
                        <td><?= (int) $row['offer_letter_verification_pending_count'] ?></td>
                        <td><?= (int) $row['candidate_confirmation_pending_count'] ?></td>
                        <td><strong><?= (int) $row['joining_status_pending_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($selectedEscalationRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $selectedEscalationTotals['total_count'] ?></td>
                        <td><?= $selectedEscalationTotals['selected_count'] ?></td>
                        <td><?= $selectedEscalationTotals['first_call_pending_count'] ?></td>
                        <td><?= $selectedEscalationTotals['offer_letter_pending_count'] ?></td>
                        <td><?= $selectedEscalationTotals['offer_letter_verification_pending_count'] ?></td>
                        <td><?= $selectedEscalationTotals['candidate_confirmation_pending_count'] ?></td>
                        <td><strong><?= $selectedEscalationTotals['joining_status_pending_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Escalation reports: Shortlisted escalation</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Aggregator</th>
                    <th>Total shortlisted</th>
                    <th>Shortlist process pending</th>
                    <th>Selected count</th>
                    <th>First call pending</th>
                    <th>Offer letter pending</th>
                    <th>Offer letter verification pending</th>
                    <th>Candidate confirmation pending</th>
                    <th>Joining status pending</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($shortlistedEscalationRows === []): ?>
                    <tr><td colspan="9" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($shortlistedEscalationRows as $row): ?>
                    <tr>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= (int) $row['total_count'] ?></td>
                        <td><?= (int) $row['shortlist_process_pending_count'] ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['first_call_pending_count'] ?></td>
                        <td><?= (int) $row['offer_letter_pending_count'] ?></td>
                        <td><?= (int) $row['offer_letter_verification_pending_count'] ?></td>
                        <td><?= (int) $row['candidate_confirmation_pending_count'] ?></td>
                        <td><strong><?= (int) $row['joining_status_pending_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($shortlistedEscalationRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $shortlistedEscalationTotals['total_count'] ?></td>
                        <td><?= $shortlistedEscalationTotals['shortlist_process_pending_count'] ?></td>
                        <td><?= $shortlistedEscalationTotals['selected_count'] ?></td>
                        <td><?= $shortlistedEscalationTotals['first_call_pending_count'] ?></td>
                        <td><?= $shortlistedEscalationTotals['offer_letter_pending_count'] ?></td>
                        <td><?= $shortlistedEscalationTotals['offer_letter_verification_pending_count'] ?></td>
                        <td><?= $shortlistedEscalationTotals['candidate_confirmation_pending_count'] ?></td>
                        <td><strong><?= $shortlistedEscalationTotals['joining_status_pending_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Escalation reports: On hold escalation</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Aggregator</th>
                    <th>Total on hold</th>
                    <th>Shortlist process pending</th>
                    <th>Selected count</th>
                    <th>First call pending</th>
                    <th>Offer letter pending</th>
                    <th>Offer letter verification pending</th>
                    <th>Candidate confirmation pending</th>
                    <th>Joining status pending</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($onHoldEscalationRows === []): ?>
                    <tr><td colspan="9" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($onHoldEscalationRows as $row): ?>
                    <tr>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= (int) $row['total_count'] ?></td>
                        <td><?= (int) $row['shortlist_process_pending_count'] ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['first_call_pending_count'] ?></td>
                        <td><?= (int) $row['offer_letter_pending_count'] ?></td>
                        <td><?= (int) $row['offer_letter_verification_pending_count'] ?></td>
                        <td><?= (int) $row['candidate_confirmation_pending_count'] ?></td>
                        <td><strong><?= (int) $row['joining_status_pending_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($onHoldEscalationRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $onHoldEscalationTotals['total_count'] ?></td>
                        <td><?= $onHoldEscalationTotals['shortlist_process_pending_count'] ?></td>
                        <td><?= $onHoldEscalationTotals['selected_count'] ?></td>
                        <td><?= $onHoldEscalationTotals['first_call_pending_count'] ?></td>
                        <td><?= $onHoldEscalationTotals['offer_letter_pending_count'] ?></td>
                        <td><?= $onHoldEscalationTotals['offer_letter_verification_pending_count'] ?></td>
                        <td><?= $onHoldEscalationTotals['candidate_confirmation_pending_count'] ?></td>
                        <td><strong><?= $onHoldEscalationTotals['joining_status_pending_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Employer and aggregator status</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Name of Employer</th>
                    <th>Aggregator</th>
                    <th>Selected</th>
                    <th>Shortlisted</th>
                    <th>On hold</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($employerAggregatorRows === []): ?>
                    <tr><td colspan="6" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($employerAggregatorRows as $row): ?>
                    <tr>
                        <td><?= esc($row['employer_name']) ?></td>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['shortlisted_count'] ?></td>
                        <td><?= (int) $row['on_hold_count'] ?></td>
                        <td><strong><?= (int) $row['total_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($employerAggregatorRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td colspan="2">Total</td>
                        <td><?= $employerAggregatorTotals['selected_count'] ?></td>
                        <td><?= $employerAggregatorTotals['shortlisted_count'] ?></td>
                        <td><?= $employerAggregatorTotals['on_hold_count'] ?></td>
                        <td><strong><?= $employerAggregatorTotals['total_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h5">Aggregator status</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Aggregator</th>
                    <th>Selected</th>
                    <th>Shortlisted</th>
                    <th>On hold</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($aggregatorRows === []): ?>
                    <tr><td colspan="5" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($aggregatorRows as $row): ?>
                    <tr>
                        <td><?= esc($row['aggregator_name']) ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['shortlisted_count'] ?></td>
                        <td><?= (int) $row['on_hold_count'] ?></td>
                        <td><strong><?= (int) $row['total_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($aggregatorRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $aggregatorTotals['selected_count'] ?></td>
                        <td><?= $aggregatorTotals['shortlisted_count'] ?></td>
                        <td><?= $aggregatorTotals['on_hold_count'] ?></td>
                        <td><strong><?= $aggregatorTotals['total_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h5">District status</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>District (candidate_District)</th>
                    <th>Selected</th>
                    <th>Shortlisted</th>
                    <th>On hold</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($districtRows === []): ?>
                    <tr><td colspan="5" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($districtRows as $row): ?>
                    <tr>
                        <td><?= esc($row['district_name']) ?></td>
                        <td><?= (int) $row['selected_count'] ?></td>
                        <td><?= (int) $row['shortlisted_count'] ?></td>
                        <td><?= (int) $row['on_hold_count'] ?></td>
                        <td><strong><?= (int) $row['total_count'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($districtRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $districtTotals['selected_count'] ?></td>
                        <td><?= $districtTotals['shortlisted_count'] ?></td>
                        <td><?= $districtTotals['on_hold_count'] ?></td>
                        <td><strong><?= $districtTotals['total_count'] ?></strong></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer(); ?>
