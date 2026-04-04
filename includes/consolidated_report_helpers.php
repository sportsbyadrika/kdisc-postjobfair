<?php

const CONSOLIDATED_CANDIDATE_COLUMNS = [
    'Job Fair No' => 'job_fair_no',
    'DWMS_Id' => 'dwms_id',
    'Candidate_Name' => 'candidate_name',
    'Employer_ID' => 'employer_id',
    'Employer_Name' => 'employer_name',
    'Job_Id' => 'job_id',
    'Job_Title_Name' => 'job_title_name',
    'Candidate_District' => 'candidate_district',
    'Mobile_Number' => 'mobile_number',
    'SDPK' => 'sdpk',
    'SDPK_District' => 'sdpk_district',
    'Aggregator' => 'aggregator',
    'CRM_Member' => 'crm_member',
    'Category' => 'category',
    'Selction_Status' => 'selection_status',
    'Shortlist_Candidate_Status' => 'shortlist_candidate_status',
    'offer_letter_Generated' => 'offer_letter_generated',
    'Offer_letter_generated_date' => 'offer_letter_generated_date',
    'Link_to_offer_letter' => 'link_to_offer_letter',
    'response_from_employer' => 'response_from_employer',
    'Willing_to_Join' => 'willing_to_join',
    'Challenge_to_be_addressed' => 'challenge_to_be_addressed',
    'Specific_Isues_Report_to_MS' => 'specific_issues_report_to_ms',
    'Remarks_Candidate_Join' => 'remarks_candidate_join',
];

const CONSOLIDATED_SECTION_LABELS = [
    'selected' => 'First Section: List of Selected Candidate',
    'shortlisted' => 'Second Section: List of Shortlisted/Onhold Candidates',
];

const CONSOLIDATED_METRIC_LABELS = [
    'selected' => [
        'total_selected_candidate' => 'Total Selected Candidate',
        'offer_generated_yes' => 'Offer Letter Generated: Yes',
        'offer_generated_no' => 'Offer Letter Generated: No',
        'offer_link_with_link' => 'Offer Letter Softcopy: Received',
        'offer_link_blank' => 'Offer Letter Softcopy: Not Received',
        'link_verified_yes' => 'Softcopy Verified: Yes',
        'link_verified_no' => 'Softcopy Verified: No',
        'link_verified_pending' => 'Softcopy Verified: Pending',
        'receipt_confirmed_yes' => 'Offer Letter Receipt: Yes',
        'receipt_confirmed_no' => 'Offer Letter Receipt: No',
        'receipt_confirmed_pending' => 'Offer Letter Receipt: Pending',
        'joined_yes' => 'Candidate Joined: Yes',
        'joined_no' => 'Candidate Joined: No',
        'joined_pending' => 'Candidate Joined: Pending',
    ],
    'shortlisted' => [
        'total_shortlisted_onhold_candidate' => 'Total Shortlisted/Onhold Candidate',
        'shortlist_status_selected' => 'Shortlisted Conversion: Selected',
        'shortlist_status_rtd_jobs' => 'Shortlisted Conversion: RTD Jobs',
        'shortlist_status_rejected' => 'Shortlisted Conversion: Rejected',
        'shortlist_status_onhold' => 'Shortlisted Conversion: Pending',
        'offer_generated_yes' => 'Offer Letter Generated: Yes',
        'offer_generated_no' => 'Offer Letter Generated: No',
        'offer_link_with_link' => 'Offer Letter Softcopy: Received',
        'offer_link_blank' => 'Offer Letter Softcopy: Not Received',
        'link_verified_yes' => 'Softcopy Verified: Yes',
        'link_verified_no' => 'Softcopy Verified: No',
        'link_verified_pending' => 'Softcopy Verified: Pending',
        'receipt_confirmed_yes' => 'Offer Letter Receipt Confirmed: Yes',
        'receipt_confirmed_no' => 'Offer Letter Receipt Confirmed: No',
        'receipt_confirmed_pending' => 'Offer Letter Receipt Confirmed: Pending',
        'joined_yes' => 'Candidate Joined: Yes',
        'joined_no' => 'Candidate Joined: No',
        'joined_pending' => 'Candidate Joined: Pending',
    ],
];

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
        'candidate_district' => trim((string) ($_GET['candidate_district'] ?? '')),
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

    if (($filters['aggregator'] ?? '') !== '') {
        $conditions[] = "COALESCE(NULLIF(TRIM(Aggregator), ''), 'Unknown') = ?";
        $params[] = $filters['aggregator'];
    }

    if (($filters['candidate_district'] ?? '') !== '') {
        $conditions[] = "COALESCE(NULLIF(TRIM(Candidate_District), ''), 'Unknown') = ?";
        $params[] = $filters['candidate_district'];
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
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) IN ('no', 'pending') OR TRIM(COALESCE(Offer_Letter_Generated, '')) = '' THEN 1 ELSE 0 END) AS offer_generated_no,
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
    $categoryExpression = normalized_column('Category');

    $sql = "SELECT
            COALESCE(NULLIF(TRIM(Job_Fair_No), ''), 'Unknown') AS job_fair_no,
            COUNT(*) AS total_shortlisted_onhold_candidate,
            SUM(CASE WHEN $shortlistStatusExpression = 'shortlisted' THEN 1 ELSE 0 END) AS shortlist_status_shortlisted,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' THEN 1 ELSE 0 END) AS shortlist_status_selected,
            SUM(CASE WHEN $selectionStatusExpression IN ('shortlisted', 'onhold') AND $categoryExpression IN ('k-disc-rtd', 'rtd') THEN 1 ELSE 0 END) AS shortlist_status_rtd_jobs,
            SUM(CASE WHEN $shortlistStatusExpression IN ('rejected', 'candidatenotinterested') THEN 1 ELSE 0 END) AS shortlist_status_rejected,
            SUM(CASE WHEN $shortlistStatusExpression IN ('onhold', '', 'shortlisted') THEN 1 ELSE 0 END) AS shortlist_status_onhold,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'yes' THEN 1 ELSE 0 END) AS offer_generated_yes,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND (LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) IN ('no', 'pending') OR TRIM(COALESCE(Offer_Letter_Generated, '')) = '') THEN 1 ELSE 0 END) AS offer_generated_no,
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

