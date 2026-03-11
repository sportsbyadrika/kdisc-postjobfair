<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

function fetch_consolidated_distinct_values(string $column): array
{
    $sql = "SELECT DISTINCT COALESCE(NULLIF(TRIM($column), ''), 'Unknown') AS value
        FROM job_fair_result
        ORDER BY value ASC";

    return array_map(static fn(array $row): string => (string) $row['value'], db()->query($sql)->fetchAll());
}

function build_consolidated_filters(): array
{
    return [
        'aggregator' => trim((string) ($_GET['aggregator'] ?? '')),
        'job_fair' => trim((string) ($_GET['job_fair'] ?? '')),
        'category' => trim((string) ($_GET['category'] ?? '')),
        'selection_status' => trim((string) ($_GET['selection_status'] ?? '')),
    ];
}

function normalized_column(string $column): string
{
    return "LOWER(REPLACE(TRIM(COALESCE($column, '')), ' ', ''))";
}

function build_common_conditions(array $filters, array &$params): array
{
    $conditions = [];

    if ($filters['aggregator'] !== '') {
        $conditions[] = "COALESCE(NULLIF(TRIM(Aggregator), ''), 'Unknown') = ?";
        $params[] = $filters['aggregator'];
    }

    if ($filters['job_fair'] !== '') {
        $conditions[] = "COALESCE(NULLIF(TRIM(Job_Fair_No), ''), 'Unknown') = ?";
        $params[] = $filters['job_fair'];
    }

    if ($filters['category'] !== '') {
        $conditions[] = "COALESCE(NULLIF(TRIM(Category), ''), 'Unknown') = ?";
        $params[] = $filters['category'];
    }

    return $conditions;
}

