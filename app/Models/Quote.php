<?php
/**
 * Quote Model
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Quote extends Model
{
    protected static string $table = 'quotes';

    protected static array $fillable = [
        'user_id', 'quote_number', 'status', 'title',
        'subtotal', 'tax_amount', 'total',
        'contact_name', 'contact_email', 'contact_phone', 'company_name',
        'customer_notes', 'admin_notes', 'valid_until',
        'converted_order_id', 'converted_at'
    ];

    /**
     * Find by quote number
     */
    public static function findByQuoteNumber(string $quoteNumber): ?self
    {
        return self::where('quote_number', $quoteNumber)->first();
    }

    /**
     * Get quote items
     */
    public function getItems(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT qi.*, p.slug as product_slug,
                   (SELECT pi.path FROM product_images pi WHERE pi.product_id = qi.product_id AND pi.is_primary = 1 LIMIT 1) as product_image
            FROM quote_items qi
            LEFT JOIN products p ON p.id = qi.product_id
            WHERE qi.quote_id = ?
            ORDER BY qi.id ASC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /**
     * Add item to quote
     */
    public function addItem(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO quote_items (quote_id, product_id, variant_id, sku, name, variant_name, quantity, price, total, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->id,
            $data['product_id'] ?? null,
            $data['variant_id'] ?? null,
            $data['sku'] ?? null,
            $data['name'],
            $data['variant_name'] ?? null,
            $data['quantity'],
            $data['price'],
            $data['price'] * $data['quantity'],
            $data['notes'] ?? null,
        ]);
        return (int) $db->lastInsertId();
    }

    /**
     * Remove item from quote
     */
    public function removeItem(int $itemId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM quote_items WHERE id = ? AND quote_id = ?");
        $stmt->execute([$itemId, $this->id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Recalculate totals from items
     */
    public function recalculateTotals(): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) as subtotal FROM quote_items WHERE quote_id = ?");
        $stmt->execute([$this->id]);
        $subtotal = (float) $stmt->fetchColumn();

        $taxRate = config('payment.tax.rate', 15);
        $taxInclusive = config('payment.tax.inclusive', true);

        if ($taxInclusive) {
            $taxAmount = $subtotal * ($taxRate / (100 + $taxRate));
        } else {
            $taxAmount = $subtotal * ($taxRate / 100);
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = round($taxAmount, 2);
        $this->total = $subtotal;
        $this->save();
    }

    /**
     * Get the user who created this quote
     */
    public function getUser(): ?User
    {
        if (!$this->user_id) {
            return null;
        }
        return User::find($this->user_id);
    }

    /**
     * Check if quote can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'submitted'], true);
    }

    /**
     * Check if quote is expired
     */
    public function isExpired(): bool
    {
        if (!$this->valid_until) {
            return false;
        }
        return strtotime($this->valid_until) < strtotime('today');
    }

    /**
     * Get status label with formatting info
     */
    public function getStatusLabel(): array
    {
        $labels = [
            'draft'     => ['label' => 'Draft',     'color' => 'neutral'],
            'submitted' => ['label' => 'Submitted', 'color' => 'info'],
            'reviewed'  => ['label' => 'Reviewed',  'color' => 'warning'],
            'accepted'  => ['label' => 'Accepted',  'color' => 'success'],
            'rejected'  => ['label' => 'Rejected',  'color' => 'danger'],
            'expired'   => ['label' => 'Expired',   'color' => 'neutral'],
            'converted' => ['label' => 'Converted', 'color' => 'success'],
        ];
        return $labels[$this->status] ?? ['label' => ucfirst($this->status), 'color' => 'neutral'];
    }

    /**
     * Get item count
     */
    public function getItemCount(): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM quote_items WHERE quote_id = ?");
        $stmt->execute([$this->id]);
        return (int) $stmt->fetchColumn();
    }
}
