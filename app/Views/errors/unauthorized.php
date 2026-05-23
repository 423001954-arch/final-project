<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #dc3545 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="container text-center" style="max-width:520px;">
    <div class="card border-0 shadow-lg rounded-4">
        <div class="card-body py-5 px-4">
            <i class="bi bi-shield-x text-danger" style="font-size:5rem;"></i>
            <h1 class="fw-bold mt-3 mb-1" style="font-size:3rem; color:#dc3545;">403</h1>
            <h5 class="fw-bold mb-2">Access Denied</h5>
            <p class="text-muted mb-4">
                You don't have permission to view this page.<br>
                Your current role — <strong><?= esc(ucfirst(session('user')['role'] ?? 'guest')) ?></strong>
                — does not have access to this area.
            </p>

            <?php
            $role = session('user')['role'] ?? null;
            $home = match($role) {
                'admin'   => '/dashboard',
                'manager' => '/dashboard',
                'staff' => '/staff/dashboard',
                default   => '/login',
            };
            ?>
            <a href="<?= base_url($home) ?>" class="btn btn-danger px-4">
                <i class="bi bi-arrow-left me-2"></i>Back to My Dashboard
            </a>
        </div>
    </div>
    <p class="text-white-50 small mt-3">CI4 RBAC Activity &mdash; CodeIgniter 4</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
