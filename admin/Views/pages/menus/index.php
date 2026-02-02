<!-- Admin Menus Management -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">Manage navigation menus</p>
    </div>
    <div class="admin-page-actions">
        <button type="button" class="admin-btn admin-btn-primary" onclick="showCreateMenuModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Create Menu
        </button>
    </div>
</div>

<div class="menus-grid">
    <!-- Menu List -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Menus</h3>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <?php if (empty($menus)): ?>
            <div class="admin-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
                <h3 class="admin-empty-title">No menus yet</h3>
                <p class="admin-empty-text">Create your first navigation menu.</p>
                <button type="button" class="admin-btn admin-btn-primary" onclick="showCreateMenuModal()">Create Menu</button>
            </div>
            <?php else: ?>
            <ul class="menu-list">
                <?php foreach ($menus as $menu): ?>
                <li class="menu-list-item <?= isset($selectedMenu) && $selectedMenu['id'] == $menu['id'] ? 'active' : '' ?>">
                    <a href="<?= url('/admin/menus?menu=' . $menu['id']) ?>" class="menu-list-link">
                        <span class="menu-list-name"><?= e($menu['name']) ?></span>
                        <span class="menu-list-location"><?= e($menu['location'] ?? 'Not assigned') ?></span>
                    </a>
                    <div class="menu-list-actions">
                        <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon"
                                onclick="editMenu(<?= $menu['id'] ?>, '<?= e(addslashes($menu['name'])) ?>', '<?= e($menu['location'] ?? '') ?>')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon"
                                onclick="deleteMenu(<?= $menu['id'] ?>, '<?= e(addslashes($menu['name'])) ?>')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Menu Items Editor -->
    <?php if (isset($selectedMenu)): ?>
    <div class="admin-card menu-editor">
        <div class="admin-card-header">
            <h3 class="admin-card-title"><?= e($selectedMenu['name']) ?> Items</h3>
            <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick="showAddItemModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Item
            </button>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <?php if (empty($menuItems)): ?>
            <div class="admin-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <h3 class="admin-empty-title">No menu items</h3>
                <p class="admin-empty-text">Add items to this menu.</p>
                <button type="button" class="admin-btn admin-btn-primary" onclick="showAddItemModal()">Add Item</button>
            </div>
            <?php else: ?>
            <ul class="menu-items-list" id="menuItemsList">
                <?php foreach ($menuItems as $item): ?>
                <li class="menu-item" data-id="<?= $item['id'] ?>" style="padding-left: <?= ($item['depth'] ?? 0) * 20 + 16 ?>px;">
                    <div class="menu-item-handle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="8" y1="6" x2="8" y2="6"></line>
                            <line x1="16" y1="6" x2="16" y2="6"></line>
                            <line x1="8" y1="12" x2="8" y2="12"></line>
                            <line x1="16" y1="12" x2="16" y2="12"></line>
                            <line x1="8" y1="18" x2="8" y2="18"></line>
                            <line x1="16" y1="18" x2="16" y2="18"></line>
                        </svg>
                    </div>
                    <div class="menu-item-content">
                        <span class="menu-item-title"><?= e($item['title']) ?></span>
                        <span class="menu-item-url"><?= e($item['url']) ?></span>
                    </div>
                    <div class="menu-item-actions">
                        <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon"
                                onclick="editItem(<?= htmlspecialchars(json_encode($item), ENT_QUOTES) ?>)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon"
                                onclick="deleteItem(<?= $item['id'] ?>, '<?= e(addslashes($item['title'])) ?>')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="menu-items-footer">
                <form method="POST" action="<?= url('/admin/menus/' . $selectedMenu['id'] . '/reorder') ?>" id="reorderForm">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="order" id="orderInput" value="">
                    <button type="submit" class="admin-btn admin-btn-secondary" id="saveOrderBtn" style="display: none;">
                        Save Order
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="admin-card">
        <div class="admin-card-body">
            <div class="admin-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="admin-empty-icon">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                <h3 class="admin-empty-title">Select a menu</h3>
                <p class="admin-empty-text">Choose a menu from the list to edit its items.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Create/Edit Menu Modal -->