function fetch_selected_candidates_report_by_job_station(array $filters): array
{
    $params = [];
    $conditions = build_common_conditions($filters, $params);
    $conditions[] = normalized_column('Selection_Status') . " = 'selected'";

    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "SELECT
            COALESCE(NULLIF(TRIM(Candidate_Jobstation), ''), 'Unknown') AS job_station,
            COUNT(*) AS total_selected_candidate,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'yes' THEN 1 ELSE 0 END) AS offer_generated_yes,
            SUM(CASE WHEN LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) IN ('no', 'pending') OR TRIM(COALESCE(Offer_Letter_Generated, '')) = '' THEN 1 ELSE 0 END) AS offer_generated_no,
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
        GROUP BY job_station
        ORDER BY job_station ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function fetch_shortlisted_onhold_report_by_job_station(array $filters): array
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
    $categoryExpression = normalized_column('Category');

    $sql = "SELECT
            COALESCE(NULLIF(TRIM(Candidate_Jobstation), ''), 'Unknown') AS job_station,
            COUNT(*) AS total_shortlisted_onhold_candidate,
            SUM(CASE WHEN $shortlistStatusExpression = 'shortlisted' THEN 1 ELSE 0 END) AS shortlist_status_shortlisted,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' THEN 1 ELSE 0 END) AS shortlist_status_selected,
            SUM(CASE WHEN ($selectionStatusExpression = 'selected' AND $categoryExpression IN ('k-disc-rtd', 'rtd')) OR ($shortlistStatusExpression = 'selected' AND $categoryExpression IN ('k-disc-rtd', 'rtd')) THEN 1 ELSE 0 END) AS shortlist_status_rtd_jobs,
            SUM(CASE WHEN $shortlistStatusExpression IN ('rejected', 'candidatenotinterested') THEN 1 ELSE 0 END) AS shortlist_status_rejected,
            SUM(CASE WHEN $shortlistStatusExpression IN ('onhold', '', 'shortlisted') THEN 1 ELSE 0 END) AS shortlist_status_onhold,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'yes' THEN 1 ELSE 0 END) AS offer_generated_yes,
            SUM(CASE WHEN $shortlistStatusExpression = 'selected' AND (LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) IN ('no', 'pending') OR TRIM(COALESCE(Offer_Letter_Generated, '')) = '') THEN 1 ELSE 0 END) AS offer_generated_no,
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
        GROUP BY job_station
        ORDER BY job_station ASC";

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

