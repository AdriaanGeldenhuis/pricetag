<?php
/**
 * Admin Contact Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ContactController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';

        $sql = "SELECT * FROM contact_submissions WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll();

        // Stats
        $stmt = $db->query("SELECT
            COUNT(*) as total,
            SUM(status = 'new') as unread,
            SUM(DATE(created_at) = CURDATE()) as today
            FROM contact_submissions");
        $stats = $stmt->fetch();

        $this->layout('admin');
        $this->view('pages/contact/index', [
            'page_title' => 'Contact Messages',
            'active_page' => 'contact',
            'messages' => $messages,
            'stats' => $stats,
            'filters' => ['status' => $status],
        ]);
    }

    public function show(int $id): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM contact_submissions WHERE id = ?");
        $stmt->execute([$id]);
        $message = $stmt->fetch();

        if (!$message) {
            flash('error', 'Message not found');
            $this->redirect('/admin/contact');
            return;
        }

        // Mark as read
        if ($message['status'] === 'new') {
            $stmt = $db->prepare("UPDATE contact_submissions SET status = 'read', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            $message['status'] = 'read';
        }

        $this->layout('admin');
        $this->view('pages/contact/show', [
            'page_title' => 'View Message',
            'active_page' => 'contact',
            'message' => $message,
        ]);
    }

    public function updateStatus(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $status = $_POST['status'] ?? 'read';

        $stmt = $db->prepare("UPDATE contact_submissions SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);

        flash('success', 'Status updated successfully');
        $this->redirect('/admin/contact/' . $id);
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->execute([$id]);

        flash('success', 'Message deleted successfully');
        $this->redirect('/admin/contact');
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
