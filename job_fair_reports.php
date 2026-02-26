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

$employerAggregatorTotals = calculate_totals($employerAggregatorRows);
$aggregatorTotals = calculate_totals($aggregatorRows);
$districtTotals = calculate_totals($districtRows);

render_header('Job fair reports');
?>
<h1 class="h3 mb-4">Job fair reports</h1>

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
