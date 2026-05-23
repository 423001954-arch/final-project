<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-uppercase text-muted small mb-1">Healthcare Supply Chain</p>
        <h1 class="h3 fw-bold section-title mb-1">Supply Operations</h1>
        <p class="text-muted mb-0">Batch intake, warehouse locking, FEFO fulfillment, and expiry removal in one workflow.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= site_url('supply/intake') ?>" class="btn btn-success">Intake Stock</a>
        <a href="<?= site_url('supply/requisition') ?>" class="btn btn-primary">New Requisition</a>
        <a href="<?= site_url('supply/disposal') ?>" class="btn btn-outline-danger">Disposal</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="metric-card p-3"><div class="text-muted small">Facilities</div><div class="display-6 fw-bold"><?= count($facilities) ?></div></div></div>
    <div class="col-md-4"><div class="metric-card p-3"><div class="text-muted small">Medicines</div><div class="display-6 fw-bold"><?= count($medicines) ?></div></div></div>
    <div class="col-md-4"><div class="metric-card p-3"><div class="text-muted small">Expired Active Batches</div><div class="display-6 fw-bold text-danger"><?= count($expired) ?></div></div></div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Workflow Required by the System</div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ([
                ['Supply Intake', 'Admin/Manager inputs stock with Batch IDs and Expiry Dates.'],
                ['Validation', 'Batch ID must be unique and expiry date must be in the future.'],
                ['Storage Logic', 'Stock is locked to a specific warehouse location.'],
                ['Requisition', 'Clinic/user requests medicine supplies.'],
                ['Smart Fulfillment', 'FEFO automatically selects the closest valid expiry batch first.'],
                ['Disposal Protocol', 'Expired active batches are flagged and removed from active stock.'],
            ] as [$title, $body]): ?>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100 bg-light">
                        <div class="fw-bold"><?= esc($title) ?></div>
                        <div class="small text-muted"><?= esc($body) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Current Batches</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Medicine</th><th>Batch ID</th><th>Facility</th><th>Warehouse</th><th>Expiry</th><th>Status</th><th class="text-end">Available</th></tr></thead>
            <tbody>
                <?php foreach ($batches as $batch): ?>
                    <tr>
                        <td><?= esc($batch['generic_name']) ?><div class="small text-muted"><?= esc($batch['sku']) ?></div></td>
                        <td><?= esc($batch['batch_number']) ?></td>
                        <td><?= esc($batch['facility_name'] ?? '') ?></td>
                        <td><?= esc($batch['warehouse_location']) ?></td>
                        <td><?= esc($batch['expiry_date']) ?></td>
                        <td><span class="badge text-bg-secondary"><?= esc($batch['status']) ?></span></td>
                        <td class="text-end"><?= number_format((int) $batch['available_quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
