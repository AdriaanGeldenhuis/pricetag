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
use App\Core\Database;

class MenuController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        // Get all menus with item counts
        $stmt = $db->prepare("
            SELECT m.*, COUNT(mi.id) as item_count
            FROM menus m
            LEFT JOIN menu_items mi ON m.id = mi.menu_id
            GROUP BY m.id
            ORDER BY m.name
        ");
        $stmt->execute();
        $menus = $stmt->fetchAll();

        // Get menu items for each menu
        $menuItems = [];
        foreach ($menus as $menu) {
            $stmt = $db->prepare("
                SELECT mi.*, p.title as page_title, c.name as category_name
                FROM menu_items mi
                LEFT JOIN pages p ON mi.type = 'page' AND mi.reference_id = p.id
                LEFT JOIN categories c ON mi.type = 'category' AND mi.reference_id = c.id
                WHERE mi.menu_id = ?
                ORDER BY mi.sort_order
            ");
            $stmt->execute([$menu['id']]);
            $items = $stmt->fetchAll();

            $menuItems[$menu['id']] = $this->buildTree($items);
        }

        // Get pages and categories for item creation
        $stmt = $db->prepare("SELECT id, title FROM pages WHERE status = 'published' ORDER BY title");
        $stmt->execute();
        $pages = $stmt->fetchAll();

        $stmt = $db->prepare("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        $this->layout('admin');
        $this->view('pages/menus/index', [
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

        $db = Database::getInstance();

        // Check location uniqueness
        $stmt = $db->prepare("SELECT id FROM menus WHERE location = ?");
        $stmt->execute([$_POST['location']]);
        $existing = $stmt->fetch();
        if ($existing) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'A menu with this location already exists.'], 400);
                return;
            }
            flash('error', 'A menu with this location already exists.');
            $this->redirect('/admin/menus');
            return;
        }

        $stmt = $db->prepare("
            INSERT INTO menus (name, location, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
        ");
        $stmt->execute([trim($_POST['name']), trim($_POST['location'])]);

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

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch();

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

        $stmt = $db->prepare("UPDATE menus SET name = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([trim($_POST['name']), $id]);

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

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch();

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
        $stmt = $db->prepare("DELETE FROM menus WHERE id = ?");
        $stmt->execute([$id]);

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

        $db = Database::getInstance();

        // Get max sort order
        $stmt = $db->prepare("SELECT MAX(sort_order) FROM menu_items WHERE menu_id = ? AND parent_id IS NULL");
        $stmt->execute([$menuId]);
        $maxSort = (int) $stmt->fetchColumn();

        $stmt = $db->prepare("
            INSERT INTO menu_items (
                menu_id, parent_id, type, reference_id, title, url, icon,
                badge_text, badge_color, is_mega, mega_columns, open_in_new_tab,
                sort_order, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $stmt->execute([
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

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            $this->json(['success' => false, 'error' => 'Menu item not found.'], 404);
            return;
        }

        $stmt = $db->prepare("
            UPDATE menu_items SET
                type = ?, reference_id = ?, title = ?, url = ?, icon = ?,
                badge_text = ?, badge_color = ?, is_mega = ?, mega_columns = ?,
                open_in_new_tab = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
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

        $db = Database::getInstance();

        // Delete item and children (recursive via FK cascade)
        $stmt = $db->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);

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

        $db = Database::getInstance();

        foreach ($items as $index => $item) {
            $stmt = $db->prepare("
                UPDATE menu_items SET
                    parent_id = ?, sort_order = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
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
        $db = Database::getInstance();

        foreach ($items as $item) {
            $stmt = $db->prepare("
                UPDATE menu_items SET
                    parent_id = ?, sort_order = ?, updated_at = NOW()
                WHERE id = ? AND menu_id = ?
            ");
            $stmt->execute([$parentId, $order++, $item['id'], $menuId]);

            if (!empty($item['children'])) {
                $this->updateMenuItems($menuId, $item['children'], $item['id'], $order);
            }
        }
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        $viewPath = ADMIN_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '$view' not found at '$viewPath'");
        }
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        if (isset($this->data['_layout'])) {
            $layoutPath = ADMIN_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
                return;
            }
        }
        echo $content;
    }

    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }
}
