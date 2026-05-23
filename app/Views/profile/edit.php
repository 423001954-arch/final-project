<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="mb-4">Edit Profile</h2>

            <?php $errors = session('errors') ?? []; ?>

            <form action="<?= base_url('profile/update') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= old('name', $user['name'] ?? '') ?>">
                    <div class="invalid-feedback"><?= esc($errors['name'] ?? '') ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= old('email', $user['email'] ?? '') ?>">
                    <div class="invalid-feedback"><?= esc($errors['email'] ?? '') ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Profile Image</label>
                    <input type="file" name="profile_image" accept="image/*" class="form-control <?= isset($errors['profile_image']) ? 'is-invalid' : '' ?>" onchange="previewImage(event)">
                    <div class="invalid-feedback"><?= esc($errors['profile_image'] ?? '') ?></div>
                </div>

                <div class="mb-3">
                    <?php if (! empty($user['profile_image'])): ?>
                        <img id="preview" src="<?= base_url('uploads/profiles/' . $user['profile_image']) ?>" class="img-thumbnail" style="max-width: 200px;">
                    <?php else: ?>
                        <img id="preview" src="" class="img-thumbnail" style="max-width: 200px; display:none;">
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-success">Save Changes</button>
                <a href="<?= base_url('profile') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (! file) {
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('preview');
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}
</script>

<?= $this->endSection() ?>
