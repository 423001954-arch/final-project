<!DOCTYPE html>
<html>
<head>
    <title><?= esc($title ?? 'Healthcare Supply Chain') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eef4f2; color: #17211f; }
        .app-shell { min-height: 100vh; }
        .app-nav { background: #0f3d3e; box-shadow: 0 8px 24px rgba(15, 61, 62, .16); }
        .navbar-brand { letter-spacing: .02em; }
        .nav-link { border-radius: 6px; padding-inline: .75rem !important; }
        .nav-link:hover { background: rgba(255,255,255,.12); }
        .role-pill { background: #f6c453; color: #17211f; }
        .content-wrap { max-width: 1180px; }
        .card, .alert, .btn, .form-control, .form-select { border-radius: 8px; }
        .metric-card { border: 1px solid #d7e3df; background: #fff; }
        .section-title { color: #0f3d3e; }
    </style>
</head>
<body>
<?php
    $roleAccess = \App\Services\RoleAccess::class;
    $sessionUser = session('user');
    $isLoggedIn = ! empty($sessionUser);
    $role = $roleAccess::normalize($sessionUser['role'] ?? null);
    $profileUrl = site_url('profile');
    $dashboardUrl = $role === 'staff' ? site_url('staff/dashboard') : site_url('dashboard');
    $user = $isLoggedIn ? (new \App\Models\UserModel())->where('email', $sessionUser['email'])->first() : null;
?>

<div class="app-shell">
    <nav class="navbar navbar-dark app-nav navbar-expand-lg">
        <div class="container-fluid content-wrap">
            <a class="navbar-brand fw-bold" href="<?= $dashboardUrl ?>">HealthChain SCM</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto align-items-lg-center gap-lg-1">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item"><a href="<?= $dashboardUrl ?>" class="nav-link text-white">Dashboard</a></li>
                        <?php if (in_array($role, ['superadmin', 'manager'], true)): ?>
                            <li class="nav-item"><a href="<?= site_url('supply') ?>" class="nav-link text-white">Supply Operations</a></li>
                            <li class="nav-item"><a href="<?= site_url('supply/intake') ?>" class="nav-link text-white">Intake</a></li>
                            <li class="nav-item"><a href="<?= site_url('supply/disposal') ?>" class="nav-link text-white">Disposal</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a href="<?= site_url('supply/requisition') ?>" class="nav-link text-white">Requisition</a></li>
                        <?php if ($role === 'superadmin'): ?>
                            <li class="nav-item"><a href="<?= site_url('admin/users') ?>" class="nav-link text-white">Users</a></li>
                            <li class="nav-item"><a href="<?= site_url('admin/roles') ?>" class="nav-link text-white">Roles</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <?php if ($isLoggedIn): ?>
                    <div class="d-flex align-items-center gap-2">
                        <?php if (! empty($user['profile_image'])): ?>
                            <img src="<?= base_url('uploads/profiles/' . $user['profile_image']) ?>" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #fff;">
                        <?php endif; ?>
                        <span class="badge role-pill"><?= esc($roleAccess::label($role)) ?></span>
                        <a href="<?= $profileUrl ?>" class="btn btn-light btn-sm">Profile</a>
                        <a href="<?= site_url('logout') ?>" class="btn btn-outline-light btn-sm">Logout</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container-fluid content-wrap py-4">
        <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $class): ?>
            <?php if (session()->getFlashdata($key)): ?>
                <div class="alert alert-<?= $class ?>"><?= esc(session()->getFlashdata($key)) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= $this->renderSection('content') ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
