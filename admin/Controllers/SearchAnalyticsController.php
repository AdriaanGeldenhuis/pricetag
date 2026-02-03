<?php
/**
 * Admin Search Analytics Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SearchAnalyticsController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        // Top search terms
        $stmt = $db->query("
            SELECT * FROM search_terms
            ORDER BY count DESC
            LIMIT 50
        ");
        $topSearches = $stmt->fetchAll();

        // Zero-result searches
        $stmt = $db->query("
            SELECT * FROM search_terms
            WHERE results_count = 0
            ORDER BY count DESC
            LIMIT 20
        ");
        $zeroResults = $stmt->fetchAll();

        // Search synonyms
        $stmt = $db->query("SELECT * FROM search_synonyms ORDER BY id DESC");
        $synonyms = $stmt->fetchAll();

        // Stats
        $stmt = $db->query("SELECT
            COUNT(*) as total_terms,
            SUM(count) as total_searches,
            SUM(results_count = 0) as zero_result_terms
            FROM search_terms");
        $stats = $stmt->fetch();

        $this->layout('admin');
        $this->view('pages/search/index', [
            'page_title' => 'Search Analytics',
            'active_page' => 'search',
            'topSearches' => $topSearches,
            'zeroResults' => $zeroResults,
            'synonyms' => $synonyms,
            'stats' => $stats,
        ]);
    }

    public function storeSynonym(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $terms = trim($_POST['terms'] ?? '');

        if (!$terms) {
            flash('error', 'Terms are required');
            $this->redirect('/admin/search');
            return;
        }

        $stmt = $db->prepare("INSERT INTO search_synonyms (terms) VALUES (?)");
        $stmt->execute([$terms]);

        flash('success', 'Synonym group added successfully');
        $this->redirect('/admin/search');
    }

    public function destroySynonym(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM search_synonyms WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Synonym group deleted');
        $this->redirect('/admin/search');
    }

    public function addRedirect(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $redirectUrl = trim($_POST['redirect_url'] ?? '');

        $stmt = $db->prepare("UPDATE search_terms SET redirect_url = ? WHERE id = ?");
        $stmt->execute([$redirectUrl ?: null, $id]);

        flash('success', 'Search redirect updated');
        $this->redirect('/admin/search');
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        $viewPath = ADMIN_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        if (isset($this->data['_layout'])) {
            include ADMIN_PATH . '/Views/layouts/' . $this->data['_layout'] . '.php';
            return;
        }
        echo $content;
    }

    protected function layout(string $layout): self
    {
        $this->data['_layout'] = $layout;
        return $this;
    }
}
