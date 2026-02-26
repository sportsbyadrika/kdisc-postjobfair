<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

if (current_user()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;
if (is_post()) {
    $mobile = trim($_POST['mobile_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = login_user($mobile, $password);
    if (!$error) {
        header('Location: /dashboard.php');
        exit;
    }
}

render_header('Login');
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Sign in</h1>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= esc($error) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input class="form-control" name="mobile_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input class="form-control" type="password" name="password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>
