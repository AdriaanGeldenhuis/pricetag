<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Homepage Builder</h1>
        <p class="admin-page-subtitle">Drag sections to reorder them on your homepage</p>
    </div>
    <div class="admin-page-actions">
        <button type="button" class="admin-btn admin-btn-primary" onclick="showAddSectionModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Section
        </button>
    </div>
</div>

<div class="admin-card">
    <div class="card-body">
        <?php if (empty($sections)): ?>
        <div class="text-center py-12">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="mx-auto mb-4 text-muted opacity-50">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            <p class="text-muted mb-4">No sections configured yet.</p>
            <button type="button" class="admin-btn admin-btn-primary" onclick="showAddSectionModal()">
                Add Your First Section
            </button>
        </div>
        <?php else: ?>
        <div id="sortableSections" class="space-y-3">
            <?php foreach ($sections as $section): ?>
            <div class="section-item" data-id="<?= $section['id'] ?>">
                <div class="flex items-center gap-4">
                    <div class="drag-handle cursor-move text-muted">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                            <line x1="8" y1="6" x2="8" y2="6.01"></line>
                            <line x1="16" y1="6" x2="16" y2="6.01"></line>
                            <line x1="8" y1="12" x2="8" y2="12.01"></line>
                            <line x1="16" y1="12" x2="16" y2="12.01"></line>
                            <line x1="8" y1="18" x2="8" y2="18.01"></line>
                            <line x1="16" y1="18" x2="16" y2="18.01"></line>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium"><?= e($section['title'] ?: ucwords(str_replace('_', ' ', $section['type']))) ?></h4>
                        <p class="text-sm text-muted"><?= e($section['type']) ?></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button"
                                onclick="toggleSection(<?= $section['id'] ?>)"
                                class="toggle-btn <?= $section['is_active'] ? 'active' : '' ?>"
                                title="<?= $section['is_active'] ? 'Click to deactivate' : 'Click to activate' ?>">
                            <span class="toggle-slider"></span>
                        </button>
                        <a href="<?= url('/admin/homepage/' . $section['id'] . '/edit') ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Edit
                        </a>
                        <button type="button" onclick="deleteSection(<?= $section['id'] ?>)" class="admin-btn admin-btn-sm admin-btn-danger-outline">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Section Modal -->
<div class="admin-modal-overlay" id="addSectionModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add Section</h3>
            <button class="admin-modal-close" onclick="hideAddSectionModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form action="<?= url('/admin/homepage') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="admin-modal-body">
                <div class="form-group">
                    <label class="form-label">Section Type</label>
                    <div class="section-type-grid">
                        <?php foreach ($sectionTypes ?? [] as $type => $info): ?>
                        <label class="section-type-option">
                            <input type="radio" name="type" value="<?= $type ?>" class="sr-only" required>
                            <div class="section-type-card">
                                <strong><?= e($info['name']) ?></strong>
                                <p class="text-sm text-muted"><?= e($info['description'] ?? '') ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="hideAddSectionModal()">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary">Add Section</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Section Modal -->
<div class="admin-modal-overlay" id="deleteSectionModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Section</h3>
            <button class="admin-modal-close" onclick="hideDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete this section? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="hideDeleteModal()">Cancel</button>
            <form id="deleteSectionForm" method="POST" style="display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Section</button>
            </form>
        </div>
    </div>
</div>

<style>
.section-item {
    padding: 1rem;
    background: var(--color-background);
    border: 1px solid var(--color-border-light);
    border-radius: var(--radius-lg);
    transition: all 0.2s;
}

.section-item:hover {
    border-color: var(--color-border);
    box-shadow: var(--shadow-sm);
}

.section-item.dragging {
    opacity: 0.5;
    background: var(--color-primary-50);
}

.toggle-btn {
    width: 44px;
    height: 24px;
    background: var(--color-gray-300);
    border: none;
    border-radius: 12px;
    position: relative;
    cursor: pointer;
    transition: background 0.2s;
}

.toggle-btn.active {
    background: var(--color-success);
}

.toggle-slider {
    position: absolute;
    left: 2px;
    top: 2px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.toggle-btn.active .toggle-slider {
    transform: translateX(20px);
}

.section-type-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
    max-height: 400px;
    overflow-y: auto;
}

.section-type-option input:checked + .section-type-card {
    border-color: var(--color-primary);
    background: var(--color-primary-50);
}

.section-type-card {
    padding: 0.75rem;
    border: 2px solid var(--color-border-light);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
}

.section-type-card:hover {
    border-color: var(--color-border);
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

.admin-btn-danger-outline {
    color: var(--color-danger);
    border: 1px solid var(--color-danger);
    background: transparent;
}

.admin-btn-danger-outline:hover {
    background: var(--color-danger);
    color: white;
}
</style>

<script>
// Add Section Modal
function showAddSectionModal() {
    document.getElementById('addSectionModal').classList.add('show');
}

function hideAddSectionModal() {
    document.getElementById('addSectionModal').classList.remove('show');
}

// Delete Section Modal
let deleteSectionId = null;

function deleteSection(id) {
    deleteSectionId = id;
    document.getElementById('deleteSectionForm').action = '<?= url('/admin/homepage/') ?>' + id;
    document.getElementById('deleteSectionModal').classList.add('show');
}

function hideDeleteModal() {
    document.getElementById('deleteSectionModal').classList.remove('show');
    deleteSectionId = null;
}

// Toggle Section
function toggleSection(id) {
    const btn = event.currentTarget;

    fetch('<?= url('/admin/homepage/') ?>' + id + '/toggle', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?= csrf_token() ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.classList.toggle('active', data.is_active);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Close modals on overlay click
document.querySelectorAll('.admin-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});

// Drag and drop reordering
const sortable = document.getElementById('sortableSections');
if (sortable) {
    let draggedItem = null;

    sortable.querySelectorAll('.section-item').forEach(item => {
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', function(e) {
            draggedItem = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedItem = null;
            saveOrder();
        });

        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            const afterElement = getDragAfterElement(sortable, e.clientY);
            if (afterElement == null) {
                sortable.appendChild(draggedItem);
            } else {
                sortable.insertBefore(draggedItem, afterElement);
            }
        });
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.section-item:not(.dragging)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function saveOrder() {
    const items = document.querySelectorAll('#sortableSections .section-item');
    const order = Array.from(items).map(item => item.dataset.id);

    fetch('<?= url('/admin/homepage/reorder') ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?= csrf_token() ?>',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'order[]=' + order.join('&order[]=')
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to save order');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
