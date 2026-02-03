<!-- Admin Banners & Sliders -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title ?? 'Banners & Sliders') ?></h1>
        <p class="admin-page-subtitle"><?= count($banners) ?> banners total</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/banners/create') ?>" class="admin-btn admin-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Banner
        </a>
    </div>
</div>

<!-- Location Filter -->
<div class="admin-card mb-6">
    <div class="admin-filters">
        <a href="<?= url('/admin/banners') ?>" class="admin-filter-btn <?= empty($currentLocation) ? 'active' : '' ?>">
            All Locations
        </a>
        <?php foreach ($locations as $key => $label): ?>
        <a href="<?= url('/admin/banners?location=' . $key) ?>"
           class="admin-filter-btn <?= $currentLocation === $key ? 'active' : '' ?>">
            <?= e($label) ?>
            <?php
            $count = count(array_filter($banners, fn($b) => $b['location'] === $key));
            if ($count > 0): ?>
            <span class="admin-filter-count"><?= $count ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (empty($banners)): ?>
<!-- Empty State -->
<div class="admin-card">
    <div class="admin-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
        </svg>
        <h3 class="admin-empty-title">No banners yet</h3>
        <p class="admin-empty-text">Create promotional banners and hero sliders for your store.</p>
        <a href="<?= url('/admin/banners/create') ?>" class="admin-btn admin-btn-primary">Add Your First Banner</a>
    </div>
</div>

<?php else: ?>

<?php if (empty($currentLocation)): ?>
<!-- Grouped by Location -->
<?php foreach ($locations as $locationKey => $locationLabel): ?>
<?php if (!empty($grouped[$locationKey])): ?>
<div class="admin-card mb-6">
    <div class="admin-card-header">
        <h2 class="font-semibold"><?= e($locationLabel) ?></h2>
        <span class="admin-badge neutral"><?= count($grouped[$locationKey]) ?> banner(s)</span>
    </div>
    <div class="banner-grid" data-location="<?= $locationKey ?>">
        <?php foreach ($grouped[$locationKey] as $banner): ?>
        <?php include __DIR__ . '/_banner_card.php'; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php else: ?>
<!-- Single Location View with Drag & Drop -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="font-semibold"><?= e($locations[$currentLocation] ?? $currentLocation) ?></h2>
        <p class="text-muted text-sm">Drag to reorder banners</p>
    </div>
    <div class="banner-grid sortable" data-location="<?= e($currentLocation) ?>">
        <?php foreach ($banners as $banner): ?>
        <?php include __DIR__ . '/_banner_card.php'; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- Delete Modal -->
<div class="admin-modal-overlay" id="deleteModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Banner</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete this banner? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Banner</button>
            </form>
        </div>
    </div>
</div>

<style>
.admin-filters {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    padding: var(--space-3);
}

.admin-filter-btn {
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    transition: var(--transition-colors);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.admin-filter-btn:hover {
    background: var(--color-background-alt);
    color: var(--color-text);
}

.admin-filter-btn.active {
    background: var(--color-primary);
    color: white;
}

.admin-filter-count {
    background: rgba(255,255,255,0.2);
    padding: 2px 6px;
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
}

.banner-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-4);
    padding: var(--space-4);
}

.banner-card {
    background: var(--color-background);
    border: 1px solid var(--color-border-light);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: var(--transition-all);
}

.banner-card:hover {
    box-shadow: var(--shadow-md);
}

.banner-card.dragging {
    opacity: 0.5;
}

.banner-image {
    position: relative;
    aspect-ratio: 16/9;
    background: var(--color-background-alt);
    overflow: hidden;
}

.banner-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.7));
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: var(--space-4);
    color: white;
}

.banner-title {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-1);
}

.banner-subtitle {
    font-size: var(--text-sm);
    opacity: 0.9;
}

.banner-status {
    position: absolute;
    top: var(--space-2);
    right: var(--space-2);
}

.banner-body {
    padding: var(--space-3);
}

.banner-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    margin-bottom: var(--space-3);
}

.banner-actions {
    display: flex;
    gap: var(--space-2);
    justify-content: flex-end;
    border-top: 1px solid var(--color-border-light);
    padding: var(--space-3);
}

.admin-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-4);
    border-bottom: 1px solid var(--color-border-light);
}

.sortable .banner-card {
    cursor: grab;
}

.sortable .banner-card:active {
    cursor: grabbing;
}
</style>

<script>
function deleteBanner(id) {
    document.getElementById('deleteForm').action = '/admin/banners/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

// Toggle banner active status
async function toggleBanner(id, button) {
    try {
        const response = await fetch('/admin/banners/' + id + '/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= csrf_token() ?>'
            }
        });

        const data = await response.json();
        if (data.success) {
            const badge = button.closest('.banner-card').querySelector('.banner-status .admin-badge');
            if (data.is_active) {
                badge.className = 'admin-badge active';
                badge.textContent = 'Active';
            } else {
                badge.className = 'admin-badge inactive';
                badge.textContent = 'Inactive';
            }
        }
    } catch (error) {
        console.error('Error toggling banner:', error);
    }
}

// Drag and drop reordering
document.querySelectorAll('.sortable').forEach(container => {
    let draggedItem = null;

    container.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('banner-card')) {
            draggedItem = e.target;
            e.target.classList.add('dragging');
        }
    });

    container.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('banner-card')) {
            e.target.classList.remove('dragging');
            saveOrder(container);
        }
    });

    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(container, e.clientY);
        if (draggedItem && afterElement) {
            container.insertBefore(draggedItem, afterElement);
        } else if (draggedItem) {
            container.appendChild(draggedItem);
        }
    });

    // Make cards draggable
    container.querySelectorAll('.banner-card').forEach(card => {
        card.draggable = true;
    });
});

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.banner-card:not(.dragging)')];

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

async function saveOrder(container) {
    const items = [...container.querySelectorAll('.banner-card')].map((card, index) => ({
        id: card.dataset.id,
        order: index
    }));

    try {
        await fetch('/admin/banners/reorder', {
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
