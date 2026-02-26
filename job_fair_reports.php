<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_auth();

function fetch_grouped_status_counts(string $selectColumns, string $groupByColumns): array
{
    $sql = "SELECT $selectColumns,
        SUM(CASE WHEN LOWER(TRIM(Shortlist_Candidate_Status)) = 'selected' THEN 1 ELSE 0 END) AS selected_count,
        SUM(CASE WHEN LOWER(TRIM(Shortlist_Candidate_Status)) = 'shortlisted' THEN 1 ELSE 0 END) AS shortlisted_count,
        SUM(CASE WHEN LOWER(REPLACE(TRIM(Shortlist_Candidate_Status), ' ', '')) = 'onhold' THEN 1 ELSE 0 END) AS on_hold_count,
        COUNT(*) AS total_count
    FROM job_fair_result
    GROUP BY $groupByColumns
    ORDER BY $groupByColumns";

    return db()->query($sql)->fetchAll();
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
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_footer(); ?>
