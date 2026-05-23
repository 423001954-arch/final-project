<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 fw-bold section-title mb-1">Expiry Disposal</h1>
        <p class="text-muted mb-0">Flag expired active batches so they are removed from FEFO stock.</p>
    </div>
    <form action="<?= site_url('supply/disposal/flag-expired') ?>" method="post">
        <?= csrf_field() ?>
        <button class="btn btn-danger">Flag Expired Stock</button>
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Expired Active Batches</div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Facility</th><th>Medicine</th><th>Batch ID</th><th>Expiry</th><th class="text-end">Available</th></tr></thead>
            <tbody>
                <?php if ($expired === []): ?>
                    <tr><td colspan="5" class="text-muted">No expired active stock found.</td></tr>
                <?php endif; ?>
                <?php foreach ($expired as $batch): ?>
                    <tr>
                        <td><?= esc($batch['facility_name'] ?? '') ?></td>
                        <td><?= esc($batch['generic_name'] ?? '') ?></td>
                        <td><?= esc($batch['batch_number']) ?></td>
                        <td class="text-danger"><?= esc($batch['expiry_date']) ?></td>
                        <td class="text-end"><?= number_format((int) $batch['available_quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
