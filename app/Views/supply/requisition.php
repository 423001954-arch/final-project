<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 fw-bold section-title mb-3">Clinic Requisition</h1>

<?php if ($allocation = session('allocation')): ?>
    <div class="alert alert-success">
        FEFO picked <?= number_format((int) $allocation['allocated_quantity']) ?> unit(s).
        <?php foreach ($allocation['allocations'] as $pick): ?>
            <div class="small">Batch <?= esc($pick['batch_number']) ?>, expiry <?= esc($pick['expiry_date']) ?>, qty <?= number_format((int) $pick['picked_quantity']) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="<?= site_url('supply/requisition') ?>" method="post" class="card border-0 shadow-sm">
    <div class="card-body row g-3">
        <?= csrf_field() ?>
        <div class="col-md-4">
            <label class="form-label">Facility</label>
            <select name="facility_id" class="form-select">
                <option value="">Select facility</option>
                <?php foreach ($facilities as $facility): ?>
                    <option value="<?= $facility['id'] ?>" <?= old('facility_id') == $facility['id'] ? 'selected' : '' ?>><?= esc($facility['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">Medicine</label>
            <select name="medicine_id" class="form-select">
                <option value="">Select medicine</option>
                <?php foreach ($medicines as $medicine): ?>
                    <option value="<?= $medicine['id'] ?>" <?= old('medicine_id') == $medicine['id'] ? 'selected' : '' ?>><?= esc($medicine['generic_name'] . ' - ' . $medicine['strength']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Quantity</label><input type="number" min="1" name="quantity" class="form-control" value="<?= old('quantity') ?>"></div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3"><?= old('remarks') ?></textarea></div>
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-primary">Submit Requisition</button></div>
</form>

<?= $this->endSection() ?>
