<?php
/**
 * Quote Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Handles customer quote requests - creating, viewing, and managing quotes.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Quote;
use App\Models\User;
use PDOException;

class QuoteController extends Controller
{
    private ?User $currentUser = null;

    public function __construct()
    {
        $this->requireAuth();
        $this->loadCurrentUser();
    }

    private function loadCurrentUser(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->currentUser = User::find($_SESSION['user_id']);
        }
    }

    /**
     * List user's quotes
     */
    public function index(): void
    {
        if (!$this->currentUser) {
            $this->redirect('/login');
            return;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $status = $_GET['status'] ?? '';

        $quotes = [];
        $total = 0;

        try {
            $db = Database::getInstance();

            $whereClause = "WHERE user_id = ?";
            $params = [$this->currentUser->id];

            if ($status && in_array($status, ['draft', 'submitted', 'reviewed', 'accepted', 'rejected', 'expired', 'converted'])) {
                $whereClause .= " AND status = ?";
                $params[] = $status;
            }

            $stmt = $db->prepare("SELECT COUNT(*) FROM quotes $whereClause");
            $stmt->execute($params);
            $total = (int) $stmt->fetchColumn();

            $params[] = $perPage;
            $params[] = $offset;
            $stmt = $db->prepare("
                SELECT q.*, (SELECT COUNT(*) FROM quote_items qi WHERE qi.quote_id = q.id) as item_count
                FROM quotes q
                $whereClause
                ORDER BY q.updated_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($params);
            $quotes = $stmt->fetchAll();

        } catch (PDOException $e) {
            logMessage('error', 'Could not fetch quotes: ' . $e->getMessage());
            flash('error', 'Unable to load quotes. Please try again.');
        }

        $this->layout('main');
        $this->view('pages/account/quotes', [
            'meta_title' => 'My Quotes | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'quotes' => $quotes,
            'currentStatus' => $status,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    /**
     * Show create quote form
     */
    public function create(): void
    {
        if (!$this->currentUser) {
            $this->redirect('/login');
            return;
        }

        $this->layout('main');
        $this->view('pages/account/quote-create', [
            'meta_title' => 'New Quote | ' . config('app.name'),
            'meta_robots' => 'noindex, nofollow',
            'user' => $this->currentUser,
        ]);
    }

    /**
     * Store a new quote
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        if (!$this->currentUser) {
            $this->redirect('/login');
            return;
        }

        $validation = $this->validate([
            'title' => 'required|min:2|max:255',
        ]);

        if (!$validation['valid']) {
            flash('error', 'Please provide a title for your quote.');
            $this->redirect('/account/quotes/create');
            return;
        }

        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            $quote = Quote::create([
                'user_id' => $this->currentUser->id,
                'quote_number' => generateQuoteNumber(),
                'status' => 'draft',
                'title' => trim($_POST['title']),
                'contact_name' => trim($_POST['contact_name'] ?? '') ?: ($this->currentUser->first_name . ' ' . $this->currentUser->last_name),
                'contact_email' => trim($_POST['contact_email'] ?? '') ?: $this->currentUser->email,
                'contact_phone' => trim($_POST['contact_phone'] ?? '') ?: $this->currentUser->phone,
                'company_name' => trim($_POST['company_name'] ?? '') ?: null,
                'customer_notes' => trim($_POST['customer_notes'] ?? '') ?: null,
                'valid_until' => !empty($_POST['valid_until']) ? $_POST['valid_until'] : date('Y-m-d', strtotime('+30 days')),
                'subtotal' => 0,
                'tax_amount' => 0,
                'total' => 0,
            ]);

            // Add items if provided
            $productIds = $_POST['product_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            $itemNotes = $_POST['item_notes'] ?? [];

            if (!empty($productIds)) {
                foreach ($productIds as $i => $productId) {
                    $productId = (int) $productId;
                    $quantity = max(1, (int) ($quantities[$i] ?? 1));

                    $stmt = $db->prepare("SELECT id, sku, name, price FROM products WHERE id = ? AND status = 'active'");
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch();

                    if ($product) {
                        $quote->addItem([
                            'product_id' => $product['id'],
                            'sku' => $product['sku'],
                            'name' => $product['name'],
                            'quantity' => $quantity,
                            'price' => $product['price'],
                            'notes' => trim($itemNotes[$i] ?? '') ?: null,
                        ]);
                    }
                }
                $quote->recalculateTotals();
            }

            $db->commit();

            flash('success', 'Quote created. Add products to your quote.');
            $this->redirect('/account/quotes/' . $quote->id);

        } catch (PDOException $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            logMessage('error', 'Failed to create quote: ' . $e->getMessage());
            flash('error', 'Failed to create quote. Please try again.');
            $this->redirect('/account/quotes/create');
        }
    }

    /**
     * View a single quote
     */
    public function show(string $id): void
    {
        if (!$this->currentUser) {
            $this->redirect('/login');
            return;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote || $quote->user_id !== $this->currentUser->id) {
                flash('error', 'Quote not found.');
                $this->redirect('/account/quotes');
                return;
            }

            $items = $quote->getItems();

            $this->layout('main');
            $this->view('pages/account/quote-detail', [
                'meta_title' => 'Quote #' . $quote->quote_number . ' | ' . config('app.name'),
                'meta_robots' => 'noindex, nofollow',
                'quote' => $quote,
                'items' => $items,
            ]);

        } catch (PDOException $e) {
            logMessage('error', 'Could not fetch quote: ' . $e->getMessage());
            flash('error', 'Quote not found.');
            $this->redirect('/account/quotes');
        }
    }

    /**
     * Add item to existing quote (AJAX)
     */
    public function addItem(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote || $quote->user_id !== $this->currentUser->id) {
                $this->json(['success' => false, 'error' => 'Quote not found'], 404);
                return;
            }

            if (!$quote->isEditable()) {
                $this->json(['success' => false, 'error' => 'This quote can no longer be edited'], 422);
                return;
            }

            $productId = (int) ($_POST['product_id'] ?? 0);
            $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id, sku, name, price FROM products WHERE id = ? AND status = 'active'");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                $this->json(['success' => false, 'error' => 'Product not found'], 404);
                return;
            }

            // Check if product already exists in quote - update quantity instead
            $stmt = $db->prepare("SELECT id, quantity FROM quote_items WHERE quote_id = ? AND product_id = ? AND variant_id IS NULL");
            $stmt->execute([$quote->id, $productId]);
            $existing = $stmt->fetch();

            if ($existing) {
                $newQty = $existing['quantity'] + $quantity;
                $stmt = $db->prepare("UPDATE quote_items SET quantity = ?, total = price * ? WHERE id = ?");
                $stmt->execute([$newQty, $newQty, $existing['id']]);
            } else {
                $quote->addItem([
                    'product_id' => $product['id'],
                    'sku' => $product['sku'],
                    'name' => $product['name'],
                    'quantity' => $quantity,
                    'price' => $product['price'],
                    'notes' => trim($_POST['notes'] ?? '') ?: null,
                ]);
            }

            $quote->recalculateTotals();

            $this->json([
                'success' => true,
                'message' => $product['name'] . ' added to quote',
                'totals' => [
                    'subtotal' => $quote->subtotal,
                    'tax_amount' => $quote->tax_amount,
                    'total' => $quote->total,
                    'item_count' => $quote->getItemCount(),
                ],
            ]);

        } catch (PDOException $e) {
            logMessage('error', 'Failed to add item to quote: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to add item'], 500);
        }
    }

    /**
     * Update item quantity (AJAX)
     */
    public function updateItem(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote || $quote->user_id !== $this->currentUser->id) {
                $this->json(['success' => false, 'error' => 'Quote not found'], 404);
                return;
            }

            if (!$quote->isEditable()) {
                $this->json(['success' => false, 'error' => 'This quote can no longer be edited'], 422);
                return;
            }

            $itemId = (int) ($_POST['item_id'] ?? 0);
            $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

            $db = Database::getInstance();
            $stmt = $db->prepare("UPDATE quote_items SET quantity = ?, total = price * ? WHERE id = ? AND quote_id = ?");
            $stmt->execute([$quantity, $quantity, $itemId, $quote->id]);

            $quote->recalculateTotals();

            $this->json([
                'success' => true,
                'totals' => [
                    'subtotal' => $quote->subtotal,
                    'tax_amount' => $quote->tax_amount,
                    'total' => $quote->total,
                ],
            ]);

        } catch (PDOException $e) {
            logMessage('error', 'Failed to update quote item: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to update item'], 500);
        }
    }

    /**
     * Remove item from quote (AJAX)
     */
    public function removeItem(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote || $quote->user_id !== $this->currentUser->id) {
                $this->json(['success' => false, 'error' => 'Quote not found'], 404);
                return;
            }

            if (!$quote->isEditable()) {
                $this->json(['success' => false, 'error' => 'This quote can no longer be edited'], 422);
                return;
            }

            $itemId = (int) ($_POST['item_id'] ?? 0);
            $removed = $quote->removeItem($itemId);

            if ($removed) {
                $quote->recalculateTotals();
            }

            $this->json([
                'success' => $removed,
                'totals' => [
                    'subtotal' => $quote->subtotal,
                    'tax_amount' => $quote->tax_amount,
                    'total' => $quote->total,
                    'item_count' => $quote->getItemCount(),
                ],
            ]);

        } catch (PDOException $e) {
            logMessage('error', 'Failed to remove quote item: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to remove item'], 500);
        }
    }

    /**
     * Submit quote for review
     */
    public function submit(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote || $quote->user_id !== $this->currentUser->id) {
                flash('error', 'Quote not found.');
                $this->redirect('/account/quotes');
                return;
            }

            if ($quote->status !== 'draft') {
                flash('error', 'This quote has already been submitted.');
                $this->redirect('/account/quotes/' . $quote->id);
                return;
            }

            if ($quote->getItemCount() === 0) {
                flash('error', 'Please add at least one product to your quote before submitting.');
                $this->redirect('/account/quotes/' . $quote->id);
                return;
            }

            $quote->status = 'submitted';
            $quote->save();

            flash('success', 'Your quote has been submitted for review.');
            $this->redirect('/account/quotes/' . $quote->id);

        } catch (PDOException $e) {
            logMessage('error', 'Failed to submit quote: ' . $e->getMessage());
            flash('error', 'Failed to submit quote. Please try again.');
            $this->redirect('/account/quotes/' . $id);
        }
    }

    /**
     * Printable quote view
     */
    public function printView(string $id): void
    {
        if (!$this->currentUser) {
            http_response_code(401);
            exit;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote || $quote->user_id !== $this->currentUser->id) {
                http_response_code(404);
                exit;
            }

            $items = $quote->getItems();

            header('Content-Type: text/html; charset=utf-8');
            include APP_PATH . '/Views/pages/account/quote-print.php';
            exit;

        } catch (PDOException $e) {
            logMessage('error', 'Could not generate quote print view: ' . $e->getMessage());
            http_response_code(500);
            exit;
        }
    }

    /**
     * Delete a draft quote
     */
    public function destroy(string $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        try {
            $quote = Quote::find((int) $id);

            if (!$quote || $quote->user_id !== $this->currentUser->id) {
                flash('error', 'Quote not found.');
                $this->redirect('/account/quotes');
                return;
            }

            if ($quote->status !== 'draft') {
                flash('error', 'Only draft quotes can be deleted.');
                $this->redirect('/account/quotes');
                return;
            }

            $quote->delete();

            flash('success', 'Quote deleted.');
            $this->redirect('/account/quotes');

        } catch (PDOException $e) {
            logMessage('error', 'Failed to delete quote: ' . $e->getMessage());
            flash('error', 'Failed to delete quote.');
            $this->redirect('/account/quotes');
        }
    }

    /**
     * Search products for the quote builder (AJAX)
     */
    public function searchProducts(): void
    {
        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            $this->json(['products' => []]);
            return;
        }

        try {
            $db = Database::getInstance();
            $searchTerm = '%' . $query . '%';
            $stmt = $db->prepare("
                SELECT p.id, p.sku, p.name, p.price, p.compare_price, p.stock_quantity, p.manage_stock,
                       (SELECT pi.path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as image
                FROM products p
                WHERE p.status = 'active'
                AND (p.name LIKE ? OR p.sku LIKE ?)
                ORDER BY p.name ASC
                LIMIT 20
            ");
            $stmt->execute([$searchTerm, $searchTerm]);
            $products = $stmt->fetchAll();

            $this->json(['products' => $products]);

        } catch (PDOException $e) {
            logMessage('error', 'Product search failed: ' . $e->getMessage());
            $this->json(['products' => []]);
        }
    }
}