<div class="admin-modal-overlay" id="menuModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="menuModalTitle">Create Menu</h3>
            <button class="admin-modal-close" onclick="closeMenuModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" id="menuForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="_method" id="menuMethod" value="POST">
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label class="admin-label required">Menu Name</label>
                    <input type="text" name="name" id="menuName" class="admin-input" required>
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Location</label>
                    <select name="location" id="menuLocation" class="admin-select">
                        <option value="">-- Select Location --</option>
                        <option value="header">Header Navigation</option>
                        <option value="footer">Footer Navigation</option>
                        <option value="sidebar">Sidebar</option>
                        <option value="mobile">Mobile Menu</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeMenuModal()">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary" id="menuSubmitBtn">Create Menu</button>
            </div>
        </form>
    </div>
</div>

<!-- Add/Edit Item Modal -->
<div class="admin-modal-overlay" id="itemModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="itemModalTitle">Add Menu Item</h3>
            <button class="admin-modal-close" onclick="closeItemModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <form method="POST" id="itemForm" action="<?= isset($selectedMenu) ? url('/admin/menus/' . $selectedMenu['id'] . '/items') : '#' ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="_method" id="itemMethod" value="POST">
            <input type="hidden" name="item_id" id="itemId" value="">
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label class="admin-label required">Title</label>
                    <input type="text" name="title" id="itemTitle" class="admin-input" required>
                </div>
                <div class="admin-form-group">
                    <label class="admin-label required">URL</label>
                    <input type="text" name="url" id="itemUrl" class="admin-input" placeholder="/about or https://..." required>
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Parent Item</label>
                    <select name="parent_id" id="itemParent" class="admin-select">
                        <option value="">-- No Parent --</option>
                        <?php if (!empty($menuItems)): ?>
                        <?php foreach ($menuItems as $item): ?>
                        <option value="<?= $item['id'] ?>"><?= e(str_repeat('— ', $item['depth'] ?? 0) . $item['title']) ?></option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label class="admin-label">CSS Class</label>
                        <input type="text" name="css_class" id="itemClass" class="admin-input" placeholder="nav-item special">
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-label">Icon</label>
                        <input type="text" name="icon" id="itemIcon" class="admin-input" placeholder="home, shopping-cart">
                    </div>
                </div>
                <div class="admin-form-group">
                    <label class="admin-checkbox">
                        <input type="checkbox" name="open_in_new_tab" id="itemNewTab" value="1">
                        <span>Open in new tab</span>
                    </label>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeItemModal()">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary" id="itemSubmitBtn">Add Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modals -->
<div class="admin-modal-overlay" id="deleteMenuModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Menu</h3>
            <button class="admin-modal-close" onclick="closeDeleteMenuModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete "<strong id="deleteMenuName"></strong>"? All menu items will also be deleted.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteMenuModal()">Cancel</button>
            <form id="deleteMenuForm" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Menu</button>
            </form>
        </div>
    </div>
</div>

<div class="admin-modal-overlay" id="deleteItemModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Menu Item</h3>
            <button class="admin-modal-close" onclick="closeDeleteItemModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete "<strong id="deleteItemName"></strong>"?</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteItemModal()">Cancel</button>
            <form id="deleteItemForm" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Item</button>
            </form>
        </div>
    </div>
</div>

<style>
.menus-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 1.5rem;
}

.menu-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.menu-list-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--admin-border);
}

.menu-list-item:last-child {
    border-bottom: none;
}

.menu-list-item.active {
    background: var(--admin-primary-light);
}

.menu-list-link {
    flex: 1;
    text-decoration: none;
    color: inherit;
}

.menu-list-name {
    display: block;
    font-weight: 500;
}

.menu-list-location {
    font-size: 0.75rem;
    color: var(--admin-text-muted);
}

.menu-list-actions {
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.menu-list-item:hover .menu-list-actions {
    opacity: 1;
}

.menu-items-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--admin-border);
    background: var(--admin-card-bg);
}

.menu-item:last-child {
    border-bottom: none;
}

