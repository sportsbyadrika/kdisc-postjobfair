<?php
require_once __DIR__ . '/auth.php';

function render_header(string $title): void
{
    $user = current_user();
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-light navbar-silver shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/dashboard.php">
            <span class="brand-icon">🚀</span>
            <span>Job Fair Status Tracker</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <?php if ($user): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/activities.php">Activities</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Job fair</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/job_fair_results.php">Job fair result data</a></li>
                            <li><a class="dropdown-item" href="/call_history_report.php">Call history report</a></li>
                            <li><a class="dropdown-item" href="/job_fair_reports.php">Reports</a></li>
                            <?php if ($user['role'] === 'administrator'): ?>
                                <li><a class="dropdown-item" href="/job_fair_result_upload.php">Upload job fair result CSV</a></li>
                                <li><a class="dropdown-item" href="/job_fair_results_export.php">Download job fair result CSV</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php if ($user['role'] === 'administrator'): ?>
                        <li class="nav-item"><a class="nav-link" href="/users.php">Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="/reports.php">Login Reports</a></li>
                    <?php endif; ?>
                </ul>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= esc($user['name']) ?> (<?= esc($user['mobile_number']) ?>)
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted"><?= esc($user['email']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container py-4 flex-grow-1">
<?php
}

function render_footer(): void
{
    ?>
</main>
<footer class="bg-light border-top py-3 mt-auto">
    <div class="container text-center text-muted small">© <?= date('Y') ?> Job Fair Status Tracker</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const clearStaleBackdrop = () => {
        if (document.querySelector('.modal.show')) {
            return;
        }

        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
    };

    clearStaleBackdrop();
    document.addEventListener('hidden.bs.modal', clearStaleBackdrop);
});
</script>
</body>
</html>
<?php
}
