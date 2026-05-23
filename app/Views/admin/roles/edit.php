<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('/admin/roles') ?>">Role Management</a></li>
        <li class="breadcrumb-item active">Edit: <?= esc($role['label']) ?></li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Edit Role
                </h5>
            </div>
            <div class="card-body p-4">

                <?php if (isset($errors) && count($errors) > 0): ?>
                <div class="alert alert-danger">
                    <strong><i class="bi bi-exclamation-triangle me-1"></i>Fix the following:</strong>
                    <ul class="mb-0 ps-3 mt-2">
                        <?php foreach ($errors as $e): ?>
                            <li class="small"><?= esc($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form action="<?= base_url('/admin/roles/update/' . $role['id']) ?>" method="POST" novalidate>
                    <?= csrf_field() ?>

                    <!-- Role slug — locked for core roles -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Role Slug <span class="text-danger">*</span>
                        </label>
                        <?php $isCore = in_array($role['name'], ['superadmin', 'manager', 'staff'], true); ?>
                        <input type="text" id="name" name="name"
                               class="form-control font-monospace <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                               value="<?= old('name', esc($role['name'])) ?>"
                               <?= $isCore ? 'readonly' : 'oninput="this.value=this.value.toLowerCase().replace(/[^a-z0-9_-]/g,\'\')"' ?>
                               required>
                        <?php if ($isCore): ?>
                            <div class="form-text text-warning">
                                <i class="bi bi-lock me-1"></i>
                                Core role slugs are locked to prevent breaking filter logic.
                            </div>
                        <?php else: ?>
                            <div class="form-text">Changing this slug requires updating the matching Filter class.</div>
                        <?php endif; ?>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Display label -->
                    <div class="mb-3">
                        <label for="label" class="form-label fw-semibold">
                            Display Label <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="label" name="label"
                               class="form-control <?= isset($errors['label']) ? 'is-invalid' : '' ?>"
                               value="<?= old('label', esc($role['label'])) ?>"
                               required>
                        <?php if (isset($errors['label'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['label']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="form-control"><?= old('description', esc($role['description'] ?? '')) ?></textarea>
                    </div>

                    <!-- Meta info -->
                    <div class="alert alert-light border small text-muted mb-4">
                        <i class="bi bi-clock me-1"></i>
                        Last updated: <?= date('F d, Y', strtotime($role['updated_at'])) ?>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?= base_url('/admin/roles') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-warning fw-semibold">
                            <i class="bi bi-save me-1"></i>Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
