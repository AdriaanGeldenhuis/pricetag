<!-- Admin Products Import/Export -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">Import and export your product catalog</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/products') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Products
        </a>
    </div>
</div>

<div class="import-export-grid">
    <!-- Import Section -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Import Products
            </h3>
        </div>
        <div class="admin-card-body">
            <div class="import-info">
                <h4>Supported Formats</h4>
                <div class="format-badges">
                    <span class="format-badge">CSV</span>
                    <span class="format-badge">JSON</span>
                </div>

                <h4>CSV Columns</h4>
                <div class="columns-list">
                    <div class="column-item required">
                        <code>sku</code>
                        <span class="badge-required">Required</span>
                    </div>
                    <div class="column-item required">
                        <code>name</code>
                        <span class="badge-required">Required for new</span>
                    </div>
                    <div class="column-item">
                        <code>price</code>
                    </div>
                    <div class="column-item">
                        <code>compare_price</code>
                    </div>
                    <div class="column-item">
                        <code>cost_price</code>
                    </div>
                    <div class="column-item">
                        <code>stock</code>
                    </div>
                    <div class="column-item">
                        <code>category</code>
                        <span class="badge-info">Name or ID</span>
                    </div>
                    <div class="column-item">
                        <code>description</code>
                    </div>
                    <div class="column-item">
                        <code>short_description</code>
                    </div>
                    <div class="column-item">
                        <code>weight</code>
                    </div>
                    <div class="column-item">
                        <code>status</code>
                        <span class="badge-info">1 or 0</span>
                    </div>
                    <div class="column-item">
                        <code>featured</code>
                        <span class="badge-info">1 or 0</span>
                    </div>
                    <div class="column-item">
                        <code>image_url</code>
                        <span class="badge-info">URL</span>
                    </div>
                </div>

                <div class="template-download">
                    <a href="<?= url('/admin/products/import/template') ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Download CSV Template
                    </a>
                    <a href="<?= url('/admin/products/import/template?format=json') ?>" class="admin-btn admin-btn-secondary admin-btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Download JSON Template
                    </a>
                </div>
            </div>

            <!-- Drag & Drop Upload Area -->
            <form id="importForm" method="POST" action="<?= url('/admin/products/import') ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="drop-zone" id="dropZone">
                    <div class="drop-zone-content">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <h3>Drag & Drop your file here</h3>
                        <p>or click to browse</p>
                        <span class="drop-zone-formats">Supports CSV and JSON files</span>
                    </div>
                    <div class="drop-zone-file" id="dropZoneFile" style="display: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        <div class="file-info">
                            <span class="file-name" id="fileName"></span>
                            <span class="file-size" id="fileSize"></span>
                        </div>
                        <button type="button" class="remove-file" id="removeFile">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <input type="file" name="file" id="fileInput" accept=".csv,.json,text/csv,application/json" required>
                </div>

                <!-- Import Options -->
                <div class="import-options">
                    <div class="admin-form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="update_existing" value="1" checked>
                            <span class="checkbox-mark"></span>
                            Update existing products (match by SKU)
                        </label>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="create_new" value="1" checked>
                            <span class="checkbox-mark"></span>
                            Create new products if SKU not found
                        </label>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="skip_errors" value="1">
                            <span class="checkbox-mark"></span>
                            Skip rows with errors and continue import
                        </label>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="import-preview" id="importPreview" style="display: none;">
                    <div class="preview-header">
                        <h4>Preview (<span id="previewCount">0</span> products)</h4>
                        <div class="preview-stats">
                            <span class="stat-new" id="statNew">0 new</span>
                            <span class="stat-update" id="statUpdate">0 updates</span>
                            <span class="stat-error" id="statError">0 errors</span>
                        </div>
                    </div>
                    <div class="preview-table-wrapper">
                        <table class="admin-table preview-table">
                            <thead id="previewHead"></thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                    <div class="preview-errors" id="previewErrors" style="display: none;">
                        <h4>Validation Errors</h4>
                        <ul id="errorList"></ul>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="import-progress" id="importProgress" style="display: none;">
                    <div class="progress-info">
                        <span id="progressText">Importing products...</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="progress-details" id="progressDetails"></div>
                </div>

                <!-- Action Buttons -->
                <div class="import-actions">
                    <button type="button" class="admin-btn admin-btn-secondary" id="previewBtn" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Preview Import
                    </button>
                    <button type="submit" class="admin-btn admin-btn-primary" id="importBtn" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        Import Products
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Section -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Export Products
            </h3>
        </div>
        <div class="admin-card-body">
            <form method="GET" action="<?= url('/admin/products/export') ?>" id="exportForm">

                <!-- Export Format -->
                <div class="admin-form-group">
                    <label class="admin-form-label">Export Format</label>
                    <div class="export-formats">
                        <label class="format-option">
                            <input type="radio" name="format" value="csv" checked>
                            <span class="format-card">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <strong>CSV</strong>
                                <small>Spreadsheet compatible</small>
                            </span>
                        </label>
                        <label class="format-option">
                            <input type="radio" name="format" value="json">
                            <span class="format-card">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="16 18 22 12 16 6"></polyline>
                                    <polyline points="8 6 2 12 8 18"></polyline>
                                </svg>
                                <strong>JSON</strong>
                                <small>Developer friendly</small>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Filter by Categories -->
                <div class="admin-form-group">
                    <label class="admin-form-label">Categories</label>
                    <div class="category-select">
                        <div class="category-select-header" id="categorySelectHeader">
                            <span id="categorySelectText">All Categories</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                        <div class="category-dropdown" id="categoryDropdown">
                            <div class="category-search">
                                <input type="text" id="categorySearch" placeholder="Search categories...">
                            </div>
                            <div class="category-options" id="categoryOptions">
                                <label class="category-option">
                                    <input type="checkbox" name="all_categories" id="allCategories" checked>
                                    <span>All Categories</span>
                                </label>
                                <?php foreach ($categories as $cat): ?>
                                <label class="category-option" data-name="<?= strtolower(e($cat['name'])) ?>">
                                    <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>">
                                    <span><?= e($cat['name_display'] ?? $cat['name']) ?></span>
                                    <small class="category-count"><?= $cat['product_count'] ?? 0 ?></small>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter by Status -->
                <div class="admin-form-group">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="admin-form-select">
                        <option value="">All Status</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>

                <!-- Filter by Stock -->
                <div class="admin-form-group">
                    <label class="admin-form-label">Stock</label>
                    <select name="stock" class="admin-form-select">
                        <option value="">All Stock Levels</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>

                <!-- Filter by Featured -->
                <div class="admin-form-group">
                    <label class="admin-form-label">Featured</label>
                    <select name="featured" class="admin-form-select">
                        <option value="">All Products</option>
                        <option value="1">Featured Only</option>
                        <option value="0">Non-Featured Only</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="admin-form-group">
                    <label class="admin-form-label">Date Range</label>
                    <div class="date-range">
                        <input type="date" name="date_from" class="admin-form-input" placeholder="From">
                        <span>to</span>
                        <input type="date" name="date_to" class="admin-form-input" placeholder="To">
                    </div>
                </div>

                <!-- Select Fields to Export -->
                <div class="admin-form-group">
                    <label class="admin-form-label">Fields to Export</label>
                    <div class="export-fields">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="sku" checked disabled>
                            <span class="checkbox-mark"></span>
                            SKU (required)
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="name" checked>
                            <span class="checkbox-mark"></span>
                            Name
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="price" checked>
                            <span class="checkbox-mark"></span>
                            Price
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="compare_price">
                            <span class="checkbox-mark"></span>
                            Compare Price
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="cost_price">
                            <span class="checkbox-mark"></span>
                            Cost Price
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="stock" checked>
                            <span class="checkbox-mark"></span>
                            Stock
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="category" checked>
                            <span class="checkbox-mark"></span>
                            Category
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="description">
                            <span class="checkbox-mark"></span>
                            Description
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="short_description">
                            <span class="checkbox-mark"></span>
                            Short Description
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="weight">
                            <span class="checkbox-mark"></span>
                            Weight
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="status" checked>
                            <span class="checkbox-mark"></span>
                            Status
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="featured">
                            <span class="checkbox-mark"></span>
                            Featured
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="image_url">
                            <span class="checkbox-mark"></span>
                            Image URL
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="slug">
                            <span class="checkbox-mark"></span>
                            Slug
                        </label>
                        <label class="admin-checkbox">
                            <input type="checkbox" name="fields[]" value="created_at">
                            <span class="checkbox-mark"></span>
                            Created Date
                        </label>
                    </div>
                </div>

                <!-- Export Summary -->
                <div class="export-summary">
                    <div class="summary-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                    <div class="summary-info">
                        <span class="summary-count"><?= number_format($totalProducts) ?></span>
                        <span class="summary-label">products will be exported</span>
                    </div>
                </div>

                <!-- Export Button -->
                <div class="export-actions">
                    <button type="submit" class="admin-btn admin-btn-primary admin-btn-block">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Export Products
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Recent Import/Export History -->
<?php if (!empty($history)): ?>
<div class="admin-card" style="margin-top: 1.5rem;">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Recent Import/Export History</h3>
    </div>
    <div class="admin-card-body" style="padding: 0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>File</th>
                    <th>Status</th>
                    <th>Products</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $log): ?>
                <tr>
                    <td class="text-muted"><?= date('M j, g:i A', strtotime($log['created_at'])) ?></td>
                    <td>
                        <span class="admin-badge <?= $log['type'] === 'import' ? 'info' : 'neutral' ?>">
                            <?= ucfirst($log['type']) ?>
                        </span>
                    </td>
                    <td><?= e($log['filename'] ?? '-') ?></td>
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
                        <?php if ($log['type'] === 'import'): ?>
                        <span class="text-success"><?= $log['created_products'] ?? 0 ?> new</span>,
                        <span class="text-primary"><?= $log['updated_products'] ?? 0 ?> updated</span>
                        <?php if (!empty($log['failed_products'])): ?>
                        , <span class="text-danger"><?= $log['failed_products'] ?> failed</span>
                        <?php endif; ?>
                        <?php else: ?>
                        <?= number_format($log['total_products'] ?? 0) ?> exported
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($log['type'] === 'export' && !empty($log['download_url'])): ?>
                        <a href="<?= e($log['download_url']) ?>" class="admin-btn admin-btn-ghost admin-btn-sm">
                            Download
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
.import-export-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 1.5rem;
}

