<!-- Admin Homepage Builder -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Homepage Builder</h1>
        <p class="admin-page-subtitle">Drag and drop sections to customize your homepage layout</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/') ?>" target="_blank" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
            </svg>
            Preview Site
        </a>
        <button type="button" onclick="showAddSectionModal()" class="admin-btn admin-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Section
        </button>
    </div>
</div>

<!-- Sections List -->
<div class="homepage-builder">
    <?php if (empty($sections)): ?>
    <div class="admin-card">
        <div class="admin-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            <h3 class="admin-empty-title">No sections configured</h3>
            <p class="admin-empty-text">Start building your homepage by adding sections.</p>
            <button type="button" onclick="showAddSectionModal()" class="admin-btn admin-btn-primary">Add Your First Section</button>
        </div>
    </div>
    <?php else: ?>

    <div class="sections-list" id="sectionsList">
        <?php foreach ($sections as $section): ?>
        <div class="section-card <?= !$section['is_active'] ? 'is-disabled' : '' ?>" data-id="<?= $section['id'] ?>">
            <div class="section-handle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <line x1="8" y1="6" x2="16" y2="6"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                    <line x1="8" y1="18" x2="16" y2="18"></line>
                </svg>
            </div>

            <div class="section-icon">
                <?= $this->getSectionIcon($section['type']) ?>
            </div>

            <div class="section-info">
                <h3 class="section-title">
                    <?= e($section['title'] ?: $sectionTypes[$section['type']]['name'] ?? ucfirst($section['type'])) ?>
                </h3>
                <p class="section-description">
                    <?= e($sectionTypes[$section['type']]['description'] ?? '') ?>
                </p>
                <?php if ($section['subtitle']): ?>
                <p class="section-subtitle"><?= e($section['subtitle']) ?></p>
                <?php endif; ?>
            </div>

            <div class="section-status">
                <?php if ($section['is_active']): ?>
                <span class="admin-badge active">Active</span>
                <?php else: ?>
                <span class="admin-badge inactive">Inactive</span>
                <?php endif; ?>
            </div>

            <div class="section-actions">
                <button type="button" onclick="toggleSection(<?= $section['id'] ?>, this)"
                        class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon"
                        title="<?= $section['is_active'] ? 'Disable' : 'Enable' ?>">
                    <?php if ($section['is_active']): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                    <?php endif; ?>
                </button>

                <a href="<?= url('/admin/homepage/' . $section['id'] . '/edit') ?>"
                   class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon" title="Edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </a>

                <button type="button" onclick="deleteSection(<?= $section['id'] ?>)"
                        class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon text-danger" title="Delete">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                    </svg>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<!-- Add Section Modal -->
<div class="admin-modal-overlay" id="addSectionModal">
    <div class="admin-modal admin-modal-lg">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add Section</h3>
            <button class="admin-modal-close" onclick="closeAddSectionModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p class="text-muted mb-4">Choose a section type to add to your homepage</p>
            <div class="section-types-grid">
                <?php foreach ($sectionTypes as $type => $info): ?>
                <form action="<?= url('/admin/homepage') ?>" method="POST" class="section-type-card">
                    <?= csrf_field() ?>
                    <input type="hidden" name="type" value="<?= $type ?>">
                    <div class="section-type-icon">
                        <?= $this->getSectionIcon($type) ?>
                    </div>
                    <div class="section-type-info">
                        <h4><?= e($info['name']) ?></h4>
                        <p><?= e($info['description']) ?></p>
                    </div>
                    <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm">Add</button>
                </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="admin-modal-overlay" id="deleteModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Section</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
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
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Section</button>
            </form>
        </div>
    </div>
</div>

<style>
.homepage-builder {
    max-width: 900px;
}

.sections-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.section-card {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4);
    background: var(--color-background);
    border: 1px solid var(--color-border-light);
    border-radius: var(--radius-lg);
    transition: var(--transition-all);
}

.section-card:hover {
    box-shadow: var(--shadow-md);
}

.section-card.is-disabled {
    opacity: 0.6;
    background: var(--color-background-alt);
}

.section-card.dragging {
    opacity: 0.5;
    box-shadow: var(--shadow-lg);
}

