<!-- Admin Products Import/Export with Drag & Drop Column Mapping + AI -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">Import products with intelligent column mapping and AI-powered content generation</p>
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

<div class="import-export-container">
    <!-- Step 1: Upload File -->
    <div class="admin-card step-card" id="step1">
        <div class="step-header">
            <div class="step-number">1</div>
            <div class="step-info">
                <h3>Upload Your File</h3>
                <p>Drag & drop your CSV or JSON file, or click to browse</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="drop-zone" id="dropZone">
                <div class="drop-zone-content" id="dropZoneContent">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <h3>Drag & Drop your file here</h3>
                    <p>or <span class="browse-link">click to browse</span></p>
                    <span class="supported-formats">Supports CSV and JSON files</span>
                </div>
                <div class="drop-zone-file" id="dropZoneFile" style="display: none;">
                    <div class="file-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <div class="file-details">
                        <span class="file-name" id="fileName"></span>
                        <span class="file-meta" id="fileMeta"></span>
                    </div>
                    <button type="button" class="remove-file-btn" id="removeFile">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <input type="file" id="fileInput" accept=".csv,.json" style="display: none;">
            </div>

            <div class="template-links">
                <a href="<?= url('/admin/products/import/template') ?>" class="template-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download CSV Template
                </a>
                <a href="<?= url('/admin/products/import/template?format=json') ?>" class="template-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download JSON Template
                </a>
            </div>
        </div>
    </div>

    <!-- Step 2: Map Columns -->
    <div class="admin-card step-card disabled" id="step2">
        <div class="step-header">
            <div class="step-number">2</div>
            <div class="step-info">
                <h3>Map Your Columns</h3>
                <p>Drag columns from your file to the product fields, or use AI to auto-detect</p>
            </div>
            <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" id="autoMapBtn" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4"></path>
                    <path d="M12 8h.01"></path>
                </svg>
                Auto-Detect Mapping
            </button>
        </div>
        <div class="admin-card-body">
            <div class="mapping-container">
                <!-- Source Columns (from file) -->
                <div class="mapping-source">
                    <h4>Your File Columns</h4>
                    <p class="mapping-hint">Drag these to the matching product field</p>
                    <div class="source-columns" id="sourceColumns">
                        <!-- Will be populated by JS -->
                    </div>
                </div>

                <!-- Target Fields (product fields) -->
                <div class="mapping-target">
                    <h4>Product Fields</h4>
                    <div class="target-fields" id="targetFields">
                        <div class="target-field required" data-field="sku">
                            <div class="field-label">
                                <span>SKU</span>
                                <span class="required-badge">Required</span>
                            </div>
                            <div class="field-dropzone" data-field="sku">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field required" data-field="name">
                            <div class="field-label">
                                <span>Product Name</span>
                                <span class="required-badge">Required</span>
                                <button type="button" class="ai-btn" data-field="name" title="AI Generate">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                    </svg>
                                    AI
                                </button>
                            </div>
                            <div class="field-dropzone" data-field="name">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="price">
                            <div class="field-label">
                                <span>Price</span>
                            </div>
                            <div class="field-dropzone" data-field="price">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="compare_price">
                            <div class="field-label">
                                <span>Compare Price</span>
                            </div>
                            <div class="field-dropzone" data-field="compare_price">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="cost_price">
                            <div class="field-label">
                                <span>Cost Price</span>
                            </div>
                            <div class="field-dropzone" data-field="cost_price">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="stock">
                            <div class="field-label">
                                <span>Stock Quantity</span>
                            </div>
                            <div class="field-dropzone" data-field="stock">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="category">
                            <div class="field-label">
                                <span>Category</span>
                            </div>
                            <div class="field-dropzone" data-field="category">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="description">
                            <div class="field-label">
                                <span>Description</span>
                                <button type="button" class="ai-btn" data-field="description" title="AI Generate">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                    </svg>
                                    AI
                                </button>
                            </div>
                            <div class="field-dropzone" data-field="description">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="short_description">
                            <div class="field-label">
                                <span>Short Description</span>
                                <button type="button" class="ai-btn" data-field="short_description" title="AI Generate">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                    </svg>
                                    AI
                                </button>
                            </div>
                            <div class="field-dropzone" data-field="short_description">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="weight">
                            <div class="field-label">
                                <span>Weight (kg)</span>
                            </div>
                            <div class="field-dropzone" data-field="weight">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="status">
                            <div class="field-label">
                                <span>Status</span>
                            </div>
                            <div class="field-dropzone" data-field="status">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                        <div class="target-field" data-field="image_url">
                            <div class="field-label">
                                <span>Image URL</span>
                            </div>
                            <div class="field-dropzone" data-field="image_url">
                                <span class="placeholder">Drop column here</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Preview & Import -->
    <div class="admin-card step-card disabled" id="step3">
        <div class="step-header">
            <div class="step-number">3</div>
            <div class="step-info">
                <h3>Preview & Import</h3>
                <p>Review your data before importing</p>
            </div>
        </div>
        <div class="admin-card-body">
            <!-- Import Options -->
            <div class="import-options">
                <label class="option-checkbox">
                    <input type="checkbox" id="updateExisting" checked>
                    <span class="checkmark"></span>
                    Update existing products (match by SKU)
                </label>
                <label class="option-checkbox">
                    <input type="checkbox" id="createNew" checked>
                    <span class="checkmark"></span>
                    Create new products if SKU not found
                </label>
                <label class="option-checkbox">
                    <input type="checkbox" id="skipErrors">
                    <span class="checkmark"></span>
                    Skip rows with errors and continue
                </label>
                <label class="option-checkbox">
                    <input type="checkbox" id="aiGenerateAll">
                    <span class="checkmark"></span>
                    <strong>AI Generate from SKU</strong> - Replace names with proper product names (e.g., BX8071514600K → Intel Core i5-14600K)
                </label>
            </div>

            <!-- Preview Stats -->
            <div class="preview-stats" id="previewStats" style="display: none;">
                <div class="stat-item stat-total">
                    <span class="stat-value" id="statTotal">0</span>
                    <span class="stat-label">Total Rows</span>
                </div>
                <div class="stat-item stat-new">
                    <span class="stat-value" id="statNew">0</span>
                    <span class="stat-label">New Products</span>
                </div>
                <div class="stat-item stat-update">
                    <span class="stat-value" id="statUpdate">0</span>
                    <span class="stat-label">Updates</span>
                </div>
                <div class="stat-item stat-error">
                    <span class="stat-value" id="statErrors">0</span>
                    <span class="stat-label">Errors</span>
                </div>
            </div>

            <!-- Preview Table -->
            <div class="preview-table-container" id="previewContainer" style="display: none;">
                <table class="admin-table preview-table">
                    <thead id="previewHead"></thead>
                    <tbody id="previewBody"></tbody>
                </table>
            </div>

            <!-- Error List -->
            <div class="error-list" id="errorList" style="display: none;">
                <h4>Validation Errors</h4>
                <ul id="errorItems"></ul>
            </div>

            <!-- Progress Bar -->
            <div class="import-progress" id="importProgress" style="display: none;">
                <div class="progress-header">
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
                    Preview Data
                </button>
                <button type="button" class="admin-btn admin-btn-primary" id="importBtn" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Import Products
                </button>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="admin-card" style="margin-top: 2rem;">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Export Products
            </h3>
        </div>
        <div class="admin-card-body">
            <form method="GET" action="<?= url('/admin/products/export') ?>" class="export-form">
                <div class="export-grid">
                    <div class="export-filters">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Format</label>
                            <div class="format-selector">
                                <label class="format-option">
                                    <input type="radio" name="format" value="csv" checked>
                                    <span>CSV</span>
                                </label>
                                <label class="format-option">
                                    <input type="radio" name="format" value="json">
                                    <span>JSON</span>
                                </label>
                            </div>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Category</label>
                            <select name="categories[]" class="admin-form-select" multiple>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-hint">Hold Ctrl/Cmd to select multiple</small>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Status</label>
                            <select name="status" class="admin-form-select">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Stock</label>
                            <select name="stock" class="admin-form-select">
                                <option value="">All</option>
                                <option value="in_stock">In Stock</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                    </div>
                    <div class="export-actions-panel">
                        <div class="export-count">
                            <span class="count-value"><?= number_format($totalProducts) ?></span>
                            <span class="count-label">products to export</span>
                        </div>
                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-lg">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Export Products
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AI Generation Modal -->
<div class="admin-modal-overlay" id="aiModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">AI Content Generation</h3>
            <button class="admin-modal-close" onclick="closeAiModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p id="aiModalText">AI will generate content for products missing this field.</p>
            <div class="ai-options">
                <label class="option-checkbox">
                    <input type="checkbox" id="aiOnlyEmpty" checked>
                    <span class="checkmark"></span>
                    Only for empty fields
                </label>
            </div>
            <div class="ai-progress" id="aiProgress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill" id="aiProgressFill"></div>
                </div>
                <span id="aiProgressText">Processing...</span>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="admin-btn admin-btn-secondary" onclick="closeAiModal()">Cancel</button>
            <button type="button" class="admin-btn admin-btn-primary" id="aiGenerateBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                </svg>
                Generate with AI
            </button>
        </div>
    </div>