function fetch_selected_candidates_report(array $filters): array
{
    $params = [];
    $conditions = build_common_conditions($filters, $params);
    $conditions[] = normalized_column('Selection_Status') . " = 'selected'";

    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "SELECT
            COALESCE(NULLIF(TRIM(Job_Fair_No), ''), 'Unknown') AS job_fair_no,
            COUNT(*) AS total_selected_candidate,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'yes' THEN 1 ELSE 0 END) AS offer_generated_yes,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'no' THEN 1 ELSE 0 END) AS offer_generated_no,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'pending' OR TRIM(COALESCE(Offer_Letter_Generated, '')) = '' THEN 1 ELSE 0 END) AS offer_generated_pending,
            SUM(CASE WHEN TRIM(COALESCE(Link_to_Offer_letter, '')) <> '' THEN 1 ELSE 0 END) AS offer_link_with_link,
            SUM(CASE WHEN TRIM(COALESCE(Link_to_Offer_letter, '')) = '' THEN 1 ELSE 0 END) AS offer_link_blank,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'yes' THEN 1 ELSE 0 END) AS link_verified_yes,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'no' THEN 1 ELSE 0 END) AS link_verified_no,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'pending' OR TRIM(COALESCE(Link_to_Offer_letter_verified, '')) = '' THEN 1 ELSE 0 END) AS link_verified_pending,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'yes' THEN 1 ELSE 0 END) AS receipt_confirmed_yes,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'no' THEN 1 ELSE 0 END) AS receipt_confirmed_no,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'pending' OR TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, '')) = '' THEN 1 ELSE 0 END) AS receipt_confirmed_pending,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'yes' THEN 1 ELSE 0 END) AS joined_yes,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'no' THEN 1 ELSE 0 END) AS joined_no,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'pending' OR TRIM(COALESCE(Candidate_Joined_Status, '')) = '' THEN 1 ELSE 0 END) AS joined_pending
        FROM job_fair_result
        $whereClause
        GROUP BY job_fair_no
        ORDER BY job_fair_no ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function fetch_shortlisted_onhold_report(array $filters): array
{
    $params = [];
    $conditions = build_common_conditions($filters, $params);

    $selectionStatusExpression = normalized_column('Selection_Status');
    $conditions[] = "$selectionStatusExpression IN ('shortlisted', 'onhold')";

    if ($filters['selection_status'] !== '') {
        $conditions[] = "$selectionStatusExpression = ?";
        $params[] = strtolower(str_replace(' ', '', $filters['selection_status']));
    }

    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    $shortlistStatusExpression = normalized_column('Shortlist_Candidate_Status');

    $sql = "SELECT
            COALESCE(NULLIF(TRIM(Job_Fair_No), ''), 'Unknown') AS job_fair_no,
            COUNT(*) AS total_shortlisted_onhold_candidate,
            SUM(CASE WHEN $shortlistStatusExpression = 'shortlisted' THEN 1 ELSE 0 END) AS shortlist_status_shortlisted,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' THEN 1 ELSE 0 END) AS shortlist_status_selected,
            SUM(CASE WHEN $shortlistStatusExpression = 'rejected' THEN 1 ELSE 0 END) AS shortlist_status_rejected,
            SUM(CASE WHEN $shortlistStatusExpression = 'onhold' THEN 1 ELSE 0 END) AS shortlist_status_onhold,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'yes' THEN 1 ELSE 0 END) AS offer_generated_yes,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'no' THEN 1 ELSE 0 END) AS offer_generated_no,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND (LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'pending' OR TRIM(COALESCE(Offer_Letter_Generated, '')) = '') THEN 1 ELSE 0 END) AS offer_generated_pending,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND TRIM(COALESCE(Link_to_Offer_letter, '')) <> '' THEN 1 ELSE 0 END) AS offer_link_with_link,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND TRIM(COALESCE(Link_to_Offer_letter, '')) = '' THEN 1 ELSE 0 END) AS offer_link_blank,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'yes' THEN 1 ELSE 0 END) AS link_verified_yes,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'no' THEN 1 ELSE 0 END) AS link_verified_no,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND (LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'pending' OR TRIM(COALESCE(Link_to_Offer_letter_verified, '')) = '') THEN 1 ELSE 0 END) AS link_verified_pending,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'yes' THEN 1 ELSE 0 END) AS receipt_confirmed_yes,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'no' THEN 1 ELSE 0 END) AS receipt_confirmed_no,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND (LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'pending' OR TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, '')) = '') THEN 1 ELSE 0 END) AS receipt_confirmed_pending,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'yes' THEN 1 ELSE 0 END) AS joined_yes,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'no' THEN 1 ELSE 0 END) AS joined_no,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND (LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'pending' OR TRIM(COALESCE(Candidate_Joined_Status, '')) = '') THEN 1 ELSE 0 END) AS joined_pending
        FROM job_fair_result
        $whereClause
        GROUP BY job_fair_no
        ORDER BY job_fair_no ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function calculate_consolidated_totals(array $rows, array $keys): array
{
    $totals = array_fill_keys($keys, 0);

    foreach ($rows as $row) {
        foreach ($keys as $key) {
            $totals[$key] += (int) ($row[$key] ?? 0);
        }
    }

    return $totals;
}

$filters = build_consolidated_filters();
$aggregatorOptions = fetch_consolidated_distinct_values('Aggregator');
$jobFairOptions = fetch_consolidated_distinct_values('Job_Fair_No');
$categoryOptions = fetch_consolidated_distinct_values('Category');
$selectionStatusOptions = fetch_consolidated_distinct_values('Selection_Status');

$selectedRows = fetch_selected_candidates_report($filters);
$selectedTotals = calculate_consolidated_totals($selectedRows, [
    'total_selected_candidate',
    'offer_generated_yes',
    'offer_generated_no',
    'offer_generated_pending',
    'offer_link_with_link',
    'offer_link_blank',
    'link_verified_yes',
    'link_verified_no',
    'link_verified_pending',
    'receipt_confirmed_yes',
    'receipt_confirmed_no',
    'receipt_confirmed_pending',
    'joined_yes',
    'joined_no',
    'joined_pending',
]);

