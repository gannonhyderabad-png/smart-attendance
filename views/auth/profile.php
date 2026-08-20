<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-user-gear text-primary me-2"></i>Admin Profile & Security</h5>
                <small class="text-muted">Manage your administrator account credentials and password</small>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="<?= base_url('profile/update') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Administrator Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="name" class="form-control bg-light border-start-0" value="<?= e($currentUser['name']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Administrator Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control bg-light border-start-0" value="<?= e($currentUser['email']) ?>" required>
                        </div>
                    </div>

                    <hr class="my-4 text-secondary">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-lock text-secondary me-2"></i>Change Admin Password</h6>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">New Password (leave blank to keep current)</label>
                        <input type="password" name="new_password" class="form-control bg-light" placeholder="••••••••">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-muted">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control bg-light" placeholder="••••••••">
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
