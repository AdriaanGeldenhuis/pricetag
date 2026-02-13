<!-- Admin Products Import/Export with Drag & Drop Column Mapping + AI -->
<style>
/* Import/Export Styles */
.import-export-container {
    max-width: 1400px;
}

.step-card {
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.step-card.disabled {
    opacity: 0.5;
    pointer-events: none;
}

.step-card.completed {
    border-color: #10b981;
}

.step-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: rgba(255,255,255,0.03);
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    color: white;
}

.step-card.completed .step-number {
    background: linear-gradient(135deg, #10b981, #059669);
}

.step-info h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.step-info p {
    margin: 0.25rem 0 0;
    font-size: 0.875rem;
    color: #9ca3af;
}

/* Drop Zone */
.drop-zone {
    border: 2px dashed rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 3rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.02);
}

.drop-zone:hover, .drop-zone.drag-over {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}

.drop-zone-content svg {
    width: 48px;
    height: 48px;
    margin: 0 auto 1rem;
    color: #6b7280;
}

.drop-zone-content h3 {
    margin: 0 0 0.5rem;
    font-size: 1.25rem;
}

.drop-zone-content p {
    color: #9ca3af;
    margin: 0;
}

.browse-link {
    color: #3b82f6;
    text-decoration: underline;
    cursor: pointer;
}