function build_consolidated_detail_conditions(string $section, string $metric, array $filters, array &$params, ?string $jobFairRow, string $groupField = 'job_fair_no'): array
{
    $conditions = build_common_conditions($filters, $params);
    $selectionStatusExpression = normalized_column('Selection_Status');
    $shortlistStatusExpression = normalized_column('Shortlist_Candidate_Status');

    if ($jobFairRow !== null) {
        $groupColumn = $groupField === 'candidate_job_station' ? 'Candidate_Jobstation' : 'Job_Fair_No';
        $conditions[] = "COALESCE(NULLIF(TRIM($groupColumn), ''), 'Unknown') = ?";
        $params[] = $jobFairRow;
    }

    if ($section === 'selected') {
        $conditions[] = "$selectionStatusExpression = 'selected'";

        switch ($metric) {
            case 'offer_generated_yes':
                $conditions[] = "LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'yes'";
                break;
            case 'offer_generated_no':
                $conditions[] = "(LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) IN ('no', 'pending') OR TRIM(COALESCE(Offer_Letter_Generated, '')) = '')";
                break;
            case 'offer_link_with_link':
                $conditions[] = "TRIM(COALESCE(Link_to_Offer_letter, '')) <> ''";
                break;
            case 'offer_link_blank':
                $conditions[] = "TRIM(COALESCE(Link_to_Offer_letter, '')) = ''";
                break;
            case 'link_verified_yes':
                $conditions[] = "LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'yes'";
                break;
            case 'link_verified_no':
                $conditions[] = "LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'no'";
                break;
            case 'link_verified_pending':
                $conditions[] = "(LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'pending' OR TRIM(COALESCE(Link_to_Offer_letter_verified, '')) = '')";
                break;
            case 'receipt_confirmed_yes':
                $conditions[] = "LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'yes'";
                break;
            case 'receipt_confirmed_no':
                $conditions[] = "LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'no'";
                break;
            case 'receipt_confirmed_pending':
                $conditions[] = "(LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'pending' OR TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, '')) = '')";
                break;
            case 'joined_yes':
                $conditions[] = "LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'yes'";
                break;
            case 'joined_no':
                $conditions[] = "LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'no'";
                break;
            case 'joined_pending':
                $conditions[] = "(LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'pending' OR TRIM(COALESCE(Candidate_Joined_Status, '')) = '')";
                break;
            case 'total_selected_candidate':
            default:
                break;
        }

        return $conditions;
    }

    $conditions[] = "$selectionStatusExpression IN ('shortlisted', 'onhold')";

    if ($filters['selection_status'] !== '') {
        $conditions[] = "$selectionStatusExpression = ?";
        $params[] = strtolower(str_replace(' ', '', $filters['selection_status']));
    }

    switch ($metric) {
        case 'shortlist_status_selected':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            break;
        case 'shortlist_status_rejected':
            $conditions[] = "$shortlistStatusExpression IN ('rejected', 'candidatenotinterested')";
            break;
        case 'shortlist_status_rtd_jobs':
            $categoryExpression = normalized_column('Category');
            $conditions[] = "$selectionStatusExpression IN ('shortlisted', 'onhold')";
            $conditions[] = "$categoryExpression IN ('k-disc-rtd', 'rtd')";
            break;
        case 'shortlist_status_onhold':
            $conditions[] = "$shortlistStatusExpression IN ('onhold', '', 'shortlisted')";
            break;
        case 'offer_generated_yes':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) = 'yes'";
            break;
        case 'offer_generated_no':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "(LOWER(TRIM(COALESCE(Offer_Letter_Generated, ''))) IN ('no', 'pending') OR TRIM(COALESCE(Offer_Letter_Generated, '')) = '')";
            break;
        case 'offer_link_with_link':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "TRIM(COALESCE(Link_to_Offer_letter, '')) <> ''";
            break;
        case 'offer_link_blank':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "TRIM(COALESCE(Link_to_Offer_letter, '')) = ''";
            break;
        case 'link_verified_yes':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'yes'";
            break;
        case 'link_verified_no':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'no'";
            break;
        case 'link_verified_pending':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "(LOWER(TRIM(COALESCE(Link_to_Offer_letter_verified, ''))) = 'pending' OR TRIM(COALESCE(Link_to_Offer_letter_verified, '')) = '')";
            break;
        case 'receipt_confirmed_yes':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'yes'";
            break;
        case 'receipt_confirmed_no':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'no'";
            break;
        case 'receipt_confirmed_pending':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "(LOWER(TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, ''))) = 'pending' OR TRIM(COALESCE(Confirm_Offer_Letter_Receipt_by_Candidate, '')) = '')";
            break;
        case 'joined_yes':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'yes'";
            break;
        case 'joined_no':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'no'";
            break;
        case 'joined_pending':
            $conditions[] = "$shortlistStatusExpression = 'selected'";
            $conditions[] = "(LOWER(TRIM(COALESCE(Candidate_Joined_Status, ''))) = 'pending' OR TRIM(COALESCE(Candidate_Joined_Status, '')) = '')";
            break;
        case 'total_shortlisted_onhold_candidate':
        default:
            break;
    }

    return $conditions;
}

