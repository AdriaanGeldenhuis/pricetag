<!-- Admin Attributes Page -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Attributes</h1>
        <p class="admin-page-subtitle">Manage product attributes like size, color, material</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openAttributeModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Attribute
    </button>
</div>

<?php if (empty($attributes)): ?>
<div class="card">
    <div class="card-body text-center py-8">
        <div class="empty-icon mb-4">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48" style="opacity: 0.5">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
            </svg>
        </div>
        <h3 class="font-semibold mb-2">No attributes yet</h3>
        <p class="text-muted mb-4">Create attributes to add product variations like size, color, and more.</p>
        <button type="button" class="btn btn-primary" onclick="openAttributeModal()">Create First Attribute</button>
    </div>
</div>
<?php else: ?>
<div class="attributes-grid">
    <?php foreach ($attributes as $attr):
        $productCount = $attr['product_count'] ?? 0;
        $valueCount = $attr['value_count'] ?? 0;
    ?>
    <div class="attribute-card card" data-id="<?= $attr['id'] ?>">
        <div class="card-body">
            <div class="attribute-header">
                <div class="attribute-info">
                    <h3 class="attribute-name"><?= e($attr['name']) ?></h3>
                    <span class="attribute-type badge badge-<?= $attr['type'] === 'color' ? 'primary' : 'secondary' ?>">
                        <?= ucfirst($attr['type']) ?>
                    </span>
                </div>
                <div class="attribute-actions">
                    <button type="button" class="btn btn-sm btn-ghost" onclick="editAttribute(<?= $attr['id'] ?>)" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                    <?php if ($productCount == 0): ?>
                    <button type="button" class="btn btn-sm btn-ghost text-danger" onclick="deleteAttribute(<?= $attr['id'] ?>)" title="Delete">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="attribute-meta">
                <span><?= $valueCount ?> values</span>
                <span><?= $productCount ?> products</span>
                <?php if (!empty($attr['is_filterable'])): ?>
                <span class="badge badge-success">Filterable</span>
                <?php endif; ?>
            </div>

            <div class="attribute-values">
                <?php if (empty($attributeValues[$attr['id']])): ?>
                <p class="text-muted text-sm">No values added yet</p>
                <?php else: ?>
                <div class="values-list">
                    <?php foreach ($attributeValues[$attr['id']] as $value): ?>
                    <span class="value-tag" data-id="<?= $value['id'] ?>">
                        <?php if ($attr['type'] === 'color' && !empty($value['color_code'])): ?>
                        <span class="color-dot" style="background-color: <?= e($value['color_code']) ?>"></span>
                        <?php endif; ?>
                        <?= e($value['value']) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <button type="button" class="btn btn-sm btn-outline w-full mt-4" onclick="manageValues(<?= $attr['id'] ?>)">
                Manage Values
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Attribute Modal -->
<div class="admin-modal-overlay" id="attributeModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="attributeModalTitle">Add Attribute</h3>
            <button type="button" class="admin-modal-close" onclick="closeAttributeModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form id="attributeForm" onsubmit="saveAttribute(event)">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="attributeId">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="attributeName" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" id="attributeType" class="form-select" required>
                        <option value="select">Select (Dropdown)</option>
                        <option value="color">Color</option>
                        <option value="size">Size</option>
                        <option value="text">Text</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_filterable" id="attributeFilterable" value="1" checked class="form-checkbox">
                        <span>Show in filters</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_visible" id="attributeVisible" value="1" checked class="form-checkbox">
                        <span>Visible on product page</span>
                    </label>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAttributeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Attribute</button>
            </div>
        </form>
    </div>
</div>

<!-- Values Modal -->
<div class="admin-modal-overlay" id="valuesModal">
    <div class="admin-modal lg">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="valuesModalTitle">Manage Values</h3>
            <button type="button" class="admin-modal-close" onclick="closeValuesModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <div id="valuesList" class="values-editor"></div>
            <div class="add-value-form">
                <div class="add-value-row">
                    <input type="text" id="newValueInput" class="form-input" placeholder="Enter value...">
                    <input type="color" id="newColorInput" class="form-input-color" value="#3b82f6" style="display:none;">
                    <button type="button" class="btn btn-primary" onclick="addValue()">Add Value</button>
                </div>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeValuesModal()">Close</button>
        </div>
    </div>
</div>

<style>
/* Attributes Grid */
.attributes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.attribute-card {
    background: var(--admin-bg-card);
    border: 1px solid var(--admin-glass-border);
    border-radius: var(--admin-radius);
}

.attribute-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.attribute-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.attribute-name {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0;
    color: var(--admin-text);
}

.attribute-actions {
    display: flex;
    gap: 0.25rem;
}

.attribute-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.8125rem;
    color: var(--admin-text-muted);
    margin-bottom: 1rem;
}

.attribute-values {
    min-height: 40px;
}

.values-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.value-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    background: var(--admin-bg-elevated);
    border-radius: 4px;
    font-size: 0.8125rem;
    color: var(--admin-text);
}

.color-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.2);
    flex-shrink: 0;
}

/* Admin Modal overrides for attributes page */

/* Values Editor */
.values-editor {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 1.5rem;
}

.value-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--admin-bg-elevated);
    border-radius: var(--admin-radius);
    margin-bottom: 0.5rem;
}

.value-item:last-child {
    margin-bottom: 0;
}

.value-item .form-input {
    flex: 1;
}

.add-value-form {
    padding-top: 1rem;
    border-top: 1px solid var(--admin-glass-border);
}