</div>

<style>
/* Container */
.import-export-container {
    max-width: 1200px;
}

/* Step Cards */
.step-card {
    margin-bottom: 1.5rem;
    transition: opacity 0.3s, filter 0.3s;
}

.step-card.disabled {
    opacity: 0.5;
    pointer-events: none;
    filter: grayscale(50%);
}

.step-card.active {
    opacity: 1;
    pointer-events: auto;
    filter: none;
}

.step-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--admin-border);
}

.step-number {
    width: 40px;
    height: 40px;
    background: var(--admin-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.125rem;
}

.step-info {
    flex: 1;
}

.step-info h3 {
    margin: 0;
    font-size: 1.125rem;
}

.step-info p {
    margin: 0.25rem 0 0;
    color: var(--admin-text-muted);
    font-size: 0.875rem;
}

/* Drop Zone */
.drop-zone {
    border: 2px dashed var(--admin-border);
    border-radius: var(--admin-radius);
    padding: 3rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--admin-bg);
}

.drop-zone:hover,
.drop-zone.dragover {
    border-color: var(--admin-primary);
    background: rgba(99, 102, 241, 0.05);
}

.drop-zone.has-file {
    border-style: solid;
    border-color: var(--admin-success);
    background: rgba(16, 185, 129, 0.05);
    padding: 1.5rem;
}

