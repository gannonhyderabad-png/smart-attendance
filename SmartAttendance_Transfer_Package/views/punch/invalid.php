<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden text-center p-4">
            <div class="display-4 text-warning mb-3">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2"><?= e($title ?? 'Invalid Link') ?></h4>
            <p class="text-muted small mb-4">
                <?= e($message ?? 'This attendance punch link is invalid or expired. Please contact your company administrator for the correct URL.') ?>
            </p>
            <div>
                <a href="<?= base_url() ?>" class="btn btn-outline-primary rounded-pill px-4 btn-sm">
                    <i class="fa-solid fa-house me-1"></i> Return to Homepage
                </a>
            </div>
        </div>
    </div>
</div>
