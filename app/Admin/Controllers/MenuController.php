<?php
declare(strict_types=1);

/**
 * Admin Menu Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Manages navigation menus and menu items
 */

namespace Admin\Controllers;

use App\Core\Controller;

class MenuController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get all menus with item counts
        $menus = $db->query("
            SELECT m.*, COUNT(mi.id) as item_count
            FROM menus m
            LEFT JOIN menu_items mi ON m.id = mi.menu_id
            GROUP BY m.id
            ORDER BY m.name
        ")->fetchAll();

        // Get menu items for each menu
        $menuItems = [];
        foreach ($menus as $menu) {
            $items = $db->query("
                SELECT mi.*, p.title as page_title, c.name as category_name
                FROM menu_items mi
                LEFT JOIN pages p ON mi.type = 'page' AND mi.reference_id = p.id
                LEFT JOIN categories c ON mi.type = 'category' AND mi.reference_id = c.id
                WHERE mi.menu_id = ?
                ORDER BY mi.sort_order
            ", [$menu['id']])->fetchAll();

            $menuItems[$menu['id']] = $this->buildTree($items);
        }

        // Get pages and categories for item creation
        $pages = $db->query("SELECT id, title FROM pages WHERE status = 'published' ORDER BY title")->fetchAll();
        $categories = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/menus/index', [
            'title' => 'Menus',
            'menus' => $menus,
            'menuItems' => $menuItems,
            'pages' => $pages,
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:100',
            'location' => 'required|max:50',
        ]);

        if (!$validation['valid']) {
            if (isAjax()) {
                $this->json(['success' => false, 'errors' => $validation['errors']], 422);
                return;
            }
            flash('error', 'Please correct the errors below.');
            $this->redirect('/admin/menus');
            return;
        }

        $db = db();

        // Check location uniqueness
        $existing = $db->query("SELECT id FROM menus WHERE location = ?", [$_POST['location']])->fetch();
        if ($existing) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'A menu with this location already exists.'], 400);
                return;
            }
            flash('error', 'A menu with this location already exists.');
            $this->redirect('/admin/menus');
            return;
        }

        $db->query("
            INSERT INTO menus (name, location, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
        ", [trim($_POST['name']), trim($_POST['location'])]);

        $menuId = $db->lastInsertId();

        if (isAjax()) {
            $this->json(['success' => true, 'id' => $menuId, 'message' => 'Menu created successfully.']);
            return;
        }

        flash('success', 'Menu created successfully.');
        $this->redirect('/admin/menus');
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $menu = $db->query("SELECT * FROM menus WHERE id = ?", [$id])->fetch();

        if (!$menu) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Menu not found.'], 404);
                return;
            }
            flash('error', 'Menu not found.');
            $this->redirect('/admin/menus');
            return;
        }

        // Handle menu items update (AJAX)
        if (isset($_POST['items'])) {
            $this->updateMenuItems($id, $_POST['items']);
            $this->json(['success' => true, 'message' => 'Menu items updated successfully.']);
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:100',
        ]);

        if (!$validation['valid']) {
            if (isAjax()) {
                $this->json(['success' => false, 'errors' => $validation['errors']], 422);
                return;
            }
            flash('error', 'Please correct the errors below.');
            $this->redirect('/admin/menus');
            return;
        }

        $db->query("UPDATE menus SET name = ?, updated_at = NOW() WHERE id = ?", [trim($_POST['name']), $id]);

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'Menu updated successfully.']);
            return;
        }

        flash('success', 'Menu updated successfully.');
        $this->redirect('/admin/menus');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $menu = $db->query("SELECT * FROM menus WHERE id = ?", [$id])->fetch();

        if (!$menu) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Menu not found.'], 404);
                return;
            }
            flash('error', 'Menu not found.');
            $this->redirect('/admin/menus');
            return;
        }

        // Delete menu (items will cascade)
        $db->query("DELETE FROM menus WHERE id = ?", [$id]);

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'Menu deleted successfully.']);
            return;
        }

        flash('success', 'Menu deleted successfully.');
        $this->redirect('/admin/menus');
    }

    /**
     * Add a menu item
     */
    public function addItem(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $type = $_POST['type'] ?? 'link';
        $title = trim($_POST['title'] ?? '');

        if (!$menuId || !$title) {
            $this->json(['success' => false, 'error' => 'Menu ID and title are required.'], 400);
            return;
        }

        $db = db();

        // Get max sort order
        $maxSort = (int) $db->query(
            "SELECT MAX(sort_order) FROM menu_items WHERE menu_id = ? AND parent_id IS NULL",
            [$menuId]
        )->fetchColumn();

        $db->query("
            INSERT INTO menu_items (
                menu_id, parent_id, type, reference_id, title, url, icon,
                badge_text, badge_color, is_mega, mega_columns, open_in_new_tab,
                sort_order, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ", [
            $menuId,
            !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
            $type,
            !empty($_POST['reference_id']) ? (int) $_POST['reference_id'] : null,
            $title,
            trim($_POST['url'] ?? '') ?: null,
            trim($_POST['icon'] ?? '') ?: null,
            trim($_POST['badge_text'] ?? '') ?: null,
            trim($_POST['badge_color'] ?? '') ?: null,
            isset($_POST['is_mega']) ? 1 : 0,
            (int) ($_POST['mega_columns'] ?? 4),
            isset($_POST['open_in_new_tab']) ? 1 : 0,
            $maxSort + 1,
        ]);

        $this->json([
            'success' => true,
            'id' => $db->lastInsertId(),
            'message' => 'Menu item added successfully.',
        ]);
    }

    /**
     * Update a menu item
     */
    public function updateItem(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $item = $db->query("SELECT * FROM menu_items WHERE id = ?", [$id])->fetch();

        if (!$item) {
            $this->json(['success' => false, 'error' => 'Menu item not found.'], 404);
            return;
        }

        $db->query("
            UPDATE menu_items SET
                type = ?, reference_id = ?, title = ?, url = ?, icon = ?,
                badge_text = ?, badge_color = ?, is_mega = ?, mega_columns = ?,
                open_in_new_tab = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ", [
            $_POST['type'] ?? $item['type'],
            !empty($_POST['reference_id']) ? (int) $_POST['reference_id'] : null,
            trim($_POST['title'] ?? $item['title']),
            trim($_POST['url'] ?? '') ?: null,
            trim($_POST['icon'] ?? '') ?: null,
            trim($_POST['badge_text'] ?? '') ?: null,
            trim($_POST['badge_color'] ?? '') ?: null,
            isset($_POST['is_mega']) ? 1 : 0,
            (int) ($_POST['mega_columns'] ?? 4),
            isset($_POST['open_in_new_tab']) ? 1 : 0,
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);

        $this->json(['success' => true, 'message' => 'Menu item updated successfully.']);
    }

    /**
     * Delete a menu item
     */
    public function deleteItem(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        // Delete item and children (recursive via FK cascade)
        $db->query("DELETE FROM menu_items WHERE id = ?", [$id]);

        $this->json(['success' => true, 'message' => 'Menu item deleted successfully.']);
    }

    /**
     * Reorder menu items
     */
    public function reorder(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $items = $_POST['items'] ?? [];

        if (empty($items)) {
            $this->json(['success' => false, 'error' => 'No items provided.'], 400);
            return;
        }

        $db = db();

        foreach ($items as $index => $item) {
            $db->query("
                UPDATE menu_items SET
                    parent_id = ?, sort_order = ?, updated_at = NOW()
                WHERE id = ?
            ", [
                !empty($item['parent_id']) ? (int) $item['parent_id'] : null,
                (int) $index,
                (int) $item['id'],
            ]);
        }

        $this->json(['success' => true, 'message' => 'Menu order updated successfully.']);
    }

    private function buildTree(array $items, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }

        return $branch;
    }

    private function updateMenuItems(int $menuId, array $items, ?int $parentId = null, int &$order = 0): void
    {
        $db = db();

        foreach ($items as $item) {
            $db->query("
                UPDATE menu_items SET
                    parent_id = ?, sort_order = ?, updated_at = NOW()
                WHERE id = ? AND menu_id = ?
            ", [$parentId, $order++, $item['id'], $menuId]);

            if (!empty($item['children'])) {
                $this->updateMenuItems($menuId, $item['children'], $item['id'], $order);
            }
        }
    }
}
