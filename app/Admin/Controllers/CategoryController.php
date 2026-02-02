<?php
declare(strict_types=1);

/**
 * Admin Category Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;

class CategoryController extends Controller
{
    public function index(): void
    {
        $db = db();

        $categories = $this->getCategoriesWithCounts($db);

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/categories/index', [
            'title' => 'Categories',
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        $db = db();

        $parentCategories = $db->query("
            SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name
        ")->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/categories/form', [
            'title' => 'Add Category',
            'category' => null,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:100',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/categories/create');
            return;
        }

        $db = db();

        // Generate slug
        $slug = $this->generateSlug($_POST['name']);

        // Handle image upload
        $imagePath = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $this->uploadImage($_FILES['image']);
        }

        // Insert category
        $sql = "
            INSERT INTO categories (name, slug, description, parent_id, image, sort_order, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";

        $db->query($sql, [
            $_POST['name'],
            $slug,
            $_POST['description'] ?? '',
            !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
            $imagePath,
            (int) ($_POST['sort_order'] ?? 0),
            isset($_POST['is_active']) ? 1 : 0,
        ]);

        flash('success', 'Category created successfully.');
        $this->redirect('/admin/categories');
    }

    public function edit(int $id): void
    {
        $db = db();

        $category = $db->query("SELECT * FROM categories WHERE id = ?", [$id])->fetch();

        if (!$category) {
            flash('error', 'Category not found.');
            $this->redirect('/admin/categories');
            return;
        }

        $parentCategories = $db->query("
            SELECT id, name FROM categories WHERE parent_id IS NULL AND id != ? ORDER BY name
        ", [$id])->fetchAll();

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/categories/form', [
            'title' => 'Edit Category',
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $category = $db->query("SELECT * FROM categories WHERE id = ?", [$id])->fetch();

        if (!$category) {
            flash('error', 'Category not found.');
            $this->redirect('/admin/categories');
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:100',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $this->redirect('/admin/categories/' . $id . '/edit');
            return;
        }

        // Update slug if name changed
        $slug = $category['slug'];
        if ($_POST['name'] !== $category['name']) {
            $slug = $this->generateSlug($_POST['name'], $id);
        }

        // Handle image upload
        $imagePath = $category['image'];
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image
            if ($imagePath && file_exists(PUBLIC_PATH . $imagePath)) {
                unlink(PUBLIC_PATH . $imagePath);
            }
            $imagePath = $this->uploadImage($_FILES['image']);
        }

        // Prevent setting itself as parent
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        if ($parentId === $id) {
            $parentId = null;
        }

        // Update category
        $sql = "
            UPDATE categories SET
                name = ?, slug = ?, description = ?, parent_id = ?,
                image = ?, sort_order = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ";

        $db->query($sql, [
            $_POST['name'],
            $slug,
            $_POST['description'] ?? '',
            $parentId,
            $imagePath,
            (int) ($_POST['sort_order'] ?? 0),
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);

        flash('success', 'Category updated successfully.');
        $this->redirect('/admin/categories/' . $id . '/edit');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $category = $db->query("SELECT * FROM categories WHERE id = ?", [$id])->fetch();

        if (!$category) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Category not found'], 404);
                return;
            }
            flash('error', 'Category not found.');
            $this->redirect('/admin/categories');
            return;
        }

        // Check if category has products
        $productCount = (int) $db->query(
            "SELECT COUNT(*) FROM products WHERE category_id = ?",
            [$id]
        )->fetchColumn();

        if ($productCount > 0) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => "Cannot delete category with {$productCount} products"], 400);
                return;
            }
            flash('error', "Cannot delete category. It has {$productCount} products assigned.");
            $this->redirect('/admin/categories');
            return;
        }

        // Check if category has children
        $childCount = (int) $db->query(
            "SELECT COUNT(*) FROM categories WHERE parent_id = ?",
            [$id]
        )->fetchColumn();

        if ($childCount > 0) {
            if (isAjax()) {
                $this->json(['success' => false, 'message' => 'Cannot delete category with subcategories'], 400);
                return;
            }
            flash('error', 'Cannot delete category. It has subcategories.');
            $this->redirect('/admin/categories');
            return;
        }

        // Delete image
        if ($category['image'] && file_exists(PUBLIC_PATH . $category['image'])) {
            unlink(PUBLIC_PATH . $category['image']);
        }

        // Delete category
        $db->query("DELETE FROM categories WHERE id = ?", [$id]);

        if (isAjax()) {
            $this->json(['success' => true]);
            return;
        }

        flash('success', 'Category deleted successfully.');
        $this->redirect('/admin/categories');
    }

    private function getCategoriesWithCounts(\PDO $db, ?int $parentId = null, int $level = 0): array
    {
        $categories = [];
        $sql = "
            SELECT c.*,
                   (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count,
                   (SELECT COUNT(*) FROM categories WHERE parent_id = c.id) as child_count
            FROM categories c
            WHERE c.parent_id " . ($parentId ? "= ?" : "IS NULL") . "
            ORDER BY c.sort_order, c.name
        ";
        $params = $parentId ? [$parentId] : [];
        $rows = $db->query($sql, $params)->fetchAll();

        foreach ($rows as $row) {
            $row['level'] = $level;
            $categories[] = $row;

            if ($row['child_count'] > 0) {
                $children = $this->getCategoriesWithCounts($db, $row['id'], $level + 1);
                $categories = array_merge($categories, $children);
            }
        }

        return $categories;
    }

    private function generateSlug(string $name, ?int $excludeId = null): string
    {
        $db = db();
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM categories WHERE slug = ?";
            $params = [$slug];

            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $existing = $db->query($sql, $params)->fetch();

            if (!$existing) {
                break;
            }

            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function uploadImage(array $file): ?string
    {
        $uploadDir = PUBLIC_PATH . '/uploads/categories/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            return null;
        }

        $filename = 'cat_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $path = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            return '/uploads/categories/' . $filename;
        }

        return null;
    }
}