.menu-item-handle {
    cursor: grab;
    padding: 0.25rem;
    color: var(--admin-text-muted);
}

.menu-item-handle svg {
    width: 16px;
    height: 16px;
}

.menu-item-content {
    flex: 1;
    margin-left: 0.5rem;
}

.menu-item-title {
    display: block;
    font-weight: 500;
}

.menu-item-url {
    font-size: 0.75rem;
    color: var(--admin-text-muted);
}

.menu-item-actions {
    display: flex;
    gap: 0.25rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.menu-item:hover .menu-item-actions {
    opacity: 1;
}

.menu-items-footer {
    padding: 1rem;
    border-top: 1px solid var(--admin-border);
}

.admin-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .menus-grid {
        grid-template-columns: 1fr;
    }

    .menu-list-actions,
    .menu-item-actions {
        opacity: 1;
    }
}
</style>

<script>
// Menu Modal
function showCreateMenuModal() {
    document.getElementById('menuModalTitle').textContent = 'Create Menu';
    document.getElementById('menuForm').action = '<?= url('/admin/menus') ?>';
    document.getElementById('menuMethod').value = 'POST';
    document.getElementById('menuName').value = '';
    document.getElementById('menuLocation').value = '';
    document.getElementById('menuSubmitBtn').textContent = 'Create Menu';
    document.getElementById('menuModal').classList.add('show');
}

function editMenu(id, name, location) {
    document.getElementById('menuModalTitle').textContent = 'Edit Menu';
    document.getElementById('menuForm').action = '/admin/menus/' + id;
    document.getElementById('menuMethod').value = 'PUT';
    document.getElementById('menuName').value = name;
    document.getElementById('menuLocation').value = location;
    document.getElementById('menuSubmitBtn').textContent = 'Update Menu';
    document.getElementById('menuModal').classList.add('show');
}

function closeMenuModal() {
    document.getElementById('menuModal').classList.remove('show');
}

// Item Modal
function showAddItemModal() {
    document.getElementById('itemModalTitle').textContent = 'Add Menu Item';
    document.getElementById('itemMethod').value = 'POST';
    document.getElementById('itemId').value = '';
    document.getElementById('itemTitle').value = '';
    document.getElementById('itemUrl').value = '';
    document.getElementById('itemParent').value = '';
    document.getElementById('itemClass').value = '';
    document.getElementById('itemIcon').value = '';
    document.getElementById('itemNewTab').checked = false;
    document.getElementById('itemSubmitBtn').textContent = 'Add Item';
    document.getElementById('itemModal').classList.add('show');
}

function editItem(item) {
    document.getElementById('itemModalTitle').textContent = 'Edit Menu Item';
    document.getElementById('itemMethod').value = 'PUT';
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemTitle').value = item.title;
    document.getElementById('itemUrl').value = item.url;
    document.getElementById('itemParent').value = item.parent_id || '';
    document.getElementById('itemClass').value = item.css_class || '';
    document.getElementById('itemIcon').value = item.icon || '';
    document.getElementById('itemNewTab').checked = item.open_in_new_tab == 1;
    document.getElementById('itemSubmitBtn').textContent = 'Update Item';
    document.getElementById('itemModal').classList.add('show');
}

function closeItemModal() {
    document.getElementById('itemModal').classList.remove('show');
}

// Delete Modals
function deleteMenu(id, name) {
    document.getElementById('deleteMenuName').textContent = name;
    document.getElementById('deleteMenuForm').action = '/admin/menus/' + id;
    document.getElementById('deleteMenuModal').classList.add('show');
}

function closeDeleteMenuModal() {
    document.getElementById('deleteMenuModal').classList.remove('show');
}

function deleteItem(id, title) {
    document.getElementById('deleteItemName').textContent = title;
    document.getElementById('deleteItemForm').action = '/admin/menus/items/' + id;
    document.getElementById('deleteItemModal').classList.add('show');
}

function closeDeleteItemModal() {
    document.getElementById('deleteItemModal').classList.remove('show');
}

// Modal click outside to close
['menuModal', 'itemModal', 'deleteMenuModal', 'deleteItemModal'].forEach(function(id) {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
        }
    });
});
</script>
