<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url('/admin/roles') ?>">Role Management</a></li>
        <li class="breadcrumb-item active">Create Role</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-plus-circle me-2"></i>Create New Role
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

                <form action="<?= base_url('/admin/roles/store') ?>" method="POST" novalidate>
                    <?= csrf_field() ?>

                    <!-- Role slug -->
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Role Slug <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="form-control font-monospace <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                               placeholder="e.g. inventory_auditor"
                               value="<?= old('name') ?>"
                               oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9_-]/g,'')"
                               required>
                        <div class="form-text">
                            Lowercase letters, numbers, hyphens, underscores only.
                            This is what filters compare against, for example <code>session('user')['role'] === 'manager'</code>.
                        </div>
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
                               placeholder="e.g. Inventory Auditor"
                               value="<?= old('label') ?>"
                               required>
                        <div class="form-text">Human-readable name shown in the UI and navbar badge.</div>
                        <?php if (isset($errors['label'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['label']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="form-control"
                                  placeholder="What pages or actions does this role have access to?"><?= old('description') ?></textarea>
                    </div>

                    <!-- Implementation reminder -->
                    <div class="alert alert-info small mb-4">
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Remember:</strong> Creating a role here only saves it to the database.
                        To enforce it, you must also create a corresponding
                        <code>Filter</code> class and register it in <code>Config/Filters.php</code>,
                        then apply it to routes in <code>Config/Routes.php</code>.
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="<?= base_url('/admin/roles') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-danger fw-semibold">
                            <i class="bi bi-save me-1"></i>Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
