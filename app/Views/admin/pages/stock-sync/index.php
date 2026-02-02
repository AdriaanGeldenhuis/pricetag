<!-- Admin Stock Sync Dashboard -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">Manage stock synchronization and imports</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/stock-sync/template') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Download Template
        </a>
        <button type="button" class="admin-btn admin-btn-primary" onclick="showImportModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            Import CSV
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
        </div>
        <div class="admin-stat-content">
            <span class="admin-stat-value"><?= number_format($stats['total_syncs']) ?></span>
            <span class="admin-stat-label">Total Syncs</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <div class="admin-stat-content">
            <span class="admin-stat-value"><?= number_format($stats['successful']) ?></span>
            <span class="admin-stat-label">Successful</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon red">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <div class="admin-stat-content">
            <span class="admin-stat-value"><?= number_format($stats['failed']) ?></span>
            <span class="admin-stat-label">Failed</span>
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
        </div>
        <div class="admin-stat-content">
            <span class="admin-stat-value"><?= number_format($stats['products_updated'] ?? 0) ?></span>
            <span class="admin-stat-label">Products Updated</span>
        </div>
    </div>
</div>

<!-- Stock Alerts -->
<?php if ($lowStock > 0 || $outOfStock > 0): ?>
<div class="admin-alerts" style="margin-bottom: 1.5rem;">
    <?php if ($outOfStock > 0): ?>
    <div class="admin-alert admin-alert-danger">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div class="admin-alert-content">
            <strong><?= $outOfStock ?> products</strong> are out of stock
        </div>
        <a href="<?= url('/admin/products?stock=out') ?>" class="admin-btn admin-btn-sm admin-btn-danger">View Products</a>
    </div>
    <?php endif; ?>

    <?php if ($lowStock > 0): ?>
    <div class="admin-alert admin-alert-warning">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <div class="admin-alert-content">
            <strong><?= $lowStock ?> products</strong> have low stock
        </div>
        <a href="<?= url('/admin/products?stock=low') ?>" class="admin-btn admin-btn-sm admin-btn-warning">View Products</a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="sync-grid">
    <!-- Vendor Sync -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Vendor API Sync</h3>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <?php if (empty($vendors)): ?>
            <div class="admin-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
                <h3 class="admin-empty-title">No vendors configured</h3>
                <p class="admin-empty-text">Add vendors with API sync enabled to sync stock automatically.</p>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Products</th>
                        <th>Last Sync</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vendors as $vendor): ?>
                    <tr>
                        <td>
                            <strong><?= e($vendor['name']) ?></strong>
                        </td>
                        <td><?= number_format($vendor['product_count']) ?></td>
                        <td>
                            <?php if ($vendor['last_success']): ?>
                            <span class="text-muted"><?= date('M j, g:i A', strtotime($vendor['last_success'])) ?></span>
                            <?php else: ?>
                            <span class="text-muted">Never</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="<?= url('/admin/stock-sync/run') ?>" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="vendor_id" value="<?= $vendor['id'] ?>">
                                <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">
                                    Sync Now
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Recent Sync Logs</h3>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <?php if (empty($recentLogs)): ?>
            <div class="admin-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <h3 class="admin-empty-title">No sync logs</h3>
                <p class="admin-empty-text">Sync logs will appear here after you run a sync.</p>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Products</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td class="text-muted">
                            <?= date('M j, g:i A', strtotime($log['created_at'])) ?>
                        </td>
                        <td>
                            <span class="admin-badge neutral"><?= ucfirst($log['type']) ?></span>
                        </td>
                        <td><?= e($log['vendor_name'] ?? 'Manual Import') ?></td>
                        <td>
                            <?php if ($log['status'] === 'completed'): ?>
                            <span class="admin-badge active">Completed</span>
                            <?php elseif ($log['status'] === 'running'): ?>
                            <span class="admin-badge warning">Running</span>
                            <?php else: ?>
                            <span class="admin-badge inactive">Failed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($log['status'] === 'completed'): ?>
                            <span class="text-success"><?= $log['updated_products'] ?? 0 ?></span>
                            <?php if (!empty($log['created_products'])): ?>
                            / <span class="text-primary"><?= $log['created_products'] ?> new</span>
                            <?php endif; ?>
                            <?php if (!empty($log['failed_products'])): ?>
                            / <span class="text-danger"><?= $log['failed_products'] ?> failed</span>
                            <?php endif; ?>
                            <?php else: ?>
                            -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= url('/admin/stock-sync/log/' . $log['id']) ?>"
                               class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="View Details">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="admin-modal-overlay" id="importModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Import Stock from CSV</h3>
            <button class="admin-modal-close" onclick="closeImportModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" action="<?= url('/admin/stock-sync/import') ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="admin-modal-body">
                <div class="import-instructions">
                    <h4>CSV Format Requirements</h4>
                    <ul>
                        <li><strong>Required column:</strong> <code>sku</code></li>
                        <li><strong>Optional columns:</strong> <code>stock</code> or <code>quantity</code>, <code>price</code>, <code>name</code></li>
                        <li>If a product with the SKU doesn't exist and <code>name</code> is provided, a new product will be created</li>
                    </ul>
                    <p><a href="<?= url('/admin/stock-sync/template') ?>">Download sample template</a></p>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label required">CSV File</label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <p>Drop your CSV file here or <span class="text-primary">browse</span></p>
                        <p class="text-muted" id="selectedFileName"></p>
                        <input type="file" name="file" id="csvFile" accept=".csv,text/csv" required>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeImportModal()">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Import
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.sync-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.admin-alerts {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.admin-alert {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-radius: var(--admin-radius);
    background: var(--admin-card-bg);
    border: 1px solid var(--admin-border);
}

.admin-alert svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.admin-alert-danger {
    border-color: var(--admin-danger);
    background: rgba(239, 68, 68, 0.1);
}

.admin-alert-danger svg {
    color: var(--admin-danger);
}

.admin-alert-warning {
    border-color: var(--admin-warning);
    background: rgba(245, 158, 11, 0.1);
}

.admin-alert-warning svg {
    color: var(--admin-warning);
}

.admin-alert-content {
    flex: 1;
}

.import-instructions {
    background: var(--admin-bg);
    padding: 1rem;
    border-radius: var(--admin-radius);
    margin-bottom: 1.5rem;
}

.import-instructions h4 {
    margin: 0 0 0.5rem;
    font-size: 0.875rem;
}

.import-instructions ul {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.875rem;
}

.import-instructions li {
    margin-bottom: 0.25rem;
}

.import-instructions code {
    background: var(--admin-card-bg);
    padding: 0.125rem 0.375rem;
    border-radius: 3px;
    font-size: 0.8125rem;
}

.import-instructions p {
    margin: 0.75rem 0 0;
    font-size: 0.875rem;
}

.file-upload-area {
    border: 2px dashed var(--admin-border);
    border-radius: var(--admin-radius);
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    position: relative;
}

.file-upload-area:hover {
    border-color: var(--admin-primary);
    background: var(--admin-primary-light);
}

.file-upload-area svg {
    width: 48px;
    height: 48px;
    color: var(--admin-text-muted);
    margin-bottom: 0.5rem;
}

.file-upload-area p {
    margin: 0.5rem 0 0;
}

.file-upload-area input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.text-success {
    color: var(--admin-success);
}

.text-danger {
    color: var(--admin-danger);
}

.text-primary {
    color: var(--admin-primary);
}

@media (max-width: 768px) {
    .sync-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function showImportModal() {
    document.getElementById('importModal').classList.add('show');
}

function closeImportModal() {
    document.getElementById('importModal').classList.remove('show');
}

document.getElementById('importModal').addEventListener('click', function(e) {
    if (e.target === this) closeImportModal();
});

// File upload feedback
document.getElementById('csvFile').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    document.getElementById('selectedFileName').textContent = fileName || '';
});

// Drag and drop
const dropArea = document.getElementById('fileUploadArea');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropArea.style.borderColor = 'var(--admin-primary)';
    dropArea.style.background = 'var(--admin-primary-light)';
}

function unhighlight(e) {
    dropArea.style.borderColor = '';
    dropArea.style.background = '';
}

dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    document.getElementById('csvFile').files = files;
    document.getElementById('selectedFileName').textContent = files[0]?.name || '';
}
</script>
