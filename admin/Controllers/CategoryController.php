<?php
/**
 * Admin Category Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(): void
    {
        $categories = Category::getTree();

        $this->layout('admin');
        $this->view('pages/categories/index', [
            'page_title' => 'Categories',
            'active_page' => 'categories',
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        $categories = Category::getTree();

        $this->layout('admin');
        $this->view('pages/categories/form', [
            'page_title' => 'Add Category',
            'active_page' => 'categories',
            'category' => null,
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
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please check your input');
            $this->redirect('/admin/categories/create');
            return;
        }

        $slug = slugify($_POST['name']);

        // Ensure unique slug
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE slug LIKE ?");
        $stmt->execute([$slug . '%']);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $category = Category::create([
            'name' => $_POST['name'],
            'slug' => $slug,
            'description' => $_POST['description'] ?? null,
            'parent_id' => !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null,
            'is_active' => !empty($_POST['is_active']),
            'show_in_menu' => !empty($_POST['show_in_menu']),
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);

        // Handle image upload
        if (!empty($_FILES['image']['tmp_name'])) {
            $this->handleImageUpload($category->id, $_FILES['image']);
        }

        flash('success', 'Category created successfully');
        $this->redirect('/admin/categories');
    }

    public function edit(string $id): void
    {
        $category = Category::find((int) $id);

        if (!$category) {
            flash('error', 'Category not found');
            $this->redirect('/admin/categories');
            return;
        }

        $categories = Category::getTree();

        $this->layout('admin');
        $this->view('pages/categories/form', [
            'page_title' => 'Edit Category',
            'active_page' => 'categories',
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    public function update(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $category = Category::find((int) $id);

        if (!$category) {
            flash('error', 'Category not found');
            $this->redirect('/admin/categories');
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:100',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please check your input');
            $this->redirect('/admin/categories/' . $id . '/edit');
            return;
        }

        // Prevent setting self as parent
        if (!empty($_POST['parent_id']) && (int) $_POST['parent_id'] === (int) $id) {
            flash('error', 'Category cannot be its own parent');
            $this->redirect('/admin/categories/' . $id . '/edit');
            return;
        }

        $category->name = $_POST['name'];
        $category->description = $_POST['description'] ?? null;
        $category->parent_id = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $category->is_active = !empty($_POST['is_active']);
        $category->show_in_menu = !empty($_POST['show_in_menu']);
        $category->meta_title = $_POST['meta_title'] ?? null;
        $category->meta_description = $_POST['meta_description'] ?? null;
        $category->sort_order = (int) ($_POST['sort_order'] ?? 0);
        $category->save();

        // Handle image upload
        if (!empty($_FILES['image']['tmp_name'])) {
            $this->handleImageUpload($category->id, $_FILES['image']);
        }

        flash('success', 'Category updated successfully');
        $this->redirect('/admin/categories/' . $id . '/edit');
    }

    public function destroy(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $category = Category::find((int) $id);

        if ($category) {
            // Check for child categories
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
            $stmt->execute([(int) $id]);
            if ($stmt->fetchColumn() > 0) {
                flash('error', 'Cannot delete category with subcategories');
                $this->redirect('/admin/categories');
                return;
            }

            // Check for products
            $stmt = $db->prepare("SELECT COUNT(*) FROM product_categories WHERE category_id = ?");
            $stmt->execute([(int) $id]);
            if ($stmt->fetchColumn() > 0) {
                flash('error', 'Cannot delete category with products');
                $this->redirect('/admin/categories');
                return;
            }

            $category->delete();
            flash('success', 'Category deleted');
        }

        if (isAjax()) {
            $this->json(['success' => true]);
            return;
        }

        $this->redirect('/admin/categories');
    }

    private function handleImageUpload(int $categoryId, array $file): void
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowedTypes)) {
            flash('error', 'Invalid image type');
            return;
        }

        if ($file['size'] > $maxSize) {
            flash('error', 'Image too large (max 2MB)');
            return;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'category-' . $categoryId . '-' . time() . '.' . $ext;
        $path = 'categories/' . $filename;
        $fullPath = STORAGE_PATH . '/uploads/' . $path;

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            $db = Database::getInstance();
            $stmt = $db->prepare("UPDATE categories SET image = ? WHERE id = ?");
            $stmt->execute([$path, $categoryId]);
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
