<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 fw-bold section-title mb-3">Supply Intake</h1>
<?php $errors = session('errors') ?? []; ?>

<form action="<?= site_url('supply/intake') ?>" method="post" class="card border-0 shadow-sm">
    <div class="card-body row g-3">
        <?= csrf_field() ?>
        <div class="col-md-6">
            <label class="form-label">Medicine</label>
            <select name="medicine_id" class="form-select <?= isset($errors['medicine_id']) ? 'is-invalid' : '' ?>">
                <option value="">Select medicine</option>
                <?php foreach ($medicines as $medicine): ?>
                    <option value="<?= $medicine['id'] ?>" <?= old('medicine_id') == $medicine['id'] ? 'selected' : '' ?>><?= esc($medicine['generic_name'] . ' - ' . $medicine['strength']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Batch ID</label><input name="batch_number" class="form-control" value="<?= old('batch_number') ?>"></div>
        <div class="col-md-6"><label class="form-label">Warehouse Location</label><input name="warehouse_location" class="form-control" value="<?= old('warehouse_location', 'Main Warehouse') ?>"></div>
        <div class="col-md-3"><label class="form-label">Received Quantity</label><input type="number" min="1" name="received_quantity" class="form-control" value="<?= old('received_quantity') ?>"></div>
        <div class="col-md-3"><label class="form-label">Expiry Date</label><input type="date" name="expiry_date" class="form-control" value="<?= old('expiry_date') ?>"></div>
        <div class="col-md-6"><label class="form-label">Supplier</label><input name="supplier" class="form-control" value="<?= old('supplier') ?>"></div>
        <div class="col-md-3"><label class="form-label">Unit Cost</label><input name="unit_cost" class="form-control" value="<?= old('unit_cost', '0.00') ?>"></div>
        <div class="col-md-3"><label class="form-label">Manufactured Date</label><input type="date" name="manufactured_date" class="form-control" value="<?= old('manufactured_date') ?>"></div>
    </div>
    <div class="card-footer bg-white text-end"><button class="btn btn-success">Save Intake</button></div>
</form>

<?= $this->endSection() ?>