$shortlistedRows = fetch_shortlisted_onhold_report($filters);
$shortlistedTotals = calculate_consolidated_totals($shortlistedRows, [
    'total_shortlisted_onhold_candidate',
    'shortlist_status_shortlisted',
    'shortlist_status_selected',
    'shortlist_status_rejected',
    'shortlist_status_onhold',
    'offer_generated_yes',
    'offer_generated_no',
    'offer_generated_pending',
    'offer_link_with_link',
    'offer_link_blank',
    'link_verified_yes',
    'link_verified_no',
    'link_verified_pending',
    'receipt_confirmed_yes',
    'receipt_confirmed_no',
    'receipt_confirmed_pending',
    'joined_yes',
    'joined_no',
    'joined_pending',
]);

render_header('Consolidated report', ['main_container_class' => 'container-fluid']);
?>
<h1 class="h3 mb-4">Consolidated report</h1>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">Filters</h2>
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="aggregator" class="form-label">Aggregator</label>
                <select class="form-select" id="aggregator" name="aggregator">
                    <option value="">All Aggregators</option>
                    <?php foreach ($aggregatorOptions as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $filters['aggregator'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="job_fair" class="form-label">Job Fair No</label>
                <select class="form-select" id="job_fair" name="job_fair">
                    <option value="">All Job Fairs</option>
                    <?php foreach ($jobFairOptions as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $filters['job_fair'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categoryOptions as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $filters['category'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="selection_status" class="form-label">Selection Status (section 2)</label>
                <select class="form-select" id="selection_status" name="selection_status">
                    <option value="">All</option>
                    <?php foreach ($selectionStatusOptions as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $filters['selection_status'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply filters</button>
                <a href="consolidated_report.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">First Section: List of Selected Candidate</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th rowspan="2">Job Fair No</th>
                    <th rowspan="2">Total Selected Candidate</th>
                    <th colspan="3" class="text-center">Offer Letter Generated</th>
                    <th colspan="2" class="text-center">Link to Offer Letter</th>
                    <th colspan="3" class="text-center">Link Verified</th>
                    <th colspan="3" class="text-center">Offer Letter Receipt</th>
                    <th colspan="3" class="text-center">Candidate Joined</th>
                </tr>
                <tr>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Pending</th>
                    <th>With Link</th>
                    <th>Blank</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Pending</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Pending</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Pending</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($selectedRows === []): ?>
                    <tr><td colspan="16" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($selectedRows as $row): ?>
                    <tr>
                        <td><?= esc($row['job_fair_no']) ?></td>
                        <td><?= (int) $row['total_selected_candidate'] ?></td>
                        <td><?= (int) $row['offer_generated_yes'] ?></td>
                        <td><?= (int) $row['offer_generated_no'] ?></td>
                        <td><?= (int) $row['offer_generated_pending'] ?></td>
                        <td><?= (int) $row['offer_link_with_link'] ?></td>
                        <td><?= (int) $row['offer_link_blank'] ?></td>
                        <td><?= (int) $row['link_verified_yes'] ?></td>
                        <td><?= (int) $row['link_verified_no'] ?></td>
                        <td><?= (int) $row['link_verified_pending'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_yes'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_no'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_pending'] ?></td>
                        <td><?= (int) $row['joined_yes'] ?></td>
                        <td><?= (int) $row['joined_no'] ?></td>
                        <td><?= (int) $row['joined_pending'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($selectedRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $selectedTotals['total_selected_candidate'] ?></td>
                        <td><?= $selectedTotals['offer_generated_yes'] ?></td>
                        <td><?= $selectedTotals['offer_generated_no'] ?></td>
                        <td><?= $selectedTotals['offer_generated_pending'] ?></td>
                        <td><?= $selectedTotals['offer_link_with_link'] ?></td>
                        <td><?= $selectedTotals['offer_link_blank'] ?></td>
                        <td><?= $selectedTotals['link_verified_yes'] ?></td>
                        <td><?= $selectedTotals['link_verified_no'] ?></td>
                        <td><?= $selectedTotals['link_verified_pending'] ?></td>
                        <td><?= $selectedTotals['receipt_confirmed_yes'] ?></td>
                        <td><?= $selectedTotals['receipt_confirmed_no'] ?></td>
                        <td><?= $selectedTotals['receipt_confirmed_pending'] ?></td>
                        <td><?= $selectedTotals['joined_yes'] ?></td>
                        <td><?= $selectedTotals['joined_no'] ?></td>
                        <td><?= $selectedTotals['joined_pending'] ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">Second Section: List of Shortlisted/Onhold Candidates</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th rowspan="2">Job Fair No</th>
                    <th rowspan="2">Total Shortlisted/Onhold Candidate</th>
                    <th colspan="4" class="text-center">Shortlisted Conversion</th>
                    <th colspan="3" class="text-center">Offer Letter Generated</th>
                    <th colspan="2" class="text-center">Link to Offer Letter</th>
                    <th colspan="3" class="text-center">Link Verified</th>
                    <th colspan="3" class="text-center">Offer Letter Receipt Confirmed</th>
                    <th colspan="3" class="text-center">Candidate Joined</th>
                </tr>
                <tr>
                    <th>Shortlisted</th>
                    <th>Selected</th>
                    <th>Rejected</th>
                    <th>Onhold</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Pending</th>
                    <th>With Link</th>
                    <th>Blank</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Pending</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Pending</th>
                    <th>Yes</th>
                    <th>No</th>
                    <th>Pending</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($shortlistedRows === []): ?>
                    <tr><td colspan="20" class="text-center text-muted">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($shortlistedRows as $row): ?>
                    <tr>
                        <td><?= esc($row['job_fair_no']) ?></td>
                        <td><?= (int) $row['total_shortlisted_onhold_candidate'] ?></td>
                        <td><?= (int) $row['shortlist_status_shortlisted'] ?></td>
                        <td><?= (int) $row['shortlist_status_selected'] ?></td>
                        <td><?= (int) $row['shortlist_status_rejected'] ?></td>
                        <td><?= (int) $row['shortlist_status_onhold'] ?></td>
                        <td><?= (int) $row['offer_generated_yes'] ?></td>
                        <td><?= (int) $row['offer_generated_no'] ?></td>
                        <td><?= (int) $row['offer_generated_pending'] ?></td>
                        <td><?= (int) $row['offer_link_with_link'] ?></td>
                        <td><?= (int) $row['offer_link_blank'] ?></td>
                        <td><?= (int) $row['link_verified_yes'] ?></td>
                        <td><?= (int) $row['link_verified_no'] ?></td>
                        <td><?= (int) $row['link_verified_pending'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_yes'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_no'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_pending'] ?></td>
                        <td><?= (int) $row['joined_yes'] ?></td>
                        <td><?= (int) $row['joined_no'] ?></td>
                        <td><?= (int) $row['joined_pending'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($shortlistedRows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $shortlistedTotals['total_shortlisted_onhold_candidate'] ?></td>
                        <td><?= $shortlistedTotals['shortlist_status_shortlisted'] ?></td>
                        <td><?= $shortlistedTotals['shortlist_status_selected'] ?></td>
                        <td><?= $shortlistedTotals['shortlist_status_rejected'] ?></td>
                        <td><?= $shortlistedTotals['shortlist_status_onhold'] ?></td>
                        <td><?= $shortlistedTotals['offer_generated_yes'] ?></td>
                        <td><?= $shortlistedTotals['offer_generated_no'] ?></td>
                        <td><?= $shortlistedTotals['offer_generated_pending'] ?></td>
                        <td><?= $shortlistedTotals['offer_link_with_link'] ?></td>
                        <td><?= $shortlistedTotals['offer_link_blank'] ?></td>
                        <td><?= $shortlistedTotals['link_verified_yes'] ?></td>
                        <td><?= $shortlistedTotals['link_verified_no'] ?></td>
                        <td><?= $shortlistedTotals['link_verified_pending'] ?></td>
                        <td><?= $shortlistedTotals['receipt_confirmed_yes'] ?></td>
                        <td><?= $shortlistedTotals['receipt_confirmed_no'] ?></td>
                        <td><?= $shortlistedTotals['receipt_confirmed_pending'] ?></td>
                        <td><?= $shortlistedTotals['joined_yes'] ?></td>
                        <td><?= $shortlistedTotals['joined_no'] ?></td>
                        <td><?= $shortlistedTotals['joined_pending'] ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer(); ?>
