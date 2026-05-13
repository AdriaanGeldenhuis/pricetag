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
    flex-shrink: 0;
}

.template-link-ai {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    border-color: transparent;
    color: #fff;
    cursor: pointer;
    font-family: inherit;
    padding: 0.75rem 1.25rem;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}
.template-link-ai:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35); }
.template-link-ai svg { width: 22px; height: 22px; }
.template-link-ai span { display: flex; flex-direction: column; align-items: flex-start; text-align: left; line-height: 1.2; }
.template-link-ai small { font-size: 0.7rem; opacity: 0.85; font-weight: 400; margin-top: 2px; }
.template-link-ai.active { background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35); }

.ai-settings {
    margin-top: 1rem; padding: 1.25rem;
    background: rgba(139, 92, 246, 0.06);
    border: 1px solid rgba(139, 92, 246, 0.25);
    border-radius: 8px;
}
.ai-settings-header h4 { margin: 0 0 0.25rem; font-size: 1rem; }
.ai-settings-header p { margin: 0 0 1rem; font-size: 0.8125rem; opacity: 0.75; }
.ai-settings-header code { background: rgba(0,0,0,0.2); padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.78rem; }
.ai-settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
.ai-settings-field { display: flex; flex-direction: column; gap: 0.35rem; }
.ai-settings-field label { font-size: 0.8125rem; font-weight: 600; }
.ai-settings-field input, .ai-settings-field select {
    padding: 0.5rem 0.75rem;
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 6px;
    color: inherit;
    font-size: 0.875rem;
}
.ai-settings-field small { font-size: 0.72rem; opacity: 0.6; }
.ai-settings-note {
    margin-top: 1rem; padding: 0.75rem 1rem;
    background: rgba(245, 158, 11, 0.08);
    border-left: 3px solid #f59e0b;
    border-radius: 6px; font-size: 0.8125rem;
}
.ai-fills-badge {
    background: rgba(37, 99, 235, 0.18);
    color: #93c5fd;
    padding: 0.1rem 0.45rem;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-left: 0.4rem;
}
.ai-required-badge {
    background: rgba(139, 92, 246, 0.2);
    color: #c4b5fd;
    padding: 0.1rem 0.45rem;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-left: 0.4rem;
}
.dry-run-result h4 { margin: 0 0 0.5rem; font-size: 0.9rem; }
.dry-run-result h4 span { font-weight: 400; font-size: 0.75rem; opacity: 0.65; margin-left: 0.5rem; }
.dry-run-result pre {
    margin: 0; padding: 0.75rem;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 6px;
    font-size: 0.72rem; line-height: 1.4;
    max-height: 400px; overflow: auto;
    white-space: pre-wrap; word-break: break-word;
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
                <button type="button" id="aiGenerateBtn" class="template-link template-link-ai" title="Import using only SKU + Vendor + Cost + Category - AI fills the rest">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                    <span>
                        <strong>AI Generate from SKU</strong>
                        <small>Only SKU, Vendor, Cost &amp; Category needed - AI builds the rest</small>
                    </span>
                </button>
                <a href="<?= url('/admin/products/import/template') ?>" class="template-link">
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
                        <div class="target-field" data-field="name" id="targetName">
                            <span class="target-field-name">Name <span class="required" id="nameRequiredBadge">*</span><span class="ai-fills-badge" id="nameAiBadge" style="display:none;">AI fills this</span></span>
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
                        <div class="target-field" data-field="cost_price" id="targetCostPrice">
                            <span class="target-field-name">Cost Price (Excl VAT)<span class="ai-required-badge" id="costPriceRequiredBadge" style="display:none;">AI mode</span></span>
                            <div class="target-field-value" data-drop="cost_price"></div>
                        </div>
                        <div class="target-field" data-field="stock">
                            <span class="target-field-name">Stock Quantity</span>
                            <div class="target-field-value" data-drop="stock"></div>
                        </div>
                        <div class="target-field" data-field="category" id="targetCategory">
                            <span class="target-field-name">Category<span class="ai-required-badge" id="categoryRequiredBadge" style="display:none;">AI mode</span></span>
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

            <!-- AI Pricing & Vendor Settings (visible only when AI mode is on) -->
            <div class="ai-settings" id="aiSettings" style="display:none;">
                <div class="ai-settings-header">
                    <h4>AI Import Settings</h4>
                    <p>Sell price = <code>Cost (excl VAT) &times; (1 + Margin%) &times; (1 + VAT%)</code></p>
                </div>
                <div class="ai-settings-grid">
                    <div class="ai-settings-field">
                        <label for="marginPercent">Profit Margin %</label>
                        <input type="number" id="marginPercent" min="0" max="500" step="0.01" value="25" placeholder="25">
                        <small>Applied to cost (excl VAT) before adding VAT</small>
                    </div>
                    <div class="ai-settings-field">
                        <label for="vatRate">VAT Rate %</label>
                        <input type="number" id="vatRate" min="0" max="100" step="0.01" value="<?= e((string) ($taxRate ?? 15)) ?>">
                        <small>From store settings - editable per import</small>
                    </div>
                    <div class="ai-settings-field">
                        <label for="defaultVendor">Default Vendor</label>
                        <select id="defaultVendor">
                            <option value="">- Use vendor from row -</option>
                            <?php foreach (($vendors ?? []) as $_v): ?>
                                <option value="<?= e((string) $_v['id']) ?>"><?= e($_v['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small>Used when row has no vendor column</small>
                    </div>
                    <div class="ai-settings-field">
                        <label for="defaultCategory">Default Category</label>
                        <select id="defaultCategory">
                            <option value="">- Use category from row -</option>
                            <?php foreach (($categories ?? []) as $_c): ?>
                                <option value="<?= e((string) $_c['id']) ?>"><?= e($_c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small>Used when row has no category column</small>
                    </div>
                </div>
                <div class="ai-settings-note">
                    <strong>Unknown SKU behavior:</strong> If AI cannot identify the product, it will still be created with SKU + cost + vendor + category - status will be <em>draft</em> (not visible to customers) so you can review later.
                </div>
                <button type="button" class="btn btn-secondary" id="dryRunBtn" style="margin-top:1rem;" disabled>
                    Test First Row (AI dry-run)
                </button>
                <div class="dry-run-result" id="dryRunResult" style="display:none;margin-top:1rem;">
                    <h4>Dry-run result <span id="dryRunStatus"></span></h4>
                    <pre id="dryRunOutput"></pre>
                    <img id="dryRunImage" alt="" style="display:none;max-width:200px;border:1px solid var(--admin-border);border-radius:4px;margin-top:0.5rem;">
                </div>
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

            <div class="import-results" id="importResults" style="display: none; margin-top: 24px;">
                <div class="results-summary" style="display: flex; gap: 16px; margin-bottom: 16px;">
                    <div style="flex:1; padding: 12px 16px; border-radius: 8px; background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.4);">
                        <div style="font-size: 12px; opacity: .8;">Created</div>
                        <div id="resultsCreated" style="font-size: 24px; font-weight: 700;">0</div>
                    </div>
                    <div style="flex:1; padding: 12px 16px; border-radius: 8px; background: rgba(59,130,246,0.12); border: 1px solid rgba(59,130,246,0.4);">
                        <div style="font-size: 12px; opacity: .8;">Updated</div>
                        <div id="resultsUpdated" style="font-size: 24px; font-weight: 700;">0</div>
                    </div>
                    <div style="flex:1; padding: 12px 16px; border-radius: 8px; background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.4);">
                        <div style="font-size: 12px; opacity: .8;">Errors</div>
                        <div id="resultsErrorCount" style="font-size: 24px; font-weight: 700;">0</div>
                    </div>
                </div>
                <div id="resultsErrorsPanel" style="display: none;">
                    <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <h4 style="margin:0;">Error details</h4>
                        <button type="button" id="exportErrorsBtn" class="btn btn-sm btn-secondary">Export errors as CSV</button>
                    </div>
                    <div style="max-height: 320px; overflow-y: auto; border: 1px solid var(--admin-border, #2a2a3a); border-radius: 8px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead style="position: sticky; top: 0; background: var(--admin-bg-elevated, #1a1a2a);">
                                <tr>
                                    <th style="padding: 8px 12px; text-align: left; border-bottom: 1px solid var(--admin-border, #2a2a3a); width: 60px;">Row</th>
                                    <th style="padding: 8px 12px; text-align: left; border-bottom: 1px solid var(--admin-border, #2a2a3a);">Message</th>
                                </tr>
                            </thead>
                            <tbody id="resultsErrorsTbody"></tbody>
                        </table>
                    </div>
                </div>
                <div style="margin-top: 16px; display: flex; gap: 8px;">
                    <a href="<?= url('/admin/products') ?>" class="btn btn-primary">View product list</a>
                    <button type="button" class="btn btn-secondary" onclick="location.reload()">Run another import</button>
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

    <?php if (!empty($history)): ?>
    <!-- Recent Imports -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header"><h3 class="card-title">Recent Imports</h3></div>
        <div class="card-body" style="padding: 0;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                        <th style="text-align:left;padding:0.75rem 1rem;font-size:0.8rem;opacity:0.75;">When</th>
                        <th style="text-align:left;padding:0.75rem 1rem;font-size:0.8rem;opacity:0.75;">Source</th>
                        <th style="text-align:right;padding:0.75rem 1rem;font-size:0.8rem;opacity:0.75;">Created</th>
                        <th style="text-align:right;padding:0.75rem 1rem;font-size:0.8rem;opacity:0.75;">Updated</th>
                        <th style="text-align:right;padding:0.75rem 1rem;font-size:0.8rem;opacity:0.75;">Failed</th>
                        <th style="padding:0.75rem 1rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $_h): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:0.6rem 1rem;font-size:0.8rem;opacity:0.75;"><?= e($_h['created_at']) ?></td>
                            <td style="padding:0.6rem 1rem;"><?= e($_h['filename'] ?? '-') ?></td>
                            <td style="padding:0.6rem 1rem;text-align:right;"><?= (int) ($_h['created_products'] ?? 0) ?></td>
                            <td style="padding:0.6rem 1rem;text-align:right;"><?= (int) ($_h['updated_products'] ?? 0) ?></td>
                            <td style="padding:0.6rem 1rem;text-align:right;"><?= (int) ($_h['failed_products'] ?? 0) ?></td>
                            <td style="padding:0.6rem 1rem;text-align:right;">
                                <a href="<?= url('/admin/products/import/history/' . (int) $_h['id']) ?>" class="btn btn-sm btn-outline">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

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
        const dryRunBtn = document.getElementById('dryRunBtn');
        if (dryRunBtn) dryRunBtn.disabled = !document.getElementById('aiGenerate').checked;
        updatePreview();
    }

    function disableStep3() {
        step3.classList.add('disabled');
        step2.classList.remove('completed');
        startImportBtn.disabled = true;
        const dryRunBtn = document.getElementById('dryRunBtn');
        if (dryRunBtn) dryRunBtn.disabled = true;
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
    function setAiMode(on) {
        const aiCheckbox = document.getElementById('aiGenerate');
        const aiSettings = document.getElementById('aiSettings');
        const aiBtn = document.getElementById('aiGenerateBtn');
        const dryRunBtn = document.getElementById('dryRunBtn');
        const nameRequired = document.getElementById('nameRequiredBadge');
        const nameAi = document.getElementById('nameAiBadge');
        const costBadge = document.getElementById('costPriceRequiredBadge');
        const catBadge = document.getElementById('categoryRequiredBadge');

        aiCheckbox.checked = on;
        aiSettings.style.display = on ? 'block' : 'none';
        if (aiBtn) aiBtn.classList.toggle('active', on);
        if (dryRunBtn) dryRunBtn.disabled = !on || typeof parsedData === 'undefined' || !parsedData;
        if (nameRequired) nameRequired.style.display = on ? 'none' : 'inline';
        if (nameAi) nameAi.style.display = on ? 'inline-block' : 'none';
        if (costBadge) costBadge.style.display = on ? 'inline-block' : 'none';
        if (catBadge) catBadge.style.display = on ? 'inline-block' : 'none';
        checkMappingComplete();
    }

    document.getElementById('aiGenerate').addEventListener('change', e => setAiMode(e.target.checked));
    if (document.getElementById('aiGenerateBtn')) {
        document.getElementById('aiGenerateBtn').addEventListener('click', () => {
            setAiMode(!document.getElementById('aiGenerate').checked);
            const step3 = document.getElementById('step3');
            if (step3) step3.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    // Dry-run: run AI on first row and show the result without saving anything
    if (document.getElementById('dryRunBtn')) {
        document.getElementById('dryRunBtn').addEventListener('click', async () => {
            if (typeof parsedData === 'undefined' || !parsedData || !parsedData.length) return;
            const btn = document.getElementById('dryRunBtn');
            const result = document.getElementById('dryRunResult');
            const status = document.getElementById('dryRunStatus');
            const out = document.getElementById('dryRunOutput');
            const img = document.getElementById('dryRunImage');

            const original = btn.textContent;
            btn.disabled = true; btn.textContent = 'Testing first row...';
            result.style.display = 'block';
            status.textContent = '(running...)';
            out.textContent = '';
            img.style.display = 'none';

            const row = {};
            Object.keys(columnMapping || {}).forEach(field => {
                row[field] = parsedData[0][columnMapping[field]] || '';
            });

            const fd = new FormData();
            fd.append('_token', '<?= csrf_token() ?>');
            fd.append('row', JSON.stringify(row));
            fd.append('margin_percent', document.getElementById('marginPercent').value || '0');
            fd.append('vat_rate', document.getElementById('vatRate').value || '0');

            try {
                const res = await fetch('<?= url('/admin/products/import/dry-run') ?>', { method: 'POST', body: fd });
                const json = await res.json();
                status.textContent = json.success
                    ? '(' + (json.ai_service || 'ai') + ', method: ' + (json.method || '?') + ')'
                    : '(FAILED: ' + (json.error || json.fallback_reason || 'unknown') + ')';
                out.textContent = JSON.stringify(json, null, 2);
                if (json.preview && json.preview.image_url) {
                    img.src = json.preview.image_url;
                    img.style.display = 'block';
                }
            } catch (e) {
                status.textContent = '(network error)';
                out.textContent = e.message;
            } finally {
                btn.textContent = original;
                btn.disabled = false;
            }
        });
    }

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
            aiFields: aiFields,
            marginPercent: document.getElementById('marginPercent') ? document.getElementById('marginPercent').value || '0' : '0',
            vatRate: document.getElementById('vatRate') ? document.getElementById('vatRate').value || '0' : '0',
            defaultVendorId: document.getElementById('defaultVendor') ? document.getElementById('defaultVendor').value || '' : '',
            defaultCategoryId: document.getElementById('defaultCategory') ? document.getElementById('defaultCategory').value || '' : '',
        };

        // Map data using column mapping
        const mappedData = parsedData.map(row => {
            const mapped = {};
            Object.keys(columnMapping).forEach(field => {
                mapped[field] = row[columnMapping[field]] || '';
            });
            return mapped;
        });

        // Large AI imports run in the background queue so the user can close the tab
        if (options.aiGenerate && mappedData.length > 50) {
            return runQueuedImport(mappedData, options);
        }

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
                formData.append('margin_percent', options.marginPercent);
                formData.append('vat_rate', options.vatRate);
                formData.append('default_vendor_id', options.defaultVendorId);
                formData.append('default_category_id', options.defaultCategoryId);
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

        // Render the full results panel instead of a truncated alert().
        // Errors are shown in a scrollable table so the admin can fix
        // them without copy-pasting from a browser dialog. Export-to-CSV
        // is right there for sharing with a supplier or another admin.
        document.getElementById('resultsCreated').textContent = created;
        document.getElementById('resultsUpdated').textContent = updated;
        document.getElementById('resultsErrorCount').textContent = errors.length;

        const errorsPanel = document.getElementById('resultsErrorsPanel');
        const tbody = document.getElementById('resultsErrorsTbody');
        tbody.innerHTML = '';

        if (errors.length > 0) {
            errorsPanel.style.display = 'block';
            errors.forEach(err => {
                const m = err.match(/^Row (\d+): (.+)$/);
                const tr = document.createElement('tr');
                const tdRow = document.createElement('td');
                tdRow.style.cssText = 'padding: 6px 12px; border-bottom: 1px solid var(--admin-border, #2a2a3a); font-family: monospace;';
                const tdMsg = document.createElement('td');
                tdMsg.style.cssText = 'padding: 6px 12px; border-bottom: 1px solid var(--admin-border, #2a2a3a);';
                if (m) {
                    tdRow.textContent = m[1];
                    tdMsg.textContent = m[2];
                } else {
                    tdRow.textContent = '-';
                    tdMsg.textContent = err;
                }
                tr.appendChild(tdRow);
                tr.appendChild(tdMsg);
                tbody.appendChild(tr);
            });

            document.getElementById('exportErrorsBtn').onclick = () => {
                const rows = [['Row', 'Message']];
                errors.forEach(err => {
                    const m = err.match(/^Row (\d+): (.+)$/);
                    rows.push(m ? [m[1], m[2]] : ['', err]);
                });
                const csv = rows.map(r => r.map(c => '"' + String(c).replace(/"/g, '""') + '"').join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'import-errors-' + new Date().toISOString().slice(0, 10) + '.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            };
        } else {
            errorsPanel.style.display = 'none';
        }

        document.getElementById('importResults').style.display = 'block';
        document.getElementById('importResults').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // Background queue path: large AI imports hand off to a background job
    // so the user can close the tab. Polls every 3s for progress.
    async function runQueuedImport(mappedData, options) {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressCount = document.getElementById('progressCount');
        progressText.textContent = 'Queueing import...';

        const fd = new FormData();
        fd.append('_token', '<?= csrf_token() ?>');
        fd.append('data', JSON.stringify(mappedData));
        fd.append('update_existing', options.updateExisting ? '1' : '0');
        fd.append('create_new', options.createNew ? '1' : '0');
        fd.append('skip_errors', '1');
        fd.append('ai_generate', '1');
        fd.append('margin_percent', options.marginPercent);
        fd.append('vat_rate', options.vatRate);
        fd.append('default_vendor_id', options.defaultVendorId);
        fd.append('default_category_id', options.defaultCategoryId);

        try {
            const res = await fetch('<?= url('/admin/products/import/enqueue') ?>', { method: 'POST', body: fd });
            const json = await res.json();
            if (!json.success) {
                progressText.textContent = 'Failed to queue: ' + (json.error || 'unknown');
                document.getElementById('startImportBtn').disabled = false;
                return;
            }
            progressText.textContent = 'Import queued (job #' + json.job_id + '). Running in background...';
            progressCount.textContent = '0 / ' + json.total_rows;
            pollJobStatus(json.job_id);
        } catch (e) {
            progressText.textContent = 'Failed to queue: ' + e.message;
            document.getElementById('startImportBtn').disabled = false;
        }
    }

    async function pollJobStatus(jobId) {
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressCount = document.getElementById('progressCount');
        const tick = async () => {
            try {
                const res = await fetch('<?= url('/admin/products/import/jobs') ?>/' + jobId);
                const json = await res.json();
                if (!json.success) {
                    progressText.textContent = 'Status check failed: ' + (json.error || 'unknown');
                    return;
                }
                const job = json.job;
                progressBar.style.width = job.progress_pct + '%';
                progressText.textContent = 'Job #' + job.id + ' - ' + job.status;
                progressCount.textContent = job.processed_rows + ' / ' + job.total_rows;
                if (job.status === 'completed') {
                    progressText.textContent = 'Import completed!';
                    progressBar.style.width = '100%';
                    const errorsField = (job.errors && job.errors.length) ? job.errors : [];
                    if (typeof showResults === 'function') {
                        showResults({ created: job.created, updated: job.updated, errors: errorsField, import_log_id: job.import_log_id });
                    } else {
                        document.getElementById('resultsCreated').textContent = job.created;
                        document.getElementById('resultsUpdated').textContent = job.updated;
                        document.getElementById('importResults').style.display = 'block';
                    }
                    return;
                }
                if (job.status === 'failed' || job.status === 'cancelled') {
                    progressText.textContent = job.status === 'failed'
                        ? 'Import failed: ' + (job.error_message || 'unknown')
                        : 'Import cancelled';
                    document.getElementById('startImportBtn').disabled = false;
                    return;
                }
                setTimeout(tick, 3000);
            } catch (e) {
                progressText.textContent = 'Status check error: ' + e.message;
                setTimeout(tick, 5000);
            }
        };
        tick();
    }
});
</script>
