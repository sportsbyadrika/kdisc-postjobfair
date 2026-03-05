<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

function fetch_filter_options(string $column): array
{
    $sql = "SELECT DISTINCT $column AS value
        FROM job_fair_result
        WHERE $column IS NOT NULL AND TRIM($column) <> ''
        ORDER BY $column ASC";

    return array_map(static fn(array $row): string => (string) $row['value'], db()->query($sql)->fetchAll());
}

function build_exception_filters(): array
{
    return [
        'aggregator' => trim((string) ($_GET['aggregator'] ?? '')),
        'job_fair_no' => trim((string) ($_GET['job_fair_no'] ?? '')),
        'selection_status' => trim((string) ($_GET['selection_status'] ?? '')),
    ];
}

function build_exception_where_clause(array $filters, array &$params): string
{
    $conditions = [];

    if ($filters['aggregator'] !== '') {
        $conditions[] = 'Aggregator = ?';
        $params[] = $filters['aggregator'];
    }

    if ($filters['job_fair_no'] !== '') {
        $conditions[] = 'Job_Fair_No = ?';
        $params[] = $filters['job_fair_no'];
    }

    if ($filters['selection_status'] !== '') {
        $conditions[] = 'Selection_Status = ?';
        $params[] = $filters['selection_status'];
    }

    if ($conditions === []) {
        return '';
    }

    return 'WHERE ' . implode(' AND ', $conditions);
}

function fetch_exception_rows(array $filters): array
{
    $params = [];
    $whereClause = build_exception_where_clause($filters, $params);

    $sql = "SELECT
            COALESCE(NULLIF(TRIM(Job_Fair_No), ''), 'Unknown') AS job_fair_no,
            COUNT(*) AS total_count,
            SUM(CASE WHEN LOWER(TRIM(Offer_Letter_Generated)) = 'yes' THEN 1 ELSE 0 END) AS offer_generated_yes,
            SUM(CASE WHEN LOWER(TRIM(Offer_Letter_Generated)) = 'no' THEN 1 ELSE 0 END) AS offer_generated_no,
            SUM(CASE WHEN LOWER(TRIM(Offer_Letter_Generated)) = 'pending' THEN 1 ELSE 0 END) AS offer_generated_pending,
            SUM(CASE WHEN TRIM(COALESCE(Link_to_Offer_letter, '')) <> '' THEN 1 ELSE 0 END) AS offer_link_with,
            SUM(CASE WHEN TRIM(COALESCE(Link_to_Offer_letter, '')) = '' THEN 1 ELSE 0 END) AS offer_link_blank,
            SUM(CASE WHEN LOWER(TRIM(Link_to_Offer_letter_verified)) = 'yes' THEN 1 ELSE 0 END) AS link_verified_yes,
            SUM(CASE WHEN LOWER(TRIM(Link_to_Offer_letter_verified)) = 'no' THEN 1 ELSE 0 END) AS link_verified_no,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, 'pending'))) = 'pending' THEN 1 ELSE 0 END) AS link_verified_pending,
            SUM(CASE WHEN LOWER(TRIM(Confirm_Offer_Letter_Receipt_by_Candidate)) = 'yes' THEN 1 ELSE 0 END) AS receipt_confirmed_yes,
            SUM(CASE WHEN LOWER(TRIM(Confirm_Offer_Letter_Receipt_by_Candidate)) = 'no' THEN 1 ELSE 0 END) AS receipt_confirmed_no,
            SUM(CASE WHEN LOWER(TRIM(Confirm_Offer_Letter_Receipt_by_Candidate)) = 'pending' THEN 1 ELSE 0 END) AS receipt_confirmed_pending,
            SUM(CASE WHEN LOWER(TRIM(Candidate_Joined_Status)) = 'yes' THEN 1 ELSE 0 END) AS candidate_joined_yes,
            SUM(CASE WHEN LOWER(TRIM(Candidate_Joined_Status)) = 'no' THEN 1 ELSE 0 END) AS candidate_joined_no,
            SUM(CASE WHEN LOWER(TRIM(Candidate_Joined_Status)) = 'pending' THEN 1 ELSE 0 END) AS candidate_joined_pending
        FROM job_fair_result
        $whereClause
        GROUP BY job_fair_no
        ORDER BY job_fair_no ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function calculate_exception_totals(array $rows): array
{
    $keys = [
        'total_count',
        'offer_generated_yes',
        'offer_generated_no',
        'offer_generated_pending',
        'offer_link_with',
        'offer_link_blank',
        'link_verified_yes',
        'link_verified_no',
        'link_verified_pending',
        'receipt_confirmed_yes',
        'receipt_confirmed_no',
        'receipt_confirmed_pending',
        'candidate_joined_yes',
        'candidate_joined_no',
        'candidate_joined_pending',
    ];

    $totals = array_fill_keys($keys, 0);

    foreach ($rows as $row) {
        foreach ($keys as $key) {
            $totals[$key] += (int) ($row[$key] ?? 0);
        }
    }

    return $totals;
}