function fetch_consolidated_detail_rows(string $section, string $metric, array $filters, ?string $jobFairRow, string $groupField = 'job_fair_no'): array
{
    $params = [];
    $conditions = build_consolidated_detail_conditions($section, $metric, $filters, $params, $jobFairRow, $groupField);
    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "SELECT
            COALESCE(NULLIF(TRIM(Job_Fair_No), ''), 'Unknown') AS job_fair_no,
            COALESCE(TRIM(DWMS_ID), '') AS dwms_id,
            COALESCE(TRIM(Candidate_Name), '') AS candidate_name,
            COALESCE(TRIM(Employer_ID), '') AS employer_id,
            COALESCE(TRIM(Employer_Name), '') AS employer_name,
            COALESCE(TRIM(Job_Id), '') AS job_id,
            COALESCE(TRIM(Job_Title_Name), '') AS job_title_name,
            COALESCE(TRIM(Candidate_District), '') AS candidate_district,
            COALESCE(TRIM(Mobile_Number), '') AS mobile_number,
            COALESCE(TRIM(SDPK), '') AS sdpk,
            COALESCE(TRIM(SDPK_District), '') AS sdpk_district,
            COALESCE(TRIM(Aggregator), '') AS aggregator,
            COALESCE(TRIM(CRM_Member), '') AS crm_member,
            COALESCE(TRIM(Category), '') AS category,
            COALESCE(TRIM(Selection_Status), '') AS selection_status,
            COALESCE(TRIM(Shortlist_Candidate_Status), '') AS shortlist_candidate_status,
            COALESCE(TRIM(Offer_Letter_Generated), '') AS offer_letter_generated,
            COALESCE(DATE_FORMAT(Offer_Letter_Generated_Date, '%Y-%m-%d %H:%i:%s'), '') AS offer_letter_generated_date,
            COALESCE(TRIM(Link_to_Offer_letter), '') AS link_to_offer_letter,
            COALESCE(TRIM(response_from_employer), '') AS response_from_employer,
            COALESCE(TRIM(Willing_to_Join), '') AS willing_to_join,
            COALESCE(TRIM(Challenge_to_be_addressed), '') AS challenge_to_be_addressed,
            COALESCE(TRIM(Specific_Issues_Report_to_MS), '') AS specific_issues_report_to_ms,
            COALESCE(TRIM(Remarks_Candidate_Join), '') AS remarks_candidate_join
        FROM job_fair_result
        $whereClause
        ORDER BY job_fair_no ASC, Employer_Name ASC, Candidate_Name ASC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}