.drop-zone-content svg {
    width: 64px;
    height: 64px;
    color: var(--admin-text-muted);
    margin-bottom: 1rem;
}

.drop-zone-content h3 {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
}

.drop-zone-content p {
    margin: 0;
    color: var(--admin-text-muted);
}

.browse-link {
    color: var(--admin-primary);
    cursor: pointer;
    text-decoration: underline;
}

.supported-formats {
    display: block;
    margin-top: 1rem;
    font-size: 0.75rem;
    color: var(--admin-text-muted);
}

.drop-zone-file {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.file-icon svg {
    width: 48px;
    height: 48px;
    color: var(--admin-success);
}

.file-details {
    flex: 1;
    text-align: left;
}

.file-name {
    display: block;
    font-weight: 600;
    font-size: 1rem;
}

.file-meta {
    display: block;
    font-size: 0.8125rem;
    color: var(--admin-text-muted);
}

.remove-file-btn {
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    color: var(--admin-text-muted);
    transition: color 0.2s;
}

.remove-file-btn:hover {
    color: var(--admin-danger);
}

.remove-file-btn svg {
    width: 24px;
    height: 24px;
}

/* Template Links */
.template-links {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    justify-content: center;
}

.template-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--admin-bg);
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    color: var(--admin-text);
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s;
}

.template-link:hover {
    border-color: var(--admin-primary);
    color: var(--admin-primary);
}

.template-link svg {
    width: 16px;
    height: 16px;
}

/* Mapping Container */
.mapping-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
}

.mapping-source h4,
.mapping-target h4 {
    margin: 0 0 0.5rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    color: var(--admin-text-muted);
    letter-spacing: 0.05em;
}

.mapping-hint {
    margin: 0 0 1rem;
    font-size: 0.8125rem;
    color: var(--admin-text-muted);
}

/* Source Columns */
.source-columns {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.source-column {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--admin-card-bg);
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    cursor: grab;
    transition: all 0.2s;
}

