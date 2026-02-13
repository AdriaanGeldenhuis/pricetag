<?php
/**
 * Admin Quote Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles admin-side quote management - viewing, updating status, adding notes.
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Quote;
use PDOException;

class QuoteController extends Controller
{
    public function __construct()
    {
        $this->requireAdmin();
    }

    /**
     * List all quotes
     */
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');

        $quotes = [];
        $total = 0;

        try {
            $db = Database::getInstance();

            $whereClause = "WHERE 1=1";
            $params = [];

            if ($status && in_array($status, ['draft', 'submitted', 'reviewed', 'accepted', 'rejected', 'expired', 'converted'])) {
                $whereClause .= " AND q.status = ?";
                $params[] = $status;
            }

            if ($search) {
                $whereClause .= " AND (q.quote_number LIKE ? OR q.title LIKE ? OR q.contact_name LIKE ? OR q.company_name LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            $stmt = $db->prepare("SELECT COUNT(*) FROM quotes q $whereClause");
            $stmt->execute($params);
            $total = (int) $stmt->fetchColumn();

            $params[] = $perPage;
            $params[] = $offset;
            $stmt = $db->prepare("
                SELECT q.*,
                       u.first_name, u.last_name, u.email as user_email,
                       (SELECT COUNT(*) FROM quote_items qi WHERE qi.quote_id = q.id) as item_count
                FROM quotes q
                LEFT JOIN users u ON u.id = q.user_id
                $whereClause
                ORDER BY q.updated_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($params);
            $quotes = $stmt->fetchAll();

        } catch (PDOException $e) {
            logMessage('error', 'Admin: Could not fetch quotes: ' . $e->getMessage());
            flash('error', 'Unable to load quotes.');
        }

        // Count by status for tabs
        $statusCounts = [];
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT status, COUNT(*) as cnt FROM quotes GROUP BY status");
            while ($row = $stmt->fetch()) {
                $statusCounts[$row['status']] = (int) $row['cnt'];
            }
        } catch (PDOException $e) {
            // Non-critical
        }

        $this->layout('admin');
        $this->view('admin/pages/quotes/index', [
            'meta_title' => 'Quotes | Admin',
            'quotes' => $quotes,
            'currentStatus' => $status,
            'search' => $search,
            'statusCounts' => $statusCounts,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    /**
     * View a single quote
     */
    public function show(string $id): void
    {
        try {
            $quote = Quote::find((int) $id);

            if (!$quote) {
                flash('error', 'Quote not found.');
                $this->redirect('/admin/quotes');
                return;
            }

            $items = $quote->getItems();
            $user = $quote->getUser();

            $this->layout('admin');
            $this->view('admin/pages/quotes/show', [
                'meta_title' => 'Quote #' . $quote->quote_number . ' | Admin',
                'quote' => $quote,
                'items' => $items,
                'quoteUser' => $user,
            ]);

        } catch (PDOException $e) {
            logMessage('error', 'Admin: Could not fetch quote: ' . $e->getMessage());
            flash('error', 'Quote not found.');
            $this->redirect('/admin/quotes');
        }
    }

    /**
     * Update quote status
     */
    public function updateStatus(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote) {
                if (isAjax()) {
                    $this->json(['success' => false, 'error' => 'Quote not found'], 404);
                    return;
                }
                flash('error', 'Quote not found.');
                $this->redirect('/admin/quotes');
                return;
            }

            $newStatus = $_POST['status'] ?? '';
            $validStatuses = ['draft', 'submitted', 'reviewed', 'accepted', 'rejected', 'expired', 'converted'];

            if (!in_array($newStatus, $validStatuses)) {
                if (isAjax()) {
                    $this->json(['success' => false, 'error' => 'Invalid status'], 422);
                    return;
                }
                flash('error', 'Invalid status.');
                $this->redirect('/admin/quotes/' . $id);
                return;
            }

            $quote->status = $newStatus;
            $quote->save();

            if (isAjax()) {
                $this->json(['success' => true, 'message' => 'Status updated to ' . $newStatus]);
                return;
            }

            flash('success', 'Quote status updated to ' . ucfirst($newStatus) . '.');
            $this->redirect('/admin/quotes/' . $id);

        } catch (PDOException $e) {
            logMessage('error', 'Admin: Failed to update quote status: ' . $e->getMessage());
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Failed to update status'], 500);
                return;
            }
            flash('error', 'Failed to update status.');
            $this->redirect('/admin/quotes/' . $id);
        }
    }

    /**
     * Add admin note to quote
     */
    public function addNote(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote) {
                flash('error', 'Quote not found.');
                $this->redirect('/admin/quotes');
                return;
            }

            $note = trim($_POST['admin_notes'] ?? '');

            if ($note) {
                $quote->admin_notes = $note;
                $quote->save();
                flash('success', 'Note added to quote.');
            }

            $this->redirect('/admin/quotes/' . $id);

        } catch (PDOException $e) {
            logMessage('error', 'Admin: Failed to add note to quote: ' . $e->getMessage());
            flash('error', 'Failed to add note.');
            $this->redirect('/admin/quotes/' . $id);
        }
    }
}