.admin-card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Import Info */
.import-info {
    background: var(--admin-bg);
    padding: 1rem;
    border-radius: var(--admin-radius);
    margin-bottom: 1.5rem;
}

.import-info h4 {
    margin: 0 0 0.5rem;
    font-size: 0.8125rem;
    text-transform: uppercase;
    color: var(--admin-text-muted);
    letter-spacing: 0.05em;
}

.import-info h4:not(:first-child) {
    margin-top: 1rem;
}

.format-badges {
    display: flex;
    gap: 0.5rem;
}

.format-badge {
    background: var(--admin-primary);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.columns-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.5rem;
}

.column-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.column-item code {
    background: var(--admin-card-bg);
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8125rem;
}

.badge-required {
    background: var(--admin-danger);
    color: white;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-size: 0.625rem;
    text-transform: uppercase;
}

.badge-info {
    background: var(--admin-primary);
    color: white;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-size: 0.625rem;
}

.template-download {
    margin-top: 1rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Drop Zone */
.drop-zone {
    border: 2px dashed var(--admin-border);
    border-radius: var(--admin-radius);
    padding: 3rem 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    background: var(--admin-bg);
}

.drop-zone:hover,
.drop-zone.dragover {
    border-color: var(--admin-primary);
    background: rgba(99, 102, 241, 0.05);
}

.drop-zone.dragover {
    transform: scale(1.01);
}

.drop-zone.has-file {
    border-style: solid;
    border-color: var(--admin-success);
    background: rgba(16, 185, 129, 0.05);
}

.drop-zone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.drop-zone-content svg {
    width: 48px;
    height: 48px;
    color: var(--admin-text-muted);
    margin-bottom: 1rem;
}

.drop-zone-content h3 {
    margin: 0 0 0.25rem;
    font-size: 1.125rem;
}

.drop-zone-content p {
    margin: 0;
    color: var(--admin-text-muted);
}

.drop-zone-formats {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: var(--admin-text-muted);
}

.drop-zone-file {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.drop-zone-file svg {
    width: 40px;
    height: 40px;
    color: var(--admin-success);
}

.file-info {
    text-align: left;
}

.file-name {
    display: block;
    font-weight: 600;
    color: var(--admin-text);
}

.file-size {
    display: block;
    font-size: 0.8125rem;
    color: var(--admin-text-muted);
}

.remove-file {
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    color: var(--admin-text-muted);
    transition: color 0.2s;
}

.remove-file:hover {
    color: var(--admin-danger);
}

.remove-file svg {
    width: 20px;
    height: 20px;
}

/* Import Options */
.import-options {
    margin: 1.5rem 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.admin-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.admin-checkbox input {
    display: none;
}

.checkbox-mark {
    width: 18px;
    height: 18px;
    border: 2px solid var(--admin-border);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.admin-checkbox input:checked + .checkbox-mark {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
}

.admin-checkbox input:checked + .checkbox-mark::after {
    content: '';
    width: 5px;
    height: 9px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg) translate(-1px, -1px);
}

/* Preview Area */
.import-preview {
    margin: 1.5rem 0;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    overflow: hidden;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--admin-bg);
    border-bottom: 1px solid var(--admin-border);
}

.preview-header h4 {
    margin: 0;
    font-size: 0.875rem;
}

.preview-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.8125rem;
}

.stat-new {
    color: var(--admin-success);
}

.stat-update {
    color: var(--admin-primary);
}

.stat-error {
    color: var(--admin-danger);
}

.preview-table-wrapper {
    max-height: 300px;
    overflow: auto;
}

.preview-table {
    font-size: 0.8125rem;
}

.preview-table th,
.preview-table td {
    padding: 0.5rem 0.75rem;
    white-space: nowrap;
}

.preview-row-new {
    background: rgba(16, 185, 129, 0.1);
}

.preview-row-update {
    background: rgba(99, 102, 241, 0.1);
}

.preview-row-error {
    background: rgba(239, 68, 68, 0.1);
}

.preview-errors {
    padding: 1rem;
    background: rgba(239, 68, 68, 0.1);
    border-top: 1px solid var(--admin-danger);
}

.preview-errors h4 {
    margin: 0 0 0.5rem;
    color: var(--admin-danger);
    font-size: 0.875rem;
}

.preview-errors ul {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.8125rem;
    color: var(--admin-danger);
}

/* Progress Bar */
.import-progress {
    margin: 1.5rem 0;
    padding: 1.5rem;
    background: var(--admin-bg);
    border-radius: var(--admin-radius);
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.progress-bar {
    height: 8px;
    background: var(--admin-border);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--admin-primary);
    width: 0%;
    transition: width 0.3s ease;
}

.progress-details {
    margin-top: 0.75rem;
    font-size: 0.8125rem;
    color: var(--admin-text-muted);
}

/* Import Actions */
.import-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.import-actions .admin-btn {
    flex: 1;
}

/* Export Formats */
.export-formats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.format-option {
    cursor: pointer;
}

.format-option input {
    display: none;
}

.format-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    border: 2px solid var(--admin-border);
    border-radius: var(--admin-radius);
    transition: all 0.2s;
    text-align: center;
}

