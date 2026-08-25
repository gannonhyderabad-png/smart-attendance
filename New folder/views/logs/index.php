<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-shield-halved text-primary me-2"></i>System & Audit Logs</h5>
                <p class="text-muted small mb-0">Chronological trail of administrative actions and security events</p>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2">Total Logs: <?= number_format($total) ?></span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th class="pe-3 text-end">IP Address</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No activity logs recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="ps-3 font-monospace text-muted"><?= date('d M Y, h:i:s A', strtotime($log['created_at'])) ?></td>
                                <td>
                                    <?php if ($log['user_name']): ?>
                                        <span class="fw-semibold text-dark"><?= e($log['user_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">System / Public</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary font-monospace"><?= e($log['action']) ?></span>
                                </td>
                                <td class="text-dark"><?= e($log['description'] ?? '—') ?></td>
                                <td class="pe-3 text-end"><code><?= e($log['ip_address'] ?? '127.0.0.1') ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <span class="text-muted small">Showing page <?= $page ?> of <?= $totalPages ?></span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= ($page == $p) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= base_url('logs?page=' . $p) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </div>
</div>
