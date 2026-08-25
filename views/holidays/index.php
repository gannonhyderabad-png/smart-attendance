<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-calendar-star text-primary me-2"></i>Public Holidays Calendar
        </h4>
        <p class="text-muted small mb-0">Official company & public holidays automatically reflected in monthly attendance timesheets.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <button type="button" class="btn btn-primary rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
            <i class="fa-solid fa-plus me-1"></i> Add Holiday
        </button>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= e($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Quick Stats Summary -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 rounded-4 shadow-sm bg-primary text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase small opacity-75 fw-semibold">Total Holidays</div>
                    <h3 class="fw-bold mb-0 mt-1"><?= count($holidays) ?></h3>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                    <i class="fa-solid fa-calendar-check fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 rounded-4 shadow-sm bg-white p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Upcoming Next</div>
                    <?php
                    $today = date('Y-m-d');
                    $nextHol = null;
                    foreach ($holidays as $h) {
                        if ($h['holiday_date'] >= $today) {
                            $nextHol = $h;
                            break;
                        }
                    }
                    ?>
                    <h6 class="fw-bold mb-0 mt-1 text-dark"><?= $nextHol ? e($nextHol['title']) : 'No more this year' ?></h6>
                    <small class="text-muted"><?= $nextHol ? date('D, d M Y', strtotime($nextHol['holiday_date'])) : '—' ?></small>
                </div>
                <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                    <i class="fa-solid fa-bell fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Holidays List Table -->
<div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden mb-4">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-list-check text-primary me-2"></i>Public Holidays Schedule (<?= date('Y') ?>)
        </h6>
        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><?= count($holidays) ?> Official Holidays</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Day</th>
                    <th>Holiday Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($holidays)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-calendar-xmark fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                            No public holidays added yet. Click <strong>Add Holiday</strong> above to add company holidays.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($holidays as $hol): 
                        $holTime = strtotime($hol['holiday_date']);
                        $isPast = $hol['holiday_date'] < date('Y-m-d');
                        $isToday = $hol['holiday_date'] === date('Y-m-d');
                    ?>
                        <tr class="<?= $isToday ? 'table-warning' : ($isPast ? 'opacity-75' : '') ?>">
                            <td class="ps-4 font-monospace fw-bold text-dark">
                                <i class="fa-regular fa-calendar text-primary me-2"></i>
                                <?= date('d M Y', $holTime) ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    <?= date('l', $holTime) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark"><?= e($hol['title']) ?></span>
                            </td>
                            <td class="text-muted small">
                                <?= e($hol['description'] ?: 'Official Public Holiday') ?>
                            </td>
                            <td>
                                <?php if ($isToday): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3"><i class="fa-solid fa-star me-1"></i> Today</span>
                                <?php elseif ($isPast): ?>
                                    <span class="badge bg-secondary rounded-pill px-3">Completed</span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill px-3">Upcoming</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 me-1" 
                                            onclick="openEditHolidayModal(<?= htmlspecialchars(json_encode($hol)) ?>)">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                    </button>
                                    <form action="<?= base_url('holidays/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $hol['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger rounded-pill px-2" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-calendar-plus text-primary me-2"></i>Add Public Holiday
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('holidays/create') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Holiday Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control bg-light" placeholder="e.g. Independence Day, Diwali, Eid" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Holiday Date <span class="text-danger">*</span></label>
                        <input type="date" name="holiday_date" class="form-control bg-light" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Description / Notes</label>
                        <input type="text" name="description" class="form-control bg-light" placeholder="e.g. National Holiday / Festival">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Save Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Holiday Modal -->
<div class="modal fade" id="editHolidayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Public Holiday
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('holidays/update') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editHolId">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Holiday Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="editHolTitle" class="form-control bg-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Holiday Date <span class="text-danger">*</span></label>
                        <input type="date" name="holiday_date" id="editHolDate" class="form-control bg-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Description / Notes</label>
                        <input type="text" name="description" id="editHolDesc" class="form-control bg-light">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditHolidayModal(hol) {
    document.getElementById('editHolId').value = hol.id;
    document.getElementById('editHolTitle').value = hol.title;
    document.getElementById('editHolDate').value = hol.holiday_date;
    document.getElementById('editHolDesc').value = hol.description || '';
    
    var modal = new bootstrap.Modal(document.getElementById('editHolidayModal'));
    modal.show();
}
</script>