.format-option input:checked + .format-card {
    border-color: var(--admin-primary);
    background: rgba(99, 102, 241, 0.05);
}

.format-card svg {
    width: 32px;
    height: 32px;
    color: var(--admin-text-muted);
    margin-bottom: 0.5rem;
}

.format-option input:checked + .format-card svg {
    color: var(--admin-primary);
}

.format-card strong {
    display: block;
    margin-bottom: 0.25rem;
}

.format-card small {
    color: var(--admin-text-muted);
    font-size: 0.75rem;
}

/* Category Select */
.category-select {
    position: relative;
}

.category-select-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    background: var(--admin-card-bg);
    cursor: pointer;
    transition: border-color 0.2s;
}

.category-select-header:hover {
    border-color: var(--admin-primary);
}

.category-select-header svg {
    width: 16px;
    height: 16px;
    color: var(--admin-text-muted);
    transition: transform 0.2s;
}

.category-select.open .category-select-header svg {
    transform: rotate(180deg);
}

.category-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: var(--admin-card-bg);
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 100;
    display: none;
    max-height: 300px;
    overflow: hidden;
    flex-direction: column;
}

.category-select.open .category-dropdown {
    display: flex;
}

.category-search {
    padding: 0.5rem;
    border-bottom: 1px solid var(--admin-border);
}