$filters = build_exception_filters();
$aggregatorOptions = fetch_filter_options('Aggregator');
$jobFairNoOptions = fetch_filter_options('Job_Fair_No');
$selectionStatusOptions = fetch_filter_options('Selection_Status');
$rows = fetch_exception_rows($filters);
$totals = calculate_exception_totals($rows);

render_header('Job Fair Exception Report');
?>
<h1 class="h3 mb-4">Job Fair Exception Report</h1>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">Filters</h2>
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="aggregator" class="form-label">Aggregator</label>
                <select class="form-select" id="aggregator" name="aggregator">
                    <option value="">All Aggregators</option>
                    <?php foreach ($aggregatorOptions as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $filters['aggregator'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="job_fair_no" class="form-label">Job Fair No</label>
                <select class="form-select" id="job_fair_no" name="job_fair_no">
                    <option value="">All Job Fairs</option>
                    <?php foreach ($jobFairNoOptions as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $filters['job_fair_no'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="selection_status" class="form-label">Selection Status</label>
                <select class="form-select" id="selection_status" name="selection_status">
                    <option value="">All Selection Statuses</option>
                    <?php foreach ($selectionStatusOptions as $option): ?>
                        <option value="<?= esc($option) ?>" <?= $filters['selection_status'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply filters</button>
                <a href="job_fair_exception_report.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5">Pivot Table (Job Fair wise)</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th rowspan="2">Job Fair No</th>
                    <th rowspan="2">Total</th>
                    <th colspan="3">Offer Letter Generated</th>
                    <th colspan="2">Link to Offer Letter</th>
                    <th colspan="3">Link Verified</th>
                    <th colspan="3">Offer Letter Receipt Confirmed</th>
                    <th colspan="3">Candidate Joined</th>
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
                <?php if ($rows === []): ?>
                    <tr>
                        <td colspan="17" class="text-center text-muted">No data available.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= esc($row['job_fair_no']) ?></td>
                        <td><?= (int) $row['total_count'] ?></td>
                        <td><?= (int) $row['offer_generated_yes'] ?></td>
                        <td><?= (int) $row['offer_generated_no'] ?></td>
                        <td><?= (int) $row['offer_generated_pending'] ?></td>
                        <td><?= (int) $row['offer_link_with'] ?></td>
                        <td><?= (int) $row['offer_link_blank'] ?></td>
                        <td><?= (int) $row['link_verified_yes'] ?></td>
                        <td><?= (int) $row['link_verified_no'] ?></td>
                        <td><?= (int) $row['link_verified_pending'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_yes'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_no'] ?></td>
                        <td><?= (int) $row['receipt_confirmed_pending'] ?></td>
                        <td><?= (int) $row['candidate_joined_yes'] ?></td>
                        <td><?= (int) $row['candidate_joined_no'] ?></td>
                        <td><?= (int) $row['candidate_joined_pending'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($rows !== []): ?>
                    <tr class="table-secondary fw-semibold">
                        <td>Total</td>
                        <td><?= $totals['total_count'] ?></td>
                        <td><?= $totals['offer_generated_yes'] ?></td>
                        <td><?= $totals['offer_generated_no'] ?></td>
                        <td><?= $totals['offer_generated_pending'] ?></td>
                        <td><?= $totals['offer_link_with'] ?></td>
                        <td><?= $totals['offer_link_blank'] ?></td>
                        <td><?= $totals['link_verified_yes'] ?></td>
                        <td><?= $totals['link_verified_no'] ?></td>
                        <td><?= $totals['link_verified_pending'] ?></td>
                        <td><?= $totals['receipt_confirmed_yes'] ?></td>
                        <td><?= $totals['receipt_confirmed_no'] ?></td>
                        <td><?= $totals['receipt_confirmed_pending'] ?></td>
                        <td><?= $totals['candidate_joined_yes'] ?></td>
                        <td><?= $totals['candidate_joined_no'] ?></td>
                        <td><?= $totals['candidate_joined_pending'] ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer(); ?>