.supported-formats {
    display: block;
    margin-top: 1rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.drop-zone-file {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 8px;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.file-icon svg {
    width: 40px;
    height: 40px;
    color: #3b82f6;
}

.file-details {
    flex: 1;
}

.file-name {
    display: block;
    font-weight: 600;
}

.file-meta {
    font-size: 0.875rem;
    color: #9ca3af;
}

.remove-file-btn {
    background: transparent;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    color: #ef4444;
    border-radius: 4px;
}

.remove-file-btn:hover {
    background: rgba(239, 68, 68, 0.1);
}

/* Column Mapping */
.mapping-container {
    display: grid;
    grid-template-columns: 1fr 60px 1fr;
    gap: 2rem;
    align-items: start;
}

.mapping-section h4 {
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.source-columns {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.source-column {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(139, 92, 246, 0.2);
    border: 1px solid rgba(139, 92, 246, 0.4);
    border-radius: 6px;
    cursor: grab;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.source-column:hover {
    background: rgba(139, 92, 246, 0.3);
    transform: translateY(-1px);
}

.source-column.dragging {
    opacity: 0.5;
}

.source-column.used {
    opacity: 0.4;
    cursor: not-allowed;
    text-decoration: line-through;
}

.source-column svg {
    width: 14px;
    height: 14px;
}

.mapping-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    padding-top: 2.5rem;
}

.mapping-arrow svg {
    width: 24px;
    height: 24px;
    color: #6b7280;
}

.target-fields {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.target-field {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: rgba(255,255,255,0.05);
    border: 2px dashed rgba(255,255,255,0.1);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.target-field.drag-over {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}

.target-field.mapped {
    border-style: solid;
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.1);
}

.target-field-name {
    min-width: 140px;
    font-weight: 500;
}

.target-field-name .required {
    color: #ef4444;
    margin-left: 0.25rem;
}

.target-field-value {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.mapped-column {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    background: rgba(16, 185, 129, 0.2);
    border-radius: 4px;
    font-size: 0.875rem;
}

.remove-mapping {
    background: transparent;
    border: none;
    padding: 0.25rem;
    cursor: pointer;
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-generate-btn {
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    border: none;
    border-radius: 4px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s ease;
}

.ai-generate-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
}

.ai-generate-btn svg {
    width: 12px;
    height: 12px;
}

.field-hint {
    font-size: 0.7rem;
    color: #9ca3af;
    font-style: italic;
}

/* Preview Table */
.preview-container {
    overflow-x: auto;
    margin-top: 1rem;
}

.preview-table {
    width: 100%;
    font-size: 0.875rem;
    border-collapse: collapse;
}

.preview-table th,
.preview-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.preview-table th {
    background: rgba(255,255,255,0.05);
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.preview-table tr:hover td {
    background: rgba(255,255,255,0.02);
}

/* Import Options */
.import-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.import-option {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    background: rgba(255,255,255,0.03);
    border-radius: 8px;
    cursor: pointer;
}

.import-option input[type="checkbox"] {
    margin-top: 0.25rem;
}

.import-option-content h5 {
    margin: 0 0 0.25rem;
    font-weight: 500;
}

.import-option-content p {
    margin: 0;
    font-size: 0.75rem;
    color: #9ca3af;
}

/* Progress */
.import-progress {
    margin-top: 1.5rem;
}

.progress-bar-container {
    height: 8px;
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    transition: width 0.3s ease;
    border-radius: 4px;
}

.progress-status {
    display: flex;
    justify-content: space-between;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #9ca3af;
}

/* Export Section */
.export-section {
    margin-top: 3rem;
    padding-top: 3rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.export-filters {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

/* Template Links */
.template-links {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.template-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.05);
    border-radius: 6px;
    font-size: 0.875rem;
    color: inherit;
    text-decoration: none;
    transition: all 0.2s ease;
}

.template-link:hover {
    background: rgba(255,255,255,0.1);
}

.template-link svg {
    width: 16px;
    height: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .mapping-container {
        grid-template-columns: 1fr;
    }

    .mapping-arrow {
        transform: rotate(90deg);
        padding: 1rem 0;
    }
}
</style>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="admin-page-title mb-0">Import / Export Products</h1>
        <p class="text-muted text-sm mt-1">Import products with intelligent column mapping and AI-powered content generation</p>
    </div>
    <a href="<?= url('/admin/products') ?>" class="btn btn-ghost">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back to Products
    </a>
</div>

<div class="import-export-container">
    <!-- Step 1: Upload File -->
    <div class="card step-card" id="step1">
        <div class="step-header">
            <div class="step-number">1</div>
            <div class="step-info">
                <h3>Upload Your File</h3>
                <p>Drag & drop your CSV or JSON file, or click to browse</p>
            </div>
        </div>
        <div class="card-body">
            <div class="drop-zone" id="dropZone">
                <div class="drop-zone-content" id="dropZoneContent">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <h3>Drag & Drop your file here</h3>
                    <p>or <span class="browse-link">click to browse</span></p>
                    <span class="supported-formats">Supports CSV and JSON files (max 10MB)</span>
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <input type="file" id="fileInput" accept=".csv,.json" style="display: none;">
            </div>

            <div class="template-links">
                <a href="<?= url('/admin/products/template') ?>" class="template-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download CSV Template
                </a>
                <a href="<?= url('/admin/products/template?format=json') ?>" class="template-link">
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
    <div class="card step-card disabled" id="step2">
        <div class="step-header">
            <div class="step-number">2</div>
            <div class="step-info">
                <h3>Map Your Columns</h3>
                <p>Drag columns from your file to the product fields</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline" id="autoMapBtn" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
                Auto-Detect Mapping
            </button>
            <button type="button" class="btn btn-sm btn-ghost" id="clearMappingBtn" disabled>
                Clear All
            </button>
        </div>
        <div class="card-body">
            <div class="mapping-container">
                <!-- Source Columns (from file) -->
                <div class="mapping-section">
                    <h4>Your File Columns</h4>
                    <div class="source-columns" id="sourceColumns">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>

                <!-- Arrow -->
                <div class="mapping-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </div>

                <!-- Target Fields (product fields) -->
                <div class="mapping-section">
                    <h4>Product Fields</h4>
                    <div class="target-fields" id="targetFields">
                        <div class="target-field" data-field="sku">
                            <span class="target-field-name">SKU <span class="required">*</span></span>
                            <div class="target-field-value" data-drop="sku"></div>
                        </div>
                        <div class="target-field" data-field="name">
                            <span class="target-field-name">Name <span class="required">*</span></span>
                            <div class="target-field-value" data-drop="name"></div>
                            <button type="button" class="ai-generate-btn" data-ai-field="name" title="AI Generate">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                    <path d="M2 17l10 5 10-5"></path>
                                </svg>
                                AI
                            </button>
                        </div>
                        <div class="target-field" data-field="price">
                            <span class="target-field-name">Price <span class="required">*</span></span>
                            <div class="target-field-value" data-drop="price"></div>
                        </div>
                        <div class="target-field" data-field="description">
                            <span class="target-field-name">Description</span>
                            <div class="target-field-value" data-drop="description"></div>
                            <button type="button" class="ai-generate-btn" data-ai-field="description" title="AI Generate">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                    <path d="M2 17l10 5 10-5"></path>
                                </svg>
                                AI
                            </button>
                        </div>
                        <div class="target-field" data-field="short_description">
                            <span class="target-field-name">Short Description</span>
                            <div class="target-field-value" data-drop="short_description"></div>
                            <button type="button" class="ai-generate-btn" data-ai-field="short_description" title="AI Generate">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                    <path d="M2 17l10 5 10-5"></path>
                                </svg>
                                AI
                            </button>
                        </div>
                        <div class="target-field" data-field="compare_price">
                            <span class="target-field-name">Compare Price</span>
                            <div class="target-field-value" data-drop="compare_price"></div>
                        </div>
                        <div class="target-field" data-field="cost_price">
                            <span class="target-field-name">Cost Price</span>
                            <div class="target-field-value" data-drop="cost_price"></div>
                        </div>
                        <div class="target-field" data-field="stock">
                            <span class="target-field-name">Stock Quantity</span>
                            <div class="target-field-value" data-drop="stock"></div>
                        </div>
                        <div class="target-field" data-field="category">
                            <span class="target-field-name">Category</span>
                            <div class="target-field-value" data-drop="category"></div>
                        </div>
                        <div class="target-field" data-field="brand">
                            <span class="target-field-name">Brand</span>
                            <div class="target-field-value" data-drop="brand"></div>
                            <span class="field-hint">(Creates attribute if not exists)</span>
                        </div>
                        <div class="target-field" data-field="vendor">
                            <span class="target-field-name">Vendor / Supplier</span>
                            <div class="target-field-value" data-drop="vendor"></div>
                        </div>
                        <div class="target-field" data-field="image">
                            <span class="target-field-name">Image URL</span>
                            <div class="target-field-value" data-drop="image"></div>
                        </div>
                        <div class="target-field" data-field="weight">
                            <span class="target-field-name">Weight (kg)</span>
                            <div class="target-field-value" data-drop="weight"></div>
                        </div>
                        <div class="target-field" data-field="status">
                            <span class="target-field-name">Status</span>
                            <div class="target-field-value" data-drop="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Preview & Import -->
    <div class="card step-card disabled" id="step3">
        <div class="step-header">
            <div class="step-number">3</div>
            <div class="step-info">
                <h3>Preview & Import</h3>
                <p>Review your data and start the import</p>
            </div>
        </div>
        <div class="card-body">
            <div class="import-options">
                <label class="import-option">
                    <input type="checkbox" id="updateExisting" checked>
                    <div class="import-option-content">
                        <h5>Update Existing Products</h5>
                        <p>Update products that match by SKU</p>
                    </div>
                </label>
                <label class="import-option">
                    <input type="checkbox" id="createNew" checked>
                    <div class="import-option-content">
                        <h5>Create New Products</h5>
                        <p>Create products for new SKUs</p>
                    </div>
                </label>
                <label class="import-option">
                    <input type="checkbox" id="skipErrors">
                    <div class="import-option-content">
                        <h5>Skip Rows with Errors</h5>
                        <p>Continue importing even if some rows fail</p>
                    </div>
                </label>
                <label class="import-option" style="border: 1px solid rgba(139, 92, 246, 0.4); background: rgba(139, 92, 246, 0.08);">
                    <input type="checkbox" id="aiGenerate">
                    <div class="import-option-content">
                        <h5 style="color: #a78bfa;">AI Generate Missing Data</h5>
                        <p>Fill ALL empty fields: names, descriptions, SEO, specs, categories, brand, weight, and product images. Only SKU is required.</p>
                    </div>
                </label>
            </div>

            <div class="preview-container" id="previewContainer">
                <p class="text-muted text-center py-8">Upload a file and map columns to see preview</p>
            </div>

            <div class="import-progress" id="importProgress" style="display: none;">
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                </div>
                <div class="progress-status">
                    <span id="progressText">Preparing import...</span>
                    <span id="progressCount">0 / 0</span>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button" class="btn btn-primary" id="startImportBtn" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Start Import
                </button>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="export-section">
        <h2 class="text-xl font-semibold mb-4">Export Products</h2>
        <div class="card">
            <div class="card-body">
                <form action="<?= url('/admin/products/export') ?>" method="GET">
                    <div class="export-filters">
                        <div class="form-group mb-0">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Stock</label>
                            <select name="stock" class="form-select">
                                <option value="">All Stock</option>
                                <option value="in_stock">In Stock</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Format</label>
                            <select name="format" class="form-select">
                                <option value="csv">CSV</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary mt-4">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Export Products
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const dropZone = document.getElementById('dropZone');
    const dropZoneContent = document.getElementById('dropZoneContent');
    const dropZoneFile = document.getElementById('dropZoneFile');
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    const fileMeta = document.getElementById('fileMeta');
    const removeFile = document.getElementById('removeFile');
    const sourceColumns = document.getElementById('sourceColumns');
    const targetFields = document.getElementById('targetFields');
    const autoMapBtn = document.getElementById('autoMapBtn');
    const clearMappingBtn = document.getElementById('clearMappingBtn');
    const previewContainer = document.getElementById('previewContainer');
    const startImportBtn = document.getElementById('startImportBtn');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');

    let fileData = null;
    let parsedData = [];
    let columnMapping = {};
    let aiFields = [];

    // File upload handling
    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    removeFile.addEventListener('click', (e) => {
        e.stopPropagation();
        resetUpload();
    });

    function handleFile(file) {
        if (!file.name.match(/\.(csv|json)$/i)) {
            alert('Please upload a CSV or JSON file');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB');
            return;
        }

        fileData = file;
        fileName.textContent = file.name;
        fileMeta.textContent = formatFileSize(file.size) + ' - ' + new Date().toLocaleTimeString();

        dropZoneContent.style.display = 'none';
        dropZoneFile.style.display = 'flex';

        // Parse file
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                if (file.name.endsWith('.json')) {
                    parsedData = JSON.parse(e.target.result);
                    if (!Array.isArray(parsedData)) {
                        parsedData = [parsedData];
                    }
                } else {
                    parsedData = parseCSV(e.target.result);
                }

                if (parsedData.length > 0) {
                    populateSourceColumns(Object.keys(parsedData[0]));
                    enableStep2();
                }
            } catch (err) {
                alert('Error parsing file: ' + err.message);
                resetUpload();
            }
        };
        reader.readAsText(file);
    }

    function parseCSV(text) {
        const lines = text.split('\n').filter(line => line.trim());
        if (lines.length < 2) return [];

        const headers = parseCSVLine(lines[0]);
        const data = [];

        for (let i = 1; i < lines.length; i++) {
            const values = parseCSVLine(lines[i]);
            if (values.length === headers.length) {
                const row = {};
                headers.forEach((h, idx) => {
                    row[h.trim()] = values[idx];
                });
                data.push(row);
            }
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
                inQuotes = !inQuotes;
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

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function resetUpload() {
        fileData = null;
        parsedData = [];
        columnMapping = {};
        aiFields = [];
        fileInput.value = '';
        dropZoneContent.style.display = '';
        dropZoneFile.style.display = 'none';
        sourceColumns.innerHTML = '';
        disableStep2();
        disableStep3();
        clearAllMappings();
    }

    function populateSourceColumns(columns) {
        sourceColumns.innerHTML = '';
        columns.forEach(col => {
            const div = document.createElement('div');
            div.className = 'source-column';
            div.draggable = true;
            div.dataset.column = col;
            div.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="5" r="1"></circle>
                    <circle cx="9" cy="12" r="1"></circle>
                    <circle cx="9" cy="19" r="1"></circle>
                    <circle cx="15" cy="5" r="1"></circle>
                    <circle cx="15" cy="12" r="1"></circle>
                    <circle cx="15" cy="19" r="1"></circle>
                </svg>
                ${col}
            `;

            div.addEventListener('dragstart', handleDragStart);
            div.addEventListener('dragend', handleDragEnd);

            sourceColumns.appendChild(div);
        });
    }

    function enableStep2() {
        step2.classList.remove('disabled');
        document.getElementById('step1').classList.add('completed');
        autoMapBtn.disabled = false;
        clearMappingBtn.disabled = false;
    }

    function disableStep2() {
        step2.classList.add('disabled');
        document.getElementById('step1').classList.remove('completed');
        autoMapBtn.disabled = true;
        clearMappingBtn.disabled = true;
    }

    function enableStep3() {
        step3.classList.remove('disabled');
        step2.classList.add('completed');
        startImportBtn.disabled = false;
        updatePreview();
    }

    function disableStep3() {
        step3.classList.add('disabled');
        step2.classList.remove('completed');
        startImportBtn.disabled = true;
        previewContainer.innerHTML = '<p class="text-muted text-center py-8">Upload a file and map columns to see preview</p>';
    }

    // Drag and drop for column mapping
    let draggedColumn = null;
    let draggedElement = null;

    function handleDragStart(e) {
        draggedColumn = e.target.dataset.column || e.target.closest('.source-column')?.dataset.column;
        draggedElement = e.target.closest('.source-column') || e.target;

        if (draggedColumn) {
            e.dataTransfer.setData('text/plain', draggedColumn);
            e.dataTransfer.effectAllowed = 'move';
            draggedElement.classList.add('dragging');
        }
    }

    function handleDragEnd(e) {
        if (draggedElement) {
            draggedElement.classList.remove('dragging');
        }
        draggedColumn = null;
        draggedElement = null;
        // Remove all drag-over states
        document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
    }

    // Set up drop targets - also on the parent .target-field
    function setupDropTargets() {
        document.querySelectorAll('.target-field').forEach(targetField => {
            const dropZone = targetField.querySelector('.target-field-value');
            const field = dropZone?.dataset.drop;
            if (!field) return;

            // Handle dragover on the whole target-field
            targetField.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.dataTransfer.dropEffect = 'move';
                targetField.classList.add('drag-over');
            });

            targetField.addEventListener('dragenter', (e) => {
                e.preventDefault();
                targetField.classList.add('drag-over');
            });

            targetField.addEventListener('dragleave', (e) => {
                // Only remove if leaving the target field entirely
                if (!targetField.contains(e.relatedTarget)) {
                    targetField.classList.remove('drag-over');
                }
            });

            targetField.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                targetField.classList.remove('drag-over');

                const columnName = e.dataTransfer.getData('text/plain') || draggedColumn;
                if (columnName && field) {
                    setMapping(field, columnName);
                }
            });
        });
    }

    // Initialize drop targets
    setupDropTargets();

    function setMapping(field, column) {
        // Remove previous mapping for this field
        if (columnMapping[field]) {
            const prevCol = document.querySelector(`.source-column[data-column="${columnMapping[field]}"]`);
            if (prevCol) prevCol.classList.remove('used');
        }

        // Remove if column was mapped elsewhere
        Object.keys(columnMapping).forEach(f => {
            if (columnMapping[f] === column && f !== field) {
                delete columnMapping[f];
                updateFieldDisplay(f, null);
            }
        });

        columnMapping[field] = column;
        updateFieldDisplay(field, column);

        // Mark source column as used
        const sourceCol = document.querySelector(`.source-column[data-column="${column}"]`);
        if (sourceCol) sourceCol.classList.add('used');

        checkMappingComplete();
    }

    function updateFieldDisplay(field, column) {
        const target = document.querySelector(`[data-drop="${field}"]`);
        const fieldEl = target.parentElement;

        if (column) {
            target.innerHTML = `
                <span class="mapped-column">
                    ${column}
                    <button type="button" class="remove-mapping" data-field="${field}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </span>
            `;
            fieldEl.classList.add('mapped');

            target.querySelector('.remove-mapping').addEventListener('click', (e) => {
                e.stopPropagation();
                removeMapping(field);
            });
        } else {
            target.innerHTML = '';
            fieldEl.classList.remove('mapped');
        }
    }

    function removeMapping(field) {
        const column = columnMapping[field];
        if (column) {
            const sourceCol = document.querySelector(`.source-column[data-column="${column}"]`);
            if (sourceCol) sourceCol.classList.remove('used');
        }
        delete columnMapping[field];
        updateFieldDisplay(field, null);
        checkMappingComplete();
    }

    function clearAllMappings() {
        Object.keys(columnMapping).forEach(field => {
            removeMapping(field);
        });
        document.querySelectorAll('.source-column').forEach(col => {
            col.classList.remove('used');
        });
    }

    clearMappingBtn.addEventListener('click', clearAllMappings);

    function checkMappingComplete() {
        // When AI is enabled, only SKU is required - AI fills everything else
        const aiEnabled = document.getElementById('aiGenerate').checked;
        const requiredFields = aiEnabled ? ['sku'] : ['sku', 'name', 'price'];
        const allMapped = requiredFields.every(f => columnMapping[f]);

        if (allMapped) {
            enableStep3();
        } else {
            disableStep3();
        }
    }

    // Auto-detect mapping
    autoMapBtn.addEventListener('click', () => {
        const columns = Object.keys(parsedData[0] || {});
        const mappings = {
            'sku': ['sku', 'product_sku', 'item_sku', 'code', 'product_code', 'item_code', 'barcode'],
            'name': ['name', 'product_name', 'title', 'product_title', 'item_name'],
            'price': ['price', 'product_price', 'selling_price', 'sale_price'],
            'description': ['description', 'product_description', 'desc', 'details', 'long_description'],
            'short_description': ['short_description', 'short_desc', 'summary', 'brief', 'short_descriptions'],
            'compare_price': ['compare_price', 'original_price', 'msrp', 'retail_price', 'was_price', 'rrp'],
            'cost_price': ['cost_price', 'cost', 'purchase_price', 'buying_price', 'supplier_price'],
            'stock': ['stock', 'stock_quantity', 'quantity', 'qty', 'inventory', 'stock_qty'],
            'category': ['category', 'categories', 'product_category', 'cat'],
            'brand': ['brand', 'brand_name', 'manufacturer', 'make'],
            'vendor': ['vendor', 'supplier', 'supplier_name', 'seller'],
            'image': ['image', 'image_url', 'photo', 'picture', 'img', 'image_link'],
            'weight': ['weight', 'product_weight', 'weight_kg', 'mass'],
            'status': ['status', 'product_status', 'active', 'enabled', 'availability']
        };

        columns.forEach(col => {
            const colLower = col.toLowerCase().replace(/[^a-z0-9]/g, '_');

            Object.keys(mappings).forEach(field => {
                if (!columnMapping[field]) {
                    const matches = mappings[field];
                    if (matches.includes(colLower) || matches.some(m => colLower.includes(m))) {
                        setMapping(field, col);
                    }
                }
            });
        });
    });

    // AI Generate checkbox - re-check required mappings when toggled
    document.getElementById('aiGenerate').addEventListener('change', () => {
        checkMappingComplete();
    });

    // AI Generate buttons
    document.querySelectorAll('.ai-generate-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const field = btn.dataset.aiField;
            if (!aiFields.includes(field)) {
                aiFields.push(field);
                btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><polyline points="20 6 9 17 4 12"></polyline></svg> AI';
            } else {
                aiFields = aiFields.filter(f => f !== field);
                btn.style.background = '';
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path></svg> AI';
            }
        });
    });

    function updatePreview() {
        if (parsedData.length === 0) return;

        const previewRows = parsedData.slice(0, 5);
        const mappedFields = Object.keys(columnMapping);

        let html = '<table class="preview-table"><thead><tr>';
        mappedFields.forEach(field => {
            html += `<th>${field}</th>`;
        });
        html += '</tr></thead><tbody>';

        previewRows.forEach(row => {
            html += '<tr>';
            mappedFields.forEach(field => {
                const col = columnMapping[field];
                const value = row[col] || '';
                html += `<td>${escapeHtml(value.substring(0, 50))}${value.length > 50 ? '...' : ''}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table>';
        html += `<p class="text-muted text-sm mt-4">Showing ${previewRows.length} of ${parsedData.length} rows</p>`;

        previewContainer.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Start Import
    startImportBtn.addEventListener('click', async () => {
        if (Object.keys(columnMapping).length === 0 || parsedData.length === 0) return;

        startImportBtn.disabled = true;
        document.getElementById('importProgress').style.display = 'block';

        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressCount = document.getElementById('progressCount');

        const options = {
            updateExisting: document.getElementById('updateExisting').checked,
            createNew: document.getElementById('createNew').checked,
            skipErrors: document.getElementById('skipErrors').checked,
            aiGenerate: document.getElementById('aiGenerate').checked,
            aiFields: aiFields
        };

        // Map data using column mapping
        const mappedData = parsedData.map(row => {
            const mapped = {};
            Object.keys(columnMapping).forEach(field => {
                mapped[field] = row[columnMapping[field]] || '';
            });
            return mapped;
        });

        const batchSize = options.aiGenerate ? 1 : 10; // 1 product at a time for AI (each call ~5-15s)
        const batches = [];
        for (let i = 0; i < mappedData.length; i += batchSize) {
            batches.push(mappedData.slice(i, i + batchSize));
        }

        let processed = 0;
        let created = 0;
        let updated = 0;
        let errors = [];

        for (let i = 0; i < batches.length; i++) {
            progressText.textContent = `Processing batch ${i + 1} of ${batches.length}...`;

            try {
                const formData = new FormData();
                formData.append('data', JSON.stringify(batches[i]));
                formData.append('update_existing', options.updateExisting ? '1' : '0');
                formData.append('create_new', options.createNew ? '1' : '0');
                formData.append('skip_errors', options.skipErrors ? '1' : '0');
                formData.append('ai_generate', options.aiGenerate ? '1' : '0');
                formData.append('ai_fields', JSON.stringify(options.aiFields));
                formData.append('_token', '<?= csrf_token() ?>');

                const response = await fetch('<?= url('/admin/products/import/process') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Check if response is OK
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Server response:', text);
                    errors.push('Server error: ' + response.status);
                    continue;
                }

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    errors.push('Invalid server response');
                    continue;
                }

                if (result.success) {
                    created += result.created || 0;
                    updated += result.updated || 0;
                    if (result.errors) errors = errors.concat(result.errors);
                } else {
                    errors.push(result.error || 'Unknown error');
                }
            } catch (err) {
                errors.push('Network error: ' + err.message);
            }

            processed += batches[i].length;
            const percent = Math.round((processed / mappedData.length) * 100);
            progressBar.style.width = percent + '%';
            progressCount.textContent = `${processed} / ${mappedData.length}`;
        }

        progressText.textContent = 'Import complete!';
        progressBar.style.width = '100%';
        progressBar.style.background = 'linear-gradient(90deg, #10b981, #059669)';

        // Show results
        let message = `Import completed!\n\nCreated: ${created}\nUpdated: ${updated}`;
        if (errors.length > 0) {
            message += `\n\nErrors (${errors.length}):\n${errors.slice(0, 5).join('\n')}`;
            if (errors.length > 5) message += `\n...and ${errors.length - 5} more`;
        }

        alert(message);

        setTimeout(() => {
            window.location.href = '<?= url('/admin/products') ?>';
        }, 1000);
    });
});
</script>