.category-search input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius-sm);
    font-size: 0.875rem;
}

.category-options {
    overflow-y: auto;
    flex: 1;
}

.category-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: background 0.2s;
}

.category-option:hover {
    background: var(--admin-bg);
}

.category-option input {
    width: 16px;
    height: 16px;
}

.category-option span {
    flex: 1;
    font-size: 0.875rem;
}

.category-count {
    color: var(--admin-text-muted);
    font-size: 0.75rem;
}

/* Date Range */
.date-range {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.date-range input {
    flex: 1;
}

.date-range span {
    color: var(--admin-text-muted);
    font-size: 0.875rem;
}

/* Export Fields */
.export-fields {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 0.5rem;
}

/* Export Summary */
.export-summary {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--admin-bg);
    border-radius: var(--admin-radius);
    margin: 1.5rem 0;
}

.summary-icon {
    width: 48px;
    height: 48px;
    background: var(--admin-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.summary-icon svg {
    width: 24px;
    height: 24px;
    color: white;
}

.summary-count {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--admin-text);
}

.summary-label {
    color: var(--admin-text-muted);
    font-size: 0.875rem;
}

/* Export Actions */
.export-actions {
    margin-top: 1rem;
}

.admin-btn-block {
    width: 100%;
    justify-content: center;
}

/* Helper Classes */
.text-success { color: var(--admin-success); }
.text-primary { color: var(--admin-primary); }
.text-danger { color: var(--admin-danger); }

/* Responsive */
@media (max-width: 992px) {
    .import-export-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .columns-list {
        grid-template-columns: 1fr 1fr;
    }

    .export-formats {
        grid-template-columns: 1fr;
    }

    .export-fields {
        grid-template-columns: 1fr;
    }

    .import-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Drag and Drop functionality
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const dropZoneContent = dropZone.querySelector('.drop-zone-content');
    const dropZoneFile = document.getElementById('dropZoneFile');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    const previewBtn = document.getElementById('previewBtn');
    const importBtn = document.getElementById('importBtn');
    const importPreview = document.getElementById('importPreview');
    const importForm = document.getElementById('importForm');

    let currentFile = null;
    let parsedData = null;

    // Prevent defaults
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop zone
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
    });

    // Handle drop
    dropZone.addEventListener('drop', handleDrop, false);
    fileInput.addEventListener('change', handleFileSelect, false);

    function handleDrop(e) {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    }

    function handleFileSelect(e) {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    }

    function handleFile(file) {
        const validTypes = ['text/csv', 'application/json', 'application/vnd.ms-excel'];
        const validExtensions = ['.csv', '.json'];
        const ext = '.' + file.name.split('.').pop().toLowerCase();

        if (!validTypes.includes(file.type) && !validExtensions.includes(ext)) {
            alert('Please upload a CSV or JSON file');
            return;
        }

        currentFile = file;
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);

        dropZoneContent.style.display = 'none';
        dropZoneFile.style.display = 'flex';
        dropZone.classList.add('has-file');

        previewBtn.disabled = false;
        importBtn.disabled = false;

        // Auto-preview small files
        if (file.size < 1024 * 1024) { // < 1MB
            previewFile(file);
        }
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    // Remove file
    removeFile.addEventListener('click', function(e) {
        e.stopPropagation();
        resetDropZone();
    });

    function resetDropZone() {
        currentFile = null;
        parsedData = null;
        fileInput.value = '';
        fileName.textContent = '';
        fileSize.textContent = '';
        dropZoneContent.style.display = 'block';
        dropZoneFile.style.display = 'none';
        dropZone.classList.remove('has-file');
        previewBtn.disabled = true;
        importBtn.disabled = true;
        importPreview.style.display = 'none';
    }

    // Preview button click
    previewBtn.addEventListener('click', function() {
        if (currentFile) {
            previewFile(currentFile);
        }
    });

    function previewFile(file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            const content = e.target.result;
            const ext = file.name.split('.').pop().toLowerCase();

            try {
                if (ext === 'json') {
                    parsedData = JSON.parse(content);
                    if (!Array.isArray(parsedData)) {
                        parsedData = parsedData.products || [parsedData];
                    }
                } else {
                    parsedData = parseCSV(content);
                }

                displayPreview(parsedData);
            } catch (err) {
                alert('Error parsing file: ' + err.message);
            }
        };

        reader.readAsText(file);
    }

    function parseCSV(content) {
        const lines = content.split(/\r?\n/).filter(line => line.trim());
        if (lines.length < 2) return [];

        const headers = parseCSVLine(lines[0]).map(h => h.toLowerCase().trim());
        const data = [];

        for (let i = 1; i < lines.length; i++) {
            const values = parseCSVLine(lines[i]);
            const row = {};
            headers.forEach((header, index) => {
                row[header] = values[index] || '';
            });
            data.push(row);
        }

        return data;
    }

    function parseCSVLine(line) {
        const result = [];
        let current = '';
        let inQuotes = false;

        for (let i = 0; i < line.length; i++) {
            const char = line[i];

            if (char === '"') {
                if (inQuotes && line[i + 1] === '"') {
                    current += '"';
                    i++;
                } else {
                    inQuotes = !inQuotes;
                }
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }

        result.push(current.trim());
        return result;
    }

    function displayPreview(data) {
        if (!data || data.length === 0) {
            alert('No data found in file');
            return;
        }

        const previewHead = document.getElementById('previewHead');
        const previewBody = document.getElementById('previewBody');
        const previewCount = document.getElementById('previewCount');
        const statNew = document.getElementById('statNew');
        const statUpdate = document.getElementById('statUpdate');
        const statError = document.getElementById('statError');
        const previewErrors = document.getElementById('previewErrors');
        const errorList = document.getElementById('errorList');

        // Get headers
        const headers = Object.keys(data[0]);

        // Build header row
        previewHead.innerHTML = '<tr>' + headers.map(h => `<th>${escapeHtml(h)}</th>`).join('') + '<th>Status</th></tr>';

        // Validate and build body
        let newCount = 0;
        let updateCount = 0;
        let errorCount = 0;
        const errors = [];

        const bodyHtml = data.slice(0, 50).map((row, index) => {
            const validation = validateRow(row, index + 2);
            let rowClass = '';
            let status = '';

            if (validation.errors.length > 0) {
                rowClass = 'preview-row-error';
                status = '<span class="text-danger">Error</span>';
                errorCount++;
                errors.push(...validation.errors);
            } else if (validation.isNew) {
                rowClass = 'preview-row-new';
                status = '<span class="text-success">New</span>';
                newCount++;
            } else {
                rowClass = 'preview-row-update';
                status = '<span class="text-primary">Update</span>';
                updateCount++;
            }

            const cells = headers.map(h => `<td>${escapeHtml(String(row[h] || '').substring(0, 50))}</td>`).join('');
            return `<tr class="${rowClass}">${cells}<td>${status}</td></tr>`;
        }).join('');

        previewBody.innerHTML = bodyHtml;
        previewCount.textContent = data.length;
        statNew.textContent = newCount + ' new';
        statUpdate.textContent = updateCount + ' updates';
        statError.textContent = errorCount + ' errors';

        if (errors.length > 0) {
            errorList.innerHTML = errors.slice(0, 10).map(e => `<li>${escapeHtml(e)}</li>`).join('');
            previewErrors.style.display = 'block';
        } else {
            previewErrors.style.display = 'none';
        }

        importPreview.style.display = 'block';
    }

    function validateRow(row, rowNum) {
        const errors = [];
        let isNew = true; // Assume new unless we check against existing SKUs

        // Required fields
        if (!row.sku || String(row.sku).trim() === '') {
            errors.push(`Row ${rowNum}: SKU is required`);
        }

        // For new products, name is required
        if (!row.name || String(row.name).trim() === '') {
            // This might be an update, so it's not always an error
            // We'll mark it as potentially new requiring name
        }

        // Validate numeric fields
        if (row.price && isNaN(parseFloat(row.price))) {
            errors.push(`Row ${rowNum}: Invalid price value`);
        }

        if (row.stock && isNaN(parseInt(row.stock))) {
            errors.push(`Row ${rowNum}: Invalid stock value`);
        }

        return { errors, isNew };
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Form submission with progress
    importForm.addEventListener('submit', function(e) {
        if (!currentFile) {
            e.preventDefault();
            alert('Please select a file to import');
            return;
        }

        // Show progress
        document.getElementById('importProgress').style.display = 'block';
        importBtn.disabled = true;
        previewBtn.disabled = true;

        // Simulate progress for small files
        let progress = 0;
        const progressFill = document.getElementById('progressFill');
        const progressPercent = document.getElementById('progressPercent');

        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressFill.style.width = progress + '%';
            progressPercent.textContent = Math.round(progress) + '%';
        }, 200);

        // Clean up on page unload
        window.addEventListener('beforeunload', () => clearInterval(interval));
    });

    // Category Select Dropdown
    const categorySelect = document.querySelector('.category-select');
    const categorySelectHeader = document.getElementById('categorySelectHeader');
    const categoryDropdown = document.getElementById('categoryDropdown');
    const categorySearch = document.getElementById('categorySearch');
    const categoryOptions = document.getElementById('categoryOptions');
    const allCategories = document.getElementById('allCategories');
    const categorySelectText = document.getElementById('categorySelectText');

    if (categorySelectHeader) {
        categorySelectHeader.addEventListener('click', function() {
            categorySelect.classList.toggle('open');
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!categorySelect.contains(e.target)) {
                categorySelect.classList.remove('open');
            }
        });

        // Search categories
        categorySearch.addEventListener('input', function() {
            const search = this.value.toLowerCase();
            const options = categoryOptions.querySelectorAll('.category-option[data-name]');

            options.forEach(option => {
                const name = option.dataset.name;
                option.style.display = name.includes(search) ? '' : 'none';
            });
        });

        // Handle "All Categories" checkbox
        allCategories.addEventListener('change', function() {
            const categoryCheckboxes = categoryOptions.querySelectorAll('input[name="categories[]"]');

            if (this.checked) {
                categoryCheckboxes.forEach(cb => cb.checked = false);
                categorySelectText.textContent = 'All Categories';
            }
        });

        // Handle individual category checkboxes
        categoryOptions.querySelectorAll('input[name="categories[]"]').forEach(cb => {
            cb.addEventListener('change', function() {
                const checked = categoryOptions.querySelectorAll('input[name="categories[]"]:checked');

                if (checked.length > 0) {
                    allCategories.checked = false;
                    if (checked.length === 1) {
                        categorySelectText.textContent = checked[0].closest('.category-option').querySelector('span').textContent;
                    } else {
                        categorySelectText.textContent = checked.length + ' categories selected';
                    }
                } else {
                    allCategories.checked = true;
                    categorySelectText.textContent = 'All Categories';
                }
            });
        });
    }
});
</script>
