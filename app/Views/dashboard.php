<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php $dashboard = $dashboard ?? ['is_ready' => false]; ?>

<style>
    .ops-hero {
        background: #0f766e;
        color: #fff;
        border-radius: 8px;
        padding: 28px;
    }
    .ops-metric {
        border: 1px solid #d9e2e7;
        border-radius: 8px;
        padding: 18px;
        background: #fff;
        min-height: 110px;
    }
    .ops-metric strong {
        display: block;
        font-size: 1.7rem;
        line-height: 1;
    }
    .workflow-step {
        border-left: 4px solid #0f766e;
        padding: 14px 16px;
        background: #f8fbfa;
        border-radius: 0 8px 8px 0;
        height: 100%;
    }
</style>

<div class="ops-hero mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="text-uppercase small mb-2 opacity-75">Healthcare Supply Chain</p>
            <h1 class="h3 fw-bold mb-2">Medicine Stock Control Dashboard</h1>
            <p class="mb-0 opacity-75">
                Intake, batch validation, warehouse storage, clinic requisitions, FEFO fulfillment, and expiry disposal tracking.
            </p>
        </div>
        <div class="text-lg-end">
            <div class="small opacity-75">Signed in as</div>
            <div class="fw-semibold"><?= esc(session('user')['name'] ?? 'Operations User') ?></div>
            <span class="badge text-bg-light mt-2"><?= esc(ucfirst(session('user')['role'] ?? 'staff')) ?></span>
        </div>
    </div>
</div>

<?php if (! ($dashboard['is_ready'] ?? false)): ?>
    <div class="alert alert-warning">
        <?= esc($dashboard['message'] ?? 'Supply-chain data is not ready yet.') ?>
    </div>
<?php else: ?>
    <?php $metrics = $dashboard['metrics']; ?>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="ops-metric">
                <span class="text-muted small">Active Stock Units</span>
                <strong class="text-success my-1"><?= number_format($metrics['active_stock']) ?></strong>
                <span class="small text-success">Available, non-expired stock</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ops-metric">
                <span class="text-muted small">Available Batches</span>
                <strong><?= number_format($metrics['available_batches']) ?></strong>
                <span class="small text-muted">FEFO eligible lots</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ops-metric">
                <span class="text-muted small">Active Medicines</span>
                <strong><?= number_format($metrics['active_medicines']) ?></strong>
                <span class="small text-muted">Catalogued supply items</span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ops-metric">
                <span class="text-muted small">Active Facilities</span>
                <strong><?= number_format($metrics['active_facilities']) ?></strong>
                <span class="small text-muted">Warehouses and clinics</span>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <h2 class="h5 fw-bold mb-3">Operating Workflow</h2>
            <div class="row g-3">
                <?php foreach ($dashboard['workflow'] as $step): ?>
                    <div class="col-md-6">
                        <div class="workflow-step">
                            <h3 class="h6 fw-bold mb-2"><?= esc($step['title']) ?></h3>
                            <p class="small text-muted mb-0"><?= esc($step['status']) ?></p>
                            </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <h2 class="h5 fw-bold mb-3">Expiry Watch</h2>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Expiring Within 30 Days</div>
                <div class="list-group list-group-flush">
                    <?php if ($dashboard['expiring_soon'] === []): ?>
                        <div class="list-group-item text-muted small">No near-expiry active stock.</div>
                    <?php endif; ?>
                    <?php foreach ($dashboard['expiring_soon'] as $item): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold"><?= esc($item['generic_name']) ?></div>
                                    <div class="small text-muted"><?= esc($item['facility_name'] ?? 'No facility') ?> · <?= esc($item['warehouse_location'] ?? 'Main Warehouse') ?></div>
                                </div>
                                <div class="text-end small">
                                    <div class="badge bg-light text-dark border mb-1"><?= esc($item['batch_number']) ?></div>
                                    <div class="text-danger fw-bold"><?= esc($item['expiry_date']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Expired Active Stock</div>
                <div class="card-body">
                    <div class="display-6 fw-bold text-danger"><?= number_format($metrics['expired_batches']) ?></div>
                    <p class="small text-muted mb-2">Run this background check to remove expired batches from active FEFO stock:</p>
                    <code class="bg-light p-2 rounded d-block text-dark border">php spark supply:flag-expired</code>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Recent Stock Movements</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Movement</th>
                        <th>Medicine</th>
                        <th>Batch</th>
                        <th>Facility</th>
                        <th class="text-end">Quantity</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dashboard['recent_movements'] === []): ?>
                        <tr>
                            <td colspan="6" class="text-muted small text-center py-3">No stock movement history yet.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($dashboard['recent_movements'] as $movement): ?>
                        <?php 
                            $mType = strtoupper($movement['movement_type'] ?? '');
                            $badgeClass = 'bg-secondary';
                            if (strpos($mType, 'IN') !== false || strpos($mType, 'RECEIVE') !== false) $badgeClass = 'bg-success-subtle text-success';
                            if (strpos($mType, 'OUT') !== false || strpos($mType, 'DISPOSAL') !== false) $badgeClass = 'bg-danger-subtle text-danger';
                            if (strpos($mType, 'ALLOCATE') !== false || strpos($mType, 'REQ') !== false) $badgeClass = 'bg-primary-subtle text-primary';
                        ?>
                        <tr>
                            <td><span class="badge <?= $badgeClass ?> text-uppercase"><?= esc($mType) ?></span></td>
                            <td class="fw-semibold"><?= esc($movement['generic_name']) ?></td>
                            <td><span class="font-monospace text-muted small"><?= esc($movement['batch_number']) ?></span></td>
                            <td><?= esc($movement['facility_name'] ?? 'No facility') ?></td>
                            <td class="text-end fw-bold"><?= number_format((int) $movement['quantity']) ?></td>
                            <td class="small text-muted">
                                <?= esc(trim(($movement['reference_type'] ?? '') . ' ' . ($movement['reference_id'] ?? ''))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>