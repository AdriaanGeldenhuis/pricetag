<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Banners & Sliders</h1>
        <p class="admin-page-subtitle">Manage promotional banners and hero sliders</p>
    </div>
    <a href="<?= url('/admin/banners/create') ?>" class="admin-btn admin-btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Add Banner
    </a>
</div>

<?php
// Group banners by location
$bannersByLocation = [];
foreach ($banners as $banner) {
    $loc = $banner['location'] ?? 'other';
    $bannersByLocation[$loc][] = $banner;
}

$locationNames = [
    'hero' => 'Hero Slider',
    'sidebar' => 'Sidebar',
    'homepage_top' => 'Homepage - Top',
    'homepage_middle' => 'Homepage - Middle',
    'homepage_bottom' => 'Homepage - Bottom',
    'category_top' => 'Category Page - Top',
    'product_sidebar' => 'Product Page - Sidebar',
];
?>

<?php if (empty($banners)): ?>
<div class="admin-card">
    <div class="card-body text-center py-12">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" class="mx-auto mb-4 text-muted opacity-50">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <circle cx="8.5" cy="8.5" r="1.5"></circle>
            <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
        <p class="text-muted mb-4">No banners yet.</p>
        <a href="<?= url('/admin/banners/create') ?>" class="admin-btn admin-btn-primary">
            Create Your First Banner
        </a>
    </div>
</div>
<?php else: ?>

<?php foreach ($bannersByLocation as $location => $locationBanners): ?>
<div class="admin-card mb-6">
    <div class="card-header flex items-center justify-between">
        <h2 class="font-semibold"><?= e($locationNames[$location] ?? ucwords(str_replace('_', ' ', $location))) ?></h2>
        <span class="text-sm text-muted"><?= count($locationBanners) ?> banner<?= count($locationBanners) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body p-0">
        <div class="banner-list" data-location="<?= e($location) ?>">
            <?php foreach ($locationBanners as $banner): ?>
            <div class="banner-item" data-id="<?= $banner['id'] ?>">
                <div class="banner-drag-handle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <line x1="8" y1="6" x2="8" y2="6.01"></line>
                        <line x1="16" y1="6" x2="16" y2="6.01"></line>
                        <line x1="8" y1="12" x2="8" y2="12.01"></line>
                        <line x1="16" y1="12" x2="16" y2="12.01"></line>
                        <line x1="8" y1="18" x2="8" y2="18.01"></line>
                        <line x1="16" y1="18" x2="16" y2="18.01"></line>
                    </svg>
                </div>
                <div class="banner-thumb">
                    <?php if (!empty($banner['image'])): ?>
                    <img src="<?= e($banner['image']) ?>" alt="">
                    <?php else: ?>
                    <div class="banner-thumb-placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="banner-info flex-1">
                    <h4 class="font-medium"><?= e($banner['title'] ?: 'Untitled Banner') ?></h4>
                    <?php if (!empty($banner['url'])): ?>
                    <p class="text-sm text-muted truncate"><?= e($banner['url']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($banner['starts_at']) || !empty($banner['expires_at'])): ?>
                    <p class="text-xs text-muted mt-1">
                        <?php if (!empty($banner['starts_at'])): ?>
                        Starts: <?= date('M j, Y', strtotime($banner['starts_at'])) ?>
                        <?php endif; ?>
                        <?php if (!empty($banner['expires_at'])): ?>
                        • Expires: <?= date('M j, Y', strtotime($banner['expires_at'])) ?>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="banner-actions">
                    <button type="button"
                            onclick="toggleBanner(<?= $banner['id'] ?>, this)"
                            class="toggle-btn <?= $banner['is_active'] ? 'active' : '' ?>"
                            title="<?= $banner['is_active'] ? 'Click to deactivate' : 'Click to activate' ?>">
                        <span class="toggle-slider"></span>
                    </button>
                    <a href="<?= url('/admin/banners/' . $banner['id'] . '/edit') ?>" class="admin-btn admin-btn-sm admin-btn-secondary">
                        Edit
                    </a>
                    <button type="button" onclick="deleteBanner(<?= $banner['id'] ?>)" class="admin-btn admin-btn-sm admin-btn-danger-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<!-- Delete Banner Modal -->
<div class="admin-modal-overlay" id="deleteBannerModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Banner</h3>
            <button class="admin-modal-close" onclick="hideDeleteModal()">
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
            <button class="admin-btn admin-btn-secondary" onclick="hideDeleteModal()">Cancel</button>
            <form id="deleteBannerForm" method="POST" style="display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Banner</button>
            </form>
        </div>
    </div>
</div>

<style>
.banner-list {
    display: flex;
    flex-direction: column;
}

.banner-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--color-border-light);
    transition: background 0.2s;
}

.banner-item:last-child {
    border-bottom: none;
}

.banner-item:hover {
    background: var(--color-background-alt);
}

.banner-item.dragging {
    opacity: 0.5;
    background: var(--color-primary-50);
}

.banner-drag-handle {
    cursor: move;
    color: var(--color-text-muted);
    padding: 0.25rem;
}

.banner-thumb {
    width: 120px;
    height: 60px;
    border-radius: var(--radius-md);
    overflow: hidden;
    flex-shrink: 0;
    background: var(--color-background-alt);
}

.banner-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-thumb-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
}

.banner-info {
    min-width: 0;
}

.banner-info .truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.banner-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
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
// Toggle Banner
function toggleBanner(id, btn) {
    fetch('<?= url('/admin/banners/') ?>' + id + '/toggle', {
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

// Delete Banner Modal
function deleteBanner(id) {
    document.getElementById('deleteBannerForm').action = '<?= url('/admin/banners/') ?>' + id;
    document.getElementById('deleteBannerModal').classList.add('show');
}

function hideDeleteModal() {
    document.getElementById('deleteBannerModal').classList.remove('show');
}

// Close modal on overlay click
document.getElementById('deleteBannerModal')?.addEventListener('click', function(e) {
    if (e.target === this) hideDeleteModal();
});

// Drag and drop reordering
document.querySelectorAll('.banner-list').forEach(list => {
    let draggedItem = null;

    list.querySelectorAll('.banner-item').forEach(item => {
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', function(e) {
            draggedItem = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedItem = null;
            saveBannerOrder(list);
        });

        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            const afterElement = getDragAfterElement(list, e.clientY);
            if (afterElement == null) {
                list.appendChild(draggedItem);
            } else {
                list.insertBefore(draggedItem, afterElement);
            }
        });
    });
});

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.banner-item:not(.dragging)')];

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

function saveBannerOrder(list) {
    const items = list.querySelectorAll('.banner-item');
    const order = Array.from(items).map(item => item.dataset.id);

    fetch('<?= url('/admin/banners/reorder') ?>', {
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