.section-handle {
    cursor: grab;
    color: var(--color-text-muted);
    padding: var(--space-2);
}

.section-handle:active {
    cursor: grabbing;
}

.section-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary-50, #eff6ff);
    color: var(--color-primary);
    border-radius: var(--radius-lg);
    flex-shrink: 0;
}

.section-icon svg {
    width: 24px;
    height: 24px;
}

.section-info {
    flex: 1;
    min-width: 0;
}

.section-title {
    font-size: var(--text-base);
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-1);
}

.section-description {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    margin: 0;
}

.section-subtitle {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
    margin-top: var(--space-1);
    font-style: italic;
}

.section-status {
    flex-shrink: 0;
}

.section-actions {
    display: flex;
    gap: var(--space-1);
    flex-shrink: 0;
}

.section-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-3);
}

.section-type-card {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3);
    border: 1px solid var(--color-border-light);
    border-radius: var(--radius-lg);
    transition: var(--transition-all);
}

.section-type-card:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-50, #eff6ff);
}

.section-type-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-background-alt);
    border-radius: var(--radius-md);
    flex-shrink: 0;
}

.section-type-icon svg {
    width: 20px;
    height: 20px;
}

.section-type-info {
    flex: 1;
    min-width: 0;
}

.section-type-info h4 {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    margin-bottom: var(--space-1);
}

.section-type-info p {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
    margin: 0;
}

.admin-modal-lg {
    max-width: 700px;
}
</style>

<script>
// Modals
function showAddSectionModal() {
    document.getElementById('addSectionModal').classList.add('show');
}

function closeAddSectionModal() {
    document.getElementById('addSectionModal').classList.remove('show');
}

function deleteSection(id) {
    document.getElementById('deleteForm').action = '/admin/homepage/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.querySelectorAll('.admin-modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});

// Toggle section
async function toggleSection(id, button) {
    try {
        const response = await fetch('/admin/homepage/' + id + '/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= csrf_token() ?>'
            }
        });

        const data = await response.json();
        if (data.success) {
            const card = button.closest('.section-card');
            const badge = card.querySelector('.section-status .admin-badge');

            if (data.is_active) {
                card.classList.remove('is-disabled');
                badge.className = 'admin-badge active';
                badge.textContent = 'Active';
            } else {
                card.classList.add('is-disabled');
                badge.className = 'admin-badge inactive';
                badge.textContent = 'Inactive';
            }
        }
    } catch (error) {
        console.error('Error toggling section:', error);
    }
}

// Drag and drop reordering
const sectionsList = document.getElementById('sectionsList');
if (sectionsList) {
    let draggedItem = null;

    sectionsList.querySelectorAll('.section-card').forEach(card => {
        card.draggable = true;

        card.addEventListener('dragstart', function(e) {
            draggedItem = this;
            this.classList.add('dragging');
        });

        card.addEventListener('dragend', function(e) {
            this.classList.remove('dragging');
            saveOrder();
        });

        card.addEventListener('dragover', function(e) {
            e.preventDefault();
            const afterElement = getDragAfterElement(sectionsList, e.clientY);
            if (draggedItem && afterElement) {
                sectionsList.insertBefore(draggedItem, afterElement);
            } else if (draggedItem) {
                sectionsList.appendChild(draggedItem);
            }
        });
    });
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.section-card:not(.dragging)')];

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

async function saveOrder() {
    const items = [...document.querySelectorAll('.section-card')].map((card, index) => ({
        id: card.dataset.id,
        order: index
    }));

    try {
        await fetch('/admin/homepage/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= csrf_token() ?>'
            },
            body: JSON.stringify({ items })
        });
    } catch (error) {
        console.error('Error saving order:', error);
    }
}
</script>

<?php
// Helper function for section icons
function getSectionIcon($type) {
    $icons = [
        'hero' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
        'trust_badges' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
        'featured_categories' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
        'trending_products' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
        'banner' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
        'new_arrivals' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        'testimonials' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path></svg>',
        'newsletter' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        'brands' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>',
        'custom_html' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
    ];
    return $icons[$type] ?? $icons['custom_html'];
}
$this->getSectionIcon = 'getSectionIcon';
?>
