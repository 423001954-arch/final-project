<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-0">Dashboard</h3>
        <p class="text-muted small mt-1">Welcome, <strong><?= esc($user['name']) ?></strong></p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-3 text-center">
            <h5 class="fw-bold"><?= esc($user['name']) ?></h5>
            <span class="badge <?= session('user')['role'] === 'superadmin' ? 'bg-danger' : 'bg-primary' ?>">
                <?= ucfirst(esc(session('user')['role'])) ?>
            </span>
        </div>
    </div>

    <?php if (in_array(session('user')['role'], ['superadmin', 'manager'])): ?>
    <div class="col-lg-8">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm bg-success text-white">
                    <small>Available Stock</small>
                    <h4 class="mb-0"><?= number_format($active_stock ?? 0) ?></h4>
                </div>
            </div>
            </div>
    </div>
    <?php endif; ?>

    <div class="col-12 mt-4">
        <div class="card border-0 shadow-sm p-4">
            <h4>Quick Actions</h4>
            <a href="<?= site_url('supply/requisition') ?>" class="btn btn-primary w-25">Create Requisition</a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>