.source-column:hover {
    border-color: var(--admin-primary);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.source-column.dragging {
    opacity: 0.5;
    cursor: grabbing;
}

.source-column.mapped {
    background: rgba(16, 185, 129, 0.1);
    border-color: var(--admin-success);
}

.source-column .drag-handle {
    color: var(--admin-text-muted);
}

.source-column .drag-handle svg {
    width: 16px;
    height: 16px;
}

.source-column .column-name {
    flex: 1;
    font-weight: 500;
}

.source-column .column-sample {
    font-size: 0.75rem;
    color: var(--admin-text-muted);
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Target Fields */
.target-fields {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.target-field {
    background: var(--admin-bg);
    border-radius: var(--admin-radius);
    padding: 0.75rem;
}

.target-field.required .field-label span:first-child::after {
    content: '*';
    color: var(--admin-danger);
    margin-left: 2px;
}

.field-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.required-badge {
    background: var(--admin-danger);
    color: white;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-size: 0.625rem;
    text-transform: uppercase;
}

.ai-btn {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    background: linear-gradient(135deg, #8b5cf6, #6366f1);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 0.6875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.ai-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
}

.ai-btn svg {
    width: 12px;
    height: 12px;
}

.field-dropzone {
    min-height: 44px;
    border: 2px dashed var(--admin-border);
    border-radius: var(--admin-radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.field-dropzone.dragover {
    border-color: var(--admin-primary);
    background: rgba(99, 102, 241, 0.1);
}

.field-dropzone.mapped {
    border-style: solid;
    border-color: var(--admin-success);
    background: rgba(16, 185, 129, 0.1);
}

.field-dropzone .placeholder {
    color: var(--admin-text-muted);
    font-size: 0.8125rem;
}

.field-dropzone .mapped-column {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: var(--admin-success);
    color: white;
    border-radius: 4px;
    font-size: 0.8125rem;
    font-weight: 500;
}

.field-dropzone .remove-mapping {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: white;
    opacity: 0.7;
}

.field-dropzone .remove-mapping:hover {
    opacity: 1;
}

.field-dropzone .remove-mapping svg {
    width: 14px;
    height: 14px;
}

/* Import Options */
.import-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.option-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.option-checkbox input {
    display: none;
}

.option-checkbox .checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid var(--admin-border);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.option-checkbox input:checked + .checkmark {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
}

.option-checkbox input:checked + .checkmark::after {
    content: '';
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg) translate(-1px, -1px);
}

/* Preview Stats */
.preview-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    border-radius: var(--admin-radius);
    background: var(--admin-bg);
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-label {
    font-size: 0.8125rem;
    color: var(--admin-text-muted);
}

.stat-new .stat-value { color: var(--admin-success); }
.stat-update .stat-value { color: var(--admin-primary); }
.stat-error .stat-value { color: var(--admin-danger); }

/* Preview Table */
.preview-table-container {
    max-height: 400px;
    overflow: auto;
    margin-bottom: 1.5rem;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
}

.preview-table {
    font-size: 0.8125rem;
}

.preview-table th,
.preview-table td {
    padding: 0.5rem 0.75rem;
    white-space: nowrap;
}

.row-new { background: rgba(16, 185, 129, 0.1); }
.row-update { background: rgba(99, 102, 241, 0.1); }
.row-error { background: rgba(239, 68, 68, 0.1); }

/* Error List */
.error-list {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid var(--admin-danger);
    border-radius: var(--admin-radius);
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.error-list h4 {
    margin: 0 0 0.5rem;
    color: var(--admin-danger);
    font-size: 0.875rem;
}

.error-list ul {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.8125rem;
    color: var(--admin-danger);
    max-height: 150px;
    overflow-y: auto;
}

/* Progress */
.import-progress {
    padding: 1.5rem;
    background: var(--admin-bg);
    border-radius: var(--admin-radius);
    margin-bottom: 1.5rem;
}

.progress-header {
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
    transition: width 0.3s;
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
}

.import-actions .admin-btn {
    flex: 1;
    justify-content: center;
}

/* Export Section */
.export-grid {
    display: grid;
    grid-template-columns: 1fr 250px;
    gap: 2rem;
}

.export-filters {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.format-selector {
    display: flex;
    gap: 0.5rem;
}

.format-option {
    flex: 1;
    cursor: pointer;
}

.format-option input {
    display: none;
}

.format-option span {
    display: block;
    padding: 0.5rem 1rem;
    text-align: center;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius-sm);
    transition: all 0.2s;
}

.format-option input:checked + span {
    background: var(--admin-primary);
    border-color: var(--admin-primary);
    color: white;
}

.form-hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: var(--admin-text-muted);
}

.export-actions-panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.export-count {
    margin-bottom: 1.5rem;
}

.count-value {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: var(--admin-primary);
}

.count-label {
    color: var(--admin-text-muted);
    font-size: 0.875rem;
}

.admin-btn-lg {
    padding: 0.875rem 2rem;
    font-size: 1rem;
}

/* AI Modal */
.ai-options {
    margin: 1rem 0;
}

.ai-progress {
    margin-top: 1rem;
}

.ai-progress .progress-bar {
    margin-bottom: 0.5rem;
}

/* Responsive */
@media (max-width: 992px) {
    .mapping-container {
        grid-template-columns: 1fr;
    }

    .target-fields {
        grid-template-columns: 1fr;
    }

    .export-grid {
        grid-template-columns: 1fr;
    }

    .preview-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .import-options {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const dropZoneContent = document.getElementById('dropZoneContent');
    const dropZoneFile = document.getElementById('dropZoneFile');
    const fileName = document.getElementById('fileName');
    const fileMeta = document.getElementById('fileMeta');
    const removeFile = document.getElementById('removeFile');
    const sourceColumns = document.getElementById('sourceColumns');
    const autoMapBtn = document.getElementById('autoMapBtn');
    const previewBtn = document.getElementById('previewBtn');
    const importBtn = document.getElementById('importBtn');

    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');

    let currentFile = null;
    let parsedData = null;
    let columnMapping = {};
    let aiField = null;

    // Click to browse
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag and drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
        dropZone.addEventListener(event, e => {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    ['dragenter', 'dragover'].forEach(event => {
        dropZone.addEventListener(event, () => dropZone.classList.add('dragover'));
    });

    ['dragleave', 'drop'].forEach(event => {
        dropZone.addEventListener(event, () => dropZone.classList.remove('dragover'));
    });

    dropZone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) {
            handleFile(e.dataTransfer.files[0]);
        }
    });

    fileInput.addEventListener('change', e => {
        if (e.target.files.length) {
            handleFile(e.target.files[0]);
        }
    });

    removeFile.addEventListener('click', e => {
        e.stopPropagation();
        resetUpload();
    });

    function handleFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['csv', 'json'].includes(ext)) {
            alert('Please upload a CSV or JSON file');
            return;
        }

        currentFile = file;
        fileName.textContent = file.name;
        fileMeta.textContent = formatSize(file.size) + ' - ' + ext.toUpperCase();

        dropZoneContent.style.display = 'none';
        dropZoneFile.style.display = 'flex';
        dropZone.classList.add('has-file');

        // Parse file
        const reader = new FileReader();
        reader.onload = e => {
            try {
                if (ext === 'json') {
                    parsedData = JSON.parse(e.target.result);
                    if (parsedData.products) parsedData = parsedData.products;
                    if (!Array.isArray(parsedData)) parsedData = [parsedData];
                } else {
                    parsedData = parseCSV(e.target.result);
                }

                if (parsedData.length > 0) {
                    populateSourceColumns(Object.keys(parsedData[0]));
                    enableStep(2);
                }
            } catch (err) {
                alert('Error parsing file: ' + err.message);
                resetUpload();
            }
        };
        reader.readAsText(file);
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function parseCSV(content) {
        const lines = content.split(/\r?\n/).filter(l => l.trim());
        if (lines.length < 2) return [];

        const headers = parseCSVLine(lines[0]);
        return lines.slice(1).map(line => {
            const values = parseCSVLine(line);
            const row = {};
            headers.forEach((h, i) => row[h.toLowerCase().trim()] = values[i] || '');
            return row;
        });
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

    function resetUpload() {
        currentFile = null;
        parsedData = null;
        columnMapping = {};
        fileInput.value = '';
        dropZoneContent.style.display = 'block';
        dropZoneFile.style.display = 'none';
        dropZone.classList.remove('has-file');
        sourceColumns.innerHTML = '';
        resetMappings();
        disableStep(2);
        disableStep(3);
    }

    function enableStep(num) {
        document.getElementById('step' + num).classList.remove('disabled');
        document.getElementById('step' + num).classList.add('active');
        if (num === 2) autoMapBtn.disabled = false;
        if (num === 3) {
            previewBtn.disabled = false;
            importBtn.disabled = false;
        }
    }

    function disableStep(num) {
        document.getElementById('step' + num).classList.add('disabled');
        document.getElementById('step' + num).classList.remove('active');
        if (num === 2) autoMapBtn.disabled = true;
        if (num === 3) {
            previewBtn.disabled = true;
            importBtn.disabled = true;
        }
    }

    function populateSourceColumns(columns) {
        sourceColumns.innerHTML = columns.map(col => {
            const sample = parsedData[0][col] || '';
            return `
                <div class="source-column" draggable="true" data-column="${escapeHtml(col)}">
                    <span class="drag-handle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="8" y1="6" x2="16" y2="6"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                            <line x1="8" y1="18" x2="16" y2="18"></line>
                        </svg>
                    </span>
                    <span class="column-name">${escapeHtml(col)}</span>
                    <span class="column-sample" title="${escapeHtml(sample)}">${escapeHtml(sample.substring(0, 20))}</span>
                </div>
            `;
        }).join('');

        // Setup drag events
        setupDragAndDrop();
    }

    function setupDragAndDrop() {
        const draggables = document.querySelectorAll('.source-column');
        const dropzones = document.querySelectorAll('.field-dropzone');

        draggables.forEach(el => {
            el.addEventListener('dragstart', e => {
                el.classList.add('dragging');
                e.dataTransfer.setData('text/plain', el.dataset.column);
            });

            el.addEventListener('dragend', () => {
                el.classList.remove('dragging');
            });
        });

        dropzones.forEach(zone => {
            zone.addEventListener('dragover', e => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', () => {
                zone.classList.remove('dragover');
            });

            zone.addEventListener('drop', e => {
                e.preventDefault();
                zone.classList.remove('dragover');

                const column = e.dataTransfer.getData('text/plain');
                const field = zone.dataset.field;

                mapColumn(column, field, zone);
            });
        });
    }

    function mapColumn(column, field, zone) {
        // Remove previous mapping for this field
        if (columnMapping[field]) {
            const prevCol = document.querySelector(`.source-column[data-column="${columnMapping[field]}"]`);
            if (prevCol) prevCol.classList.remove('mapped');
        }

        // Set new mapping
        columnMapping[field] = column;

        // Update UI
        const sourceCol = document.querySelector(`.source-column[data-column="${column}"]`);
        if (sourceCol) sourceCol.classList.add('mapped');

        zone.classList.add('mapped');
        zone.innerHTML = `
            <span class="mapped-column">
                ${escapeHtml(column)}
                <button type="button" class="remove-mapping" onclick="removeMapping('${field}', this.parentElement.parentElement)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </span>
        `;

        checkMappingComplete();
    }

    window.removeMapping = function(field, zone) {
        const column = columnMapping[field];
        delete columnMapping[field];

        const sourceCol = document.querySelector(`.source-column[data-column="${column}"]`);
        if (sourceCol) sourceCol.classList.remove('mapped');

        zone.classList.remove('mapped');
        zone.innerHTML = '<span class="placeholder">Drop column here</span>';

        checkMappingComplete();
    };

    function resetMappings() {
        columnMapping = {};
        document.querySelectorAll('.field-dropzone').forEach(zone => {
            zone.classList.remove('mapped');
            zone.innerHTML = '<span class="placeholder">Drop column here</span>';
        });
        document.querySelectorAll('.source-column').forEach(col => {
            col.classList.remove('mapped');
        });
    }

    function checkMappingComplete() {
        // Need at least SKU mapped
        if (columnMapping.sku) {
            enableStep(3);
        } else {
            disableStep(3);
        }
    }

    // Auto-map button
    autoMapBtn.addEventListener('click', () => {
        if (!parsedData || parsedData.length === 0) return;

        const columns = Object.keys(parsedData[0]);
        const mappings = {
            'sku': ['sku', 'product_code', 'code', 'item_number', 'item_code', 'barcode'],
            'name': ['name', 'product_name', 'title', 'product_title', 'product'],
            'price': ['price', 'cost', 'selling_price', 'retail_price', 'unit_price'],
            'compare_price': ['compare_price', 'original_price', 'msrp', 'rrp', 'was_price'],
            'cost_price': ['cost_price', 'cost', 'wholesale_price', 'buy_price'],
            'stock': ['stock', 'quantity', 'qty', 'inventory', 'stock_quantity', 'available'],
            'category': ['category', 'category_name', 'product_category', 'type'],
            'description': ['description', 'product_description', 'desc', 'details', 'long_description'],
            'short_description': ['short_description', 'summary', 'short_desc', 'excerpt'],
            'weight': ['weight', 'product_weight', 'kg', 'mass'],
            'status': ['status', 'active', 'enabled', 'published'],
            'image_url': ['image_url', 'image', 'picture', 'photo', 'thumbnail', 'img']
        };

        resetMappings();

        Object.keys(mappings).forEach(field => {
            const possibleNames = mappings[field];
            const match = columns.find(col =>
                possibleNames.some(name => col.toLowerCase().includes(name))
            );

            if (match) {
                const zone = document.querySelector(`.field-dropzone[data-field="${field}"]`);
                if (zone) mapColumn(match, field, zone);
            }
        });
    });

    // Preview button
    previewBtn.addEventListener('click', generatePreview);

    function generatePreview() {
        if (!parsedData || Object.keys(columnMapping).length === 0) return;

        const previewStats = document.getElementById('previewStats');
        const previewContainer = document.getElementById('previewContainer');
        const previewHead = document.getElementById('previewHead');
        const previewBody = document.getElementById('previewBody');
        const errorList = document.getElementById('errorList');
        const errorItems = document.getElementById('errorItems');

        let totalCount = parsedData.length;
        let newCount = 0;
        let updateCount = 0;
        let errorCount = 0;
        const errors = [];

        // Build header
        const fields = Object.keys(columnMapping);
        previewHead.innerHTML = '<tr>' + fields.map(f => `<th>${escapeHtml(f)}</th>`).join('') + '<th>Status</th></tr>';

        // Build body
        const rows = parsedData.slice(0, 50).map((row, i) => {
            const validation = validateRow(row, i + 2);
            let rowClass = '';
            let status = '';

            if (validation.errors.length > 0) {
                rowClass = 'row-error';
                status = '<span style="color:var(--admin-danger)">Error</span>';
                errorCount++;
                errors.push(...validation.errors);
            } else if (validation.isNew) {
                rowClass = 'row-new';
                status = '<span style="color:var(--admin-success)">New</span>';
                newCount++;
            } else {
                rowClass = 'row-update';
                status = '<span style="color:var(--admin-primary)">Update</span>';
                updateCount++;
            }

            const cells = fields.map(f => {
                const col = columnMapping[f];
                const val = row[col] || '';
                return `<td>${escapeHtml(String(val).substring(0, 50))}</td>`;
            }).join('');

            return `<tr class="${rowClass}">${cells}<td>${status}</td></tr>`;
        });

        previewBody.innerHTML = rows.join('');

        // Update stats
        document.getElementById('statTotal').textContent = totalCount;
        document.getElementById('statNew').textContent = newCount;
        document.getElementById('statUpdate').textContent = updateCount;
        document.getElementById('statErrors').textContent = errorCount;

        previewStats.style.display = 'grid';
        previewContainer.style.display = 'block';

        if (errors.length > 0) {
            errorItems.innerHTML = errors.slice(0, 10).map(e => `<li>${escapeHtml(e)}</li>`).join('');
            errorList.style.display = 'block';
        } else {
            errorList.style.display = 'none';
        }
    }

    function validateRow(row, rowNum) {
        const errors = [];
        let isNew = true;

        const skuCol = columnMapping.sku;
        if (!skuCol || !row[skuCol] || String(row[skuCol]).trim() === '') {
            errors.push(`Row ${rowNum}: SKU is required`);
        }

        const nameCol = columnMapping.name;
        const priceCol = columnMapping.price;

        if (priceCol && row[priceCol] && isNaN(parseFloat(row[priceCol]))) {
            errors.push(`Row ${rowNum}: Invalid price`);
        }

        return { errors, isNew };
    }

    // Import button
    importBtn.addEventListener('click', doImport);

    function doImport() {
        if (!parsedData || Object.keys(columnMapping).length === 0) return;

        const progress = document.getElementById('importProgress');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const progressPercent = document.getElementById('progressPercent');
        const progressDetails = document.getElementById('progressDetails');

        progress.style.display = 'block';
        importBtn.disabled = true;
        previewBtn.disabled = true;

        // Prepare data
        const importData = parsedData.map(row => {
            const mapped = {};
            Object.keys(columnMapping).forEach(field => {
                mapped[field] = row[columnMapping[field]] || '';
            });
            return mapped;
        });

        const aiEnabled = document.getElementById('aiGenerateAll').checked;
        const batchSize = aiEnabled ? 3 : 50; // Small batches for AI to avoid 504 timeout
        const batches = [];
        for (let i = 0; i < importData.length; i += batchSize) {
            batches.push(importData.slice(i, i + batchSize));
        }

        const updateExisting = document.getElementById('updateExisting').checked ? '1' : '0';
        const createNew = document.getElementById('createNew').checked ? '1' : '0';
        const skipErrors = document.getElementById('skipErrors').checked ? '1' : '0';
        const aiGenerate = aiEnabled ? '1' : '0';

        let totalCreated = 0;
        let totalUpdated = 0;
        let totalFailed = 0;
        let totalErrors = [];
        let completedBatches = 0;

        progressText.textContent = `Processing batch 1 of ${batches.length}...`;

        async function processBatch(batchIndex) {
            const batch = batches[batchIndex];
            const formData = new FormData();
            formData.append('_token', '<?= csrf_token() ?>');
            formData.append('data', JSON.stringify(batch));
            formData.append('update_existing', updateExisting);
            formData.append('create_new', createNew);
            formData.append('skip_errors', '1'); // Always skip errors in batch mode
            formData.append('ai_generate', aiGenerate);

            const response = await fetch('<?= url("/admin/products/import/process") ?>', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                // Server returned HTML (504/502/etc) - treat as partial failure
                return { success: false, error: 'Server error: ' + response.status, created: 0, updated: 0, failed: batch.length, errors: ['Server error: ' + response.status] };
            }
            return result;
        }

        async function processAllBatches() {
            for (let i = 0; i < batches.length; i++) {
                progressText.textContent = `Processing batch ${i + 1} of ${batches.length}` + (aiEnabled ? ' (AI generating...)' : '...');

                try {
                    const result = await processBatch(i);

                    if (result.success) {
                        totalCreated += result.created || 0;
                        totalUpdated += result.updated || 0;
                        totalFailed += result.failed || 0;
                        if (result.errors) totalErrors = totalErrors.concat(result.errors);
                    } else {
                        totalFailed += batches[i].length;
                        totalErrors.push(result.error || 'Batch ' + (i + 1) + ' failed');
                    }
                } catch (err) {
                    totalFailed += batches[i].length;
                    totalErrors.push('Batch ' + (i + 1) + ': ' + err.message);
                }

                completedBatches++;
                const pct = Math.round((completedBatches / batches.length) * 100);
                progressFill.style.width = pct + '%';
                progressPercent.textContent = pct + '%';
                progressDetails.innerHTML = `Created: ${totalCreated}, Updated: ${totalUpdated}` + (totalFailed > 0 ? `, Failed: ${totalFailed}` : '');
            }

            // Done
            progressFill.style.width = '100%';
            progressPercent.textContent = '100%';
            progressText.textContent = 'Import completed!';

            let summary = `Created: ${totalCreated}, Updated: ${totalUpdated}`;
            if (totalFailed > 0) summary += `, Failed: ${totalFailed}`;
            if (totalErrors.length > 0) {
                summary += `<br><br>Errors (${totalErrors.length}):<br>` + totalErrors.slice(0, 20).join('<br>');
            }
            progressDetails.innerHTML = summary;

            if (totalCreated > 0 || totalUpdated > 0) {
                setTimeout(() => {
                    window.location.href = '<?= url("/admin/products") ?>';
                }, 3000);
            } else {
                importBtn.disabled = false;
                previewBtn.disabled = false;
            }
        }

        processAllBatches();
    }

    // AI buttons
    document.querySelectorAll('.ai-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            aiField = btn.dataset.field;
            document.getElementById('aiModalText').textContent =
                `AI will generate ${aiField.replace('_', ' ')} for products missing this field.`;
            document.getElementById('aiModal').classList.add('show');
        });
    });

    window.closeAiModal = function() {
        document.getElementById('aiModal').classList.remove('show');
        aiField = null;
    };

    document.getElementById('aiModal').addEventListener('click', e => {
        if (e.target.id === 'aiModal') closeAiModal();
    });

    document.getElementById('aiGenerateBtn').addEventListener('click', () => {
        if (!aiField || !parsedData) return;

        const aiProgress = document.getElementById('aiProgress');
        const aiProgressFill = document.getElementById('aiProgressFill');
        const aiProgressText = document.getElementById('aiProgressText');

        aiProgress.style.display = 'block';

        // Simulate AI generation
        let pct = 0;
        const interval = setInterval(() => {
            pct += 5;
            aiProgressFill.style.width = pct + '%';
            aiProgressText.textContent = `Processing... ${Math.round(pct)}%`;

            if (pct >= 100) {
                clearInterval(interval);
                aiProgressText.textContent = 'Done!';
                setTimeout(() => {
                    closeAiModal();
                    aiProgress.style.display = 'none';
                    aiProgressFill.style.width = '0%';
                }, 1000);
            }
        }, 100);
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
