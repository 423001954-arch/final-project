<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0 page-title">
        <i class="bi bi-people-fill me-2 text-danger"></i>User Role Assignment
    </h3>
    <a href="<?= base_url('/admin/roles') ?>" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-shield-check me-1"></i>Manage Roles
    </a>
</div>

<div class="alert alert-danger border-0 d-flex align-items-start gap-3 mb-4 small">
    <i class="bi bi-shield-exclamation fs-5 flex-shrink-0 mt-1"></i>
    <div>
        <strong>Admin Only — User Role Assignment</strong><br>
        Changes here take effect on the user's <em>next login</em>.
        Changing a role does not immediately invalidate an active session.
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold mb-0 text-muted">
            <i class="bi bi-table me-2"></i>All Users
            <span class="badge bg-secondary ms-2"><?= count($users) ?></span>
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Current Role</th>
                        <th style="width:280px;">Assign New Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $user): ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?= $i + 1 ?></td>
                        <td class="fw-semibold"><?= esc($user['name']) ?></td>
                        <td class="text-muted small"><?= esc($user['email']) ?></td>
                        <td>
                            <?php
                            $badgeColor = match($user['role_name'] ?? '') {
                                'superadmin' => 'danger',
                                'manager' => 'success',
                                'staff' => 'primary',
                                default   => 'secondary',
                            };
                            ?>
                            <span class="badge bg-<?= $badgeColor ?>">
                                <?= esc($user['role_label'] ?? 'Unassigned') ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            // Prevent the currently logged-in admin from changing their own role
                            $isSelf = ($user['id'] == session('user')['id']);
                            ?>
                            <?php if ($isSelf): ?>
                                <span class="text-muted small fst-italic">
                                    <i class="bi bi-lock me-1"></i>Cannot change own role
                                </span>
                            <?php else: ?>
                            <form action="<?= base_url('/admin/users/assign-role/' . $user['id']) ?>"
                                  method="POST"
                                  class="d-flex gap-2">
                                <?= csrf_field() ?>
                                <select name="role_id" class="form-select form-select-sm">
                                    <?php foreach ($roles as $roleId => $roleLabel): ?>
                                        <option value="<?= $roleId ?>"
                                            <?= $user['role_name'] && $roleLabel === ($user['role_label'] ?? '') ? 'selected' : '' ?>>
                                            <?= esc($roleLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-danger flex-shrink-0">
                                    <i class="bi bi-check2"></i> Assign
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