.add-value-row {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.add-value-row .form-input {
    flex: 1;
}

.form-input-color {
    width: 48px;
    height: 40px;
    padding: 4px;
    border: 1px solid var(--admin-border);
    border-radius: var(--admin-radius);
    cursor: pointer;
    background: var(--admin-bg-card);
}

/* Empty state icon */
.empty-icon {
    display: flex;
    justify-content: center;
    color: var(--admin-text-muted);
}
</style>

<script>
let currentAttributeId = null;
let currentAttributeType = 'select';
const attributeValues = <?= json_encode($attributeValues ?? []) ?>;

function openAttributeModal(id = null) {
    document.getElementById('attributeModalTitle').textContent = id ? 'Edit Attribute' : 'Add Attribute';
    document.getElementById('attributeForm').reset();
    document.getElementById('attributeId').value = id || '';
    document.getElementById('attributeFilterable').checked = true;
    document.getElementById('attributeVisible').checked = true;

    if (id) {
        const card = document.querySelector(`.attribute-card[data-id="${id}"]`);
        if (card) {
            document.getElementById('attributeName').value = card.querySelector('.attribute-name').textContent.trim();
            const typeText = card.querySelector('.attribute-type').textContent.toLowerCase().trim();
            document.getElementById('attributeType').value = typeText;
        }
    }

    document.getElementById('attributeModal').classList.add('show');
}

function closeAttributeModal() {
    document.getElementById('attributeModal').classList.remove('show');
}

function editAttribute(id) {
    openAttributeModal(id);
}

async function saveAttribute(e) {
    e.preventDefault();
    const form = e.target;
    const id = document.getElementById('attributeId').value;
    const url = id ? `<?= url('/admin/attributes/') ?>${id}` : '<?= url('/admin/attributes') ?>';

    try {
        const formData = new FormData(form);
        if (id) formData.append('_method', 'PUT');

        const response = await fetch(url, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to save attribute');
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred');
    }
}

async function deleteAttribute(id) {
    if (!confirm('Are you sure you want to delete this attribute?')) return;

    try {
        const response = await fetch(`<?= url('/admin/attributes/') ?>${id}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `_token=<?= csrf_token() ?>&_method=DELETE`,
        });

        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Failed to delete attribute');
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred');
    }
}

function manageValues(id) {
    currentAttributeId = id;
    const card = document.querySelector(`.attribute-card[data-id="${id}"]`);
    currentAttributeType = card.querySelector('.attribute-type').textContent.toLowerCase().trim();

    document.getElementById('valuesModalTitle').textContent = `Manage Values - ${card.querySelector('.attribute-name').textContent.trim()}`;
    document.getElementById('newColorInput').style.display = currentAttributeType === 'color' ? 'block' : 'none';

    renderValues();
    document.getElementById('valuesModal').classList.add('show');
}

function closeValuesModal() {
    document.getElementById('valuesModal').classList.remove('show');
}

function renderValues() {
    const values = attributeValues[currentAttributeId] || [];
    const container = document.getElementById('valuesList');

    if (values.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-4">No values yet. Add one below.</p>';
        return;
    }

    const html = values.map(v => `
        <div class="value-item" data-id="${v.id}">
            ${currentAttributeType === 'color' ? `<input type="color" value="${v.color_code || '#000000'}" class="form-input-color" onchange="updateValueColor(${v.id}, this.value)">` : ''}
            <input type="text" value="${escapeHtml(v.value)}" class="form-input" onchange="updateValue(${v.id}, this.value)">
            <button type="button" class="btn btn-sm btn-ghost text-danger" onclick="deleteValue(${v.id})">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    `).join('');

    container.innerHTML = html;
}

async function addValue() {
    const input = document.getElementById('newValueInput');
    const value = input.value.trim();
    if (!value) return;

    const colorInput = document.getElementById('newColorInput');
    const color = currentAttributeType === 'color' ? colorInput.value : null;

    try {
        const response = await fetch('<?= url('/admin/attributes/values') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `_token=<?= csrf_token() ?>&attribute_id=${currentAttributeId}&value=${encodeURIComponent(value)}&color_code=${color || ''}`,
        });

        const data = await response.json();

        if (data.success) {
            attributeValues[currentAttributeId] = attributeValues[currentAttributeId] || [];
            attributeValues[currentAttributeId].push({id: data.id, value: value, color_code: color});
            renderValues();
            input.value = '';
        } else {
            alert(data.error || 'Failed to add value');
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred');
    }
}

async function updateValue(id, value) {
    try {
        const response = await fetch(`<?= url('/admin/attributes/values/') ?>${id}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `_token=<?= csrf_token() ?>&_method=PUT&value=${encodeURIComponent(value)}`,
        });

        const data = await response.json();
        if (!data.success) {
            alert(data.error || 'Failed to update value');
        }
    } catch (error) {
        console.error(error);
    }
}

async function updateValueColor(id, color) {
    try {
        const response = await fetch(`<?= url('/admin/attributes/values/') ?>${id}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `_token=<?= csrf_token() ?>&_method=PUT&color_code=${encodeURIComponent(color)}`,
        });

        const data = await response.json();
        if (!data.success) {
            alert(data.error || 'Failed to update color');
        }
    } catch (error) {
        console.error(error);
    }
}

async function deleteValue(id) {
    if (!confirm('Delete this value?')) return;

    try {
        const response = await fetch(`<?= url('/admin/attributes/values/') ?>${id}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `_token=<?= csrf_token() ?>&_method=DELETE`,
        });

        const data = await response.json();

        if (data.success) {
            attributeValues[currentAttributeId] = attributeValues[currentAttributeId].filter(v => v.id !== id);
            renderValues();
        } else {
            alert(data.error || 'Failed to delete value');
        }
    } catch (error) {
        console.error(error);
        alert('An error occurred');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modals on overlay click
document.querySelectorAll('.admin-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('show');
        }
    });
});

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.admin-modal-overlay.show').forEach(modal => {
            modal.classList.remove('show');
        });
    }
});
</script>
