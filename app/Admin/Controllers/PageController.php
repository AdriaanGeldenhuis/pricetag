<?php
declare(strict_types=1);

/**
 * Admin Page Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Manages CMS pages (About, Contact, Terms, etc.)
 */

namespace Admin\Controllers;

use App\Core\Controller;

class PageController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get filter parameters
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        // Build query
        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = '(title LIKE ? OR slug LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status && in_array($status, ['draft', 'published'])) {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $total = (int) $db->query("SELECT COUNT(*) FROM pages WHERE {$whereClause}", $params)->fetchColumn();
        $totalPages = (int) ceil($total / $perPage);

        // Get pages
        $offset = ($page - 1) * $perPage;
        $pages = $db->query("
            SELECT * FROM pages
            WHERE {$whereClause}
            ORDER BY created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params)->fetchAll();

        // Get status counts
        $statusCounts = $db->query("
            SELECT status, COUNT(*) as count
            FROM pages
            GROUP BY status
        ")->fetchAll(\PDO::FETCH_KEY_PAIR);

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/pages/index', [
            'title' => 'Pages',
            'pages' => $pages,
            'statusCounts' => $statusCounts,
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'count' => $total,
                'perPage' => $perPage,
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): void
    {
        $this->layout('admin/layouts/main');
        $this->view('admin/pages/pages/form', [
            'title' => 'Add Page',
            'page' => null,
            'templates' => $this->getTemplates(),
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'title' => 'required|min:2|max:255',
            'content' => 'required',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/admin/pages/create');
            return;
        }

        $db = db();

        // Generate slug
        $slug = $this->generateSlug($_POST['title']);

        // Insert page
        $db->query("
            INSERT INTO pages (
                title, slug, content, excerpt, meta_title, meta_description,
                template, status, published_at, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [
            trim($_POST['title']),
            $slug,
            $_POST['content'],
            trim($_POST['excerpt'] ?? '') ?: null,
            trim($_POST['meta_title'] ?? '') ?: null,
            trim($_POST['meta_description'] ?? '') ?: null,
            $_POST['template'] ?? 'default',
            $_POST['status'] ?? 'draft',
            $_POST['status'] === 'published' ? date('Y-m-d H:i:s') : null,
        ]);

        $pageId = $db->lastInsertId();

        flash('success', 'Page created successfully.');
        $this->redirect('/admin/pages/' . $pageId . '/edit');
    }

    public function edit(int $id): void
    {
        $db = db();

        $page = $db->query("SELECT * FROM pages WHERE id = ?", [$id])->fetch();

        if (!$page) {
            flash('error', 'Page not found.');
            $this->redirect('/admin/pages');
            return;
        }

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/pages/form', [
            'title' => 'Edit Page',
            'page' => $page,
            'templates' => $this->getTemplates(),
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $page = $db->query("SELECT * FROM pages WHERE id = ?", [$id])->fetch();

        if (!$page) {
            flash('error', 'Page not found.');
            $this->redirect('/admin/pages');
            return;
        }

        $validation = $this->validate([
            'title' => 'required|min:2|max:255',
            'content' => 'required',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please correct the errors below.');
            $_SESSION['form_errors'] = $validation['errors'];
            $this->redirect('/admin/pages/' . $id . '/edit');
            return;
        }

        // Update slug if title changed
        $slug = $page['slug'];
        if ($_POST['title'] !== $page['title']) {
            $slug = $this->generateSlug($_POST['title'], $id);
        }

        // Handle published_at
        $publishedAt = $page['published_at'];
        if ($_POST['status'] === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        // Update page
        $db->query("
            UPDATE pages SET
                title = ?, slug = ?, content = ?, excerpt = ?,
                meta_title = ?, meta_description = ?, template = ?,
                status = ?, published_at = ?, updated_at = NOW()
            WHERE id = ?
        ", [
            trim($_POST['title']),
            $slug,
            $_POST['content'],
            trim($_POST['excerpt'] ?? '') ?: null,
            trim($_POST['meta_title'] ?? '') ?: null,
            trim($_POST['meta_description'] ?? '') ?: null,
            $_POST['template'] ?? 'default',
            $_POST['status'] ?? 'draft',
            $publishedAt,
            $id,
        ]);

        flash('success', 'Page updated successfully.');
        $this->redirect('/admin/pages/' . $id . '/edit');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $page = $db->query("SELECT * FROM pages WHERE id = ?", [$id])->fetch();

        if (!$page) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Page not found.'], 404);
                return;
            }
            flash('error', 'Page not found.');
            $this->redirect('/admin/pages');
            return;
        }

        $db->query("DELETE FROM pages WHERE id = ?", [$id]);

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'Page deleted successfully.']);
            return;
        }

        flash('success', 'Page deleted successfully.');
        $this->redirect('/admin/pages');
    }

    /**
     * Preview a page
     */
    public function preview(int $id): void
    {
        $db = db();

        $page = $db->query("SELECT * FROM pages WHERE id = ?", [$id])->fetch();

        if (!$page) {
            flash('error', 'Page not found.');
            $this->redirect('/admin/pages');
            return;
        }

        // Render page with main layout
        $this->layout('main');
        $this->view('pages/page', [
            'meta_title' => $page['meta_title'] ?: $page['title'],
            'meta_description' => $page['meta_description'] ?: $page['excerpt'],
            'page' => $page,
            'preview' => true,
        ]);
    }

    /**
     * Duplicate a page
     */
    public function duplicate(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $page = $db->query("SELECT * FROM pages WHERE id = ?", [$id])->fetch();

        if (!$page) {
            flash('error', 'Page not found.');
            $this->redirect('/admin/pages');
            return;
        }

        // Generate new slug
        $slug = $this->generateSlug($page['title'] . ' Copy');

        // Insert duplicate
        $db->query("
            INSERT INTO pages (
                title, slug, content, excerpt, meta_title, meta_description,
                template, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', NOW(), NOW())
        ", [
            $page['title'] . ' (Copy)',
            $slug,
            $page['content'],
            $page['excerpt'],
            $page['meta_title'],
            $page['meta_description'],
            $page['template'],
        ]);

        $newId = $db->lastInsertId();

        flash('success', 'Page duplicated successfully.');
        $this->redirect('/admin/pages/' . $newId . '/edit');
    }

    private function generateSlug(string $title, ?int $excludeId = null): string
    {
        $db = db();
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM pages WHERE slug = ?";
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

    private function getTemplates(): array
    {
        return [
            'default' => 'Default',
            'full-width' => 'Full Width',
            'sidebar' => 'With Sidebar',
            'landing' => 'Landing Page',
            'contact' => 'Contact Page',
            'faq' => 'FAQ Page',
        ];
    }
}
