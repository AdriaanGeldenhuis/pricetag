<!-- Admin Banners & Sliders -->
<?php
// Helper function to check if banner is currently scheduled
if (!function_exists('isBannerScheduled')) {
    function isBannerScheduled(array $banner): bool {
        $now = time();
        if (!empty($banner['starts_at']) && strtotime($banner['starts_at']) > $now) {
            return false;
        }
        if (!empty($banner['expires_at']) && strtotime($banner['expires_at']) < $now) {
            return false;
        }
        return true;
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="admin-page-title mb-0">Banners & Sliders</h1>
        <p class="text-muted mt-1"><?= count($banners) ?> banners total</p>
    </div>
    <a href="<?= url('/admin/banners/create') ?>" class="btn btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" class="mr-2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Banner
    </a>
</div>

<!-- Location Filter -->
<div class="card mb-6">
    <div class="card-body">
        <div class="flex flex-wrap gap-2">
            <a href="<?= url('/admin/banners') ?>" class="filter-btn <?= empty($currentLocation) ? 'active' : '' ?>">
                All Locations
            </a>
            <?php foreach ($locations as $key => $label): ?>
            <a href="<?= url('/admin/banners?location=' . $key) ?>"
               class="filter-btn <?= $currentLocation === $key ? 'active' : '' ?>">
                <?= e($label) ?>
                <?php
                $count = 0;
                foreach ($banners as $b) {
                    if (($b['location'] ?? '') === $key) $count++;
                }
                if ($count > 0): ?>
                <span class="filter-count"><?= $count ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (empty($banners)): ?>
<!-- Empty State -->
<div class="card">
    <div class="card-body text-center py-12">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48" class="mx-auto mb-4 text-muted">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
        </svg>
        <h3 class="text-lg font-semibold mb-2">No banners yet</h3>
        <p class="text-muted mb-4">Create promotional banners and hero sliders for your store.</p>
        <a href="<?= url('/admin/banners/create') ?>" class="btn btn-primary">Add Your First Banner</a>
    </div>
</div>

<?php else: ?>

<?php if (empty($currentLocation)): ?>
<!-- Grouped by Location -->
<?php foreach ($locations as $locationKey => $locationLabel): ?>
<?php if (!empty($grouped[$locationKey])): ?>
<div class="card mb-6">
    <div class="card-header flex justify-between items-center">
        <h2 class="font-semibold"><?= e($locationLabel) ?></h2>
        <span class="admin-badge neutral"><?= count($grouped[$locationKey]) ?> banner(s)</span>
    </div>
    <div class="card-body">
        <div class="banner-grid">
            <?php foreach ($grouped[$locationKey] as $banner): ?>
            <!-- Banner Card -->
            <div class="banner-card" data-id="<?= $banner['id'] ?>">
                <div class="banner-image">
                    <img src="<?= e($banner['image']) ?>" alt="<?= e($banner['title'] ?: 'Banner') ?>">
                    <?php if ($banner['title'] || $banner['subtitle']): ?>
                    <div class="banner-overlay-text">
                        <?php if ($banner['title']): ?>
                        <div class="banner-title"><?= e($banner['title']) ?></div>
                        <?php endif; ?>
                        <?php if ($banner['subtitle']): ?>
                        <div class="banner-subtitle"><?= e(substr($banner['subtitle'], 0, 60)) ?><?= strlen($banner['subtitle']) > 60 ? '...' : '' ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="banner-status">
                        <?php if ($banner['is_active']): ?>
                        <span class="admin-badge active">Active</span>
                        <?php else: ?>
                        <span class="admin-badge inactive">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="banner-body">
                    <div class="banner-meta">
                        <?php if ($banner['url']): ?>
                        <span class="admin-badge neutral" title="<?= e($banner['url']) ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                            Link
                        </span>
                        <?php endif; ?>

                        <?php if ($banner['button_text']): ?>
                        <span class="admin-badge neutral"><?= e($banner['button_text']) ?></span>
                        <?php endif; ?>

                        <?php if ($banner['starts_at'] || $banner['expires_at']): ?>
                        <span class="admin-badge <?= isBannerScheduled($banner) ? 'warning' : 'neutral' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Scheduled
                        </span>
                        <?php endif; ?>

                        <?php if ($banner['mobile_image']): ?>
                        <span class="admin-badge neutral" title="Mobile image set">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                            Mobile
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($banner['starts_at'] || $banner['expires_at']): ?>
                    <div class="text-xs text-muted mb-2">
                        <?php if ($banner['starts_at']): ?>
                        <span>Starts: <?= date('M j, Y', strtotime($banner['starts_at'])) ?></span>
                        <?php endif; ?>
                        <?php if ($banner['expires_at']): ?>
                        <span class="ml-2">Expires: <?= date('M j, Y', strtotime($banner['expires_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="banner-actions">
                    <button type="button" onclick="toggleBanner(<?= $banner['id'] ?>, this)"
                            class="btn btn-ghost btn-sm btn-icon" title="Toggle Active">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>

                    <a href="<?= url('/admin/banners/' . $banner['id'] . '/edit') ?>"
                       class="btn btn-ghost btn-sm btn-icon" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>

                    <button type="button" onclick="deleteBanner(<?= $banner['id'] ?>)"
                            class="btn btn-ghost btn-sm btn-icon text-danger" title="Delete">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php else: ?>
<!-- Single Location View with Drag & Drop -->
<div class="card">
    <div class="card-header flex justify-between items-center">
        <h2 class="font-semibold"><?= e($locations[$currentLocation] ?? $currentLocation) ?></h2>
        <p class="text-muted text-sm">Drag to reorder banners</p>
    </div>
    <div class="card-body">
        <div class="banner-grid sortable" data-location="<?= e($currentLocation) ?>">
            <?php foreach ($banners as $banner): ?>
            <!-- Banner Card -->
            <div class="banner-card" data-id="<?= $banner['id'] ?>">
                <div class="banner-image">
                    <img src="<?= e($banner['image']) ?>" alt="<?= e($banner['title'] ?: 'Banner') ?>">
                    <?php if ($banner['title'] || $banner['subtitle']): ?>
                    <div class="banner-overlay-text">
                        <?php if ($banner['title']): ?>
                        <div class="banner-title"><?= e($banner['title']) ?></div>
                        <?php endif; ?>
                        <?php if ($banner['subtitle']): ?>
                        <div class="banner-subtitle"><?= e(substr($banner['subtitle'], 0, 60)) ?><?= strlen($banner['subtitle']) > 60 ? '...' : '' ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="banner-status">
                        <?php if ($banner['is_active']): ?>
                        <span class="admin-badge active">Active</span>
                        <?php else: ?>
                        <span class="admin-badge inactive">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="banner-body">
                    <div class="banner-meta">
                        <?php if ($banner['url']): ?>
                        <span class="admin-badge neutral" title="<?= e($banner['url']) ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                            Link
                        </span>
                        <?php endif; ?>

                        <?php if ($banner['button_text']): ?>
                        <span class="admin-badge neutral"><?= e($banner['button_text']) ?></span>
                        <?php endif; ?>

                        <?php if ($banner['starts_at'] || $banner['expires_at']): ?>
                        <span class="admin-badge <?= isBannerScheduled($banner) ? 'warning' : 'neutral' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            Scheduled
                        </span>
                        <?php endif; ?>

                        <?php if ($banner['mobile_image']): ?>
                        <span class="admin-badge neutral" title="Mobile image set">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                            Mobile
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($banner['starts_at'] || $banner['expires_at']): ?>
                    <div class="text-xs text-muted mb-2">
                        <?php if ($banner['starts_at']): ?>
                        <span>Starts: <?= date('M j, Y', strtotime($banner['starts_at'])) ?></span>
                        <?php endif; ?>
                        <?php if ($banner['expires_at']): ?>
                        <span class="ml-2">Expires: <?= date('M j, Y', strtotime($banner['expires_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="banner-actions">
                    <button type="button" onclick="toggleBanner(<?= $banner['id'] ?>, this)"
                            class="btn btn-ghost btn-sm btn-icon" title="Toggle Active">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>

                    <a href="<?= url('/admin/banners/' . $banner['id'] . '/edit') ?>"
                       class="btn btn-ghost btn-sm btn-icon" title="Edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>

                    <button type="button" onclick="deleteBanner(<?= $banner['id'] ?>)"
                            class="btn btn-ghost btn-sm btn-icon text-danger" title="Delete">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
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
            <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form id="deleteForm" method="POST" style="display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger">Delete Banner</button>
            </form>
        </div>
    </div>
</div>

<style>
.filter-btn {
    padding: var(--space-2) var(--space-3);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    transition: var(--transition-colors);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    text-decoration: none;
}

.filter-btn:hover {
    background: var(--color-background-alt);
    color: var(--color-text);
}

.filter-btn.active {
    background: var(--color-primary);
    color: white;
}

.filter-count {
    background: rgba(255,255,255,0.2);
    padding: 2px 6px;
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
}

.banner-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-4);
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

.banner-overlay-text {
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
    margin-bottom: var(--space-2);
}

.banner-actions {
    display: flex;
    gap: var(--space-2);
    justify-content: flex-end;
    border-top: 1px solid var(--color-border-light);
    padding: var(--space-3);
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
