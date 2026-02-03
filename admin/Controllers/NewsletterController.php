<?php
/**
 * Admin Newsletter Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class NewsletterController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $sql = "SELECT * FROM newsletter_subscribers WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if ($search) {
            $sql .= " AND email LIKE ?";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $subscribers = $stmt->fetchAll();

        // Stats
        $stmt = $db->query("SELECT
            COUNT(*) as total,
            SUM(status = 'subscribed') as active,
            SUM(status = 'unsubscribed') as unsubscribed,
            SUM(DATE(created_at) = CURDATE()) as today
            FROM newsletter_subscribers");
        $stats = $stmt->fetch();

        $this->layout('admin');
        $this->view('pages/newsletter/index', [
            'page_title' => 'Newsletter Subscribers',
            'active_page' => 'newsletter',
            'subscribers' => $subscribers,
            'stats' => $stats,
            'filters' => ['status' => $status, 'search' => $search],
        ]);
    }

    public function export(): void
    {
        $db = Database::getInstance();
        $status = $_GET['status'] ?? 'subscribed';

        $sql = "SELECT email, status, subscribed_at, created_at FROM newsletter_subscribers";
        if ($status !== 'all') {
            $sql .= " WHERE status = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$status]);
        } else {
            $stmt = $db->query($sql);
        }
        $subscribers = $stmt->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Email', 'Status', 'Subscribed Date', 'Created Date']);

        foreach ($subscribers as $sub) {
            fputcsv($output, [
                $sub['email'],
                $sub['status'],
                $sub['subscribed_at'] ?? '',
                $sub['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Subscriber deleted successfully');
        $this->redirect('/admin/newsletter');
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);

        $viewPath = ADMIN_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View '$view' not found");
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
