<?php
/**
 * Admin Attribute Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 */

namespace Admin\Controllers;

use App\Core\Controller;
use App\Core\Database;

class AttributeController extends Controller
{
    public function index(): void
    {
        $db = Database::getInstance();

        // Get all attributes with value counts
        $stmt = $db->query("
            SELECT a.*,
                   COUNT(av.id) as value_count
            FROM attributes a
            LEFT JOIN attribute_values av ON a.id = av.attribute_id
            GROUP BY a.id
            ORDER BY a.sort_order, a.name
        ");
        $attributes = $stmt->fetchAll();

        // Get attribute values grouped by attribute
        $attributeValues = [];
        foreach ($attributes as $attr) {
            $stmt = $db->prepare("SELECT * FROM attribute_values WHERE attribute_id = ? ORDER BY sort_order, value");
            $stmt->execute([$attr['id']]);
            $attributeValues[$attr['id']] = $stmt->fetchAll();
        }

        $this->layout('admin');
        $this->view('pages/attributes/index', [
            'page_title' => 'Attributes',
            'active_page' => 'attributes',
            'attributes' => $attributes,
            'attributeValues' => $attributeValues,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'select';

        if (empty($name)) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Name is required'], 422);
                return;
            }
            flash('error', 'Name is required');
            $this->redirect('/admin/attributes');
            return;
        }

        $db = Database::getInstance();

        // Generate slug
        $slug = slugify($name);

        // Get max sort order
        $stmt = $db->query("SELECT MAX(sort_order) FROM attributes");
        $maxSort = (int) $stmt->fetchColumn();

        // Insert attribute
        $stmt = $db->prepare("
            INSERT INTO attributes (name, slug, type, is_filterable, is_visible, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $name,
            $slug,
            $type,
            isset($_POST['is_filterable']) ? 1 : 0,
            isset($_POST['is_visible']) ? 1 : 0,
            $maxSort + 1,
        ]);

        if (isAjax()) {
            $this->json(['success' => true, 'id' => $db->lastInsertId(), 'message' => 'Attribute created']);
            return;
        }

        flash('success', 'Attribute created successfully');
        $this->redirect('/admin/attributes');
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM attributes WHERE id = ?");
        $stmt->execute([$id]);
        $attribute = $stmt->fetch();

        if (!$attribute) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Attribute not found'], 404);
                return;
            }
            flash('error', 'Attribute not found');
            $this->redirect('/admin/attributes');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Name is required'], 422);
                return;
            }
            flash('error', 'Name is required');
            $this->redirect('/admin/attributes');
            return;
        }

        // Update slug if name changed
        $slug = $attribute['slug'];
        if ($name !== $attribute['name']) {
            $slug = slugify($name);
        }

        $stmt = $db->prepare("
            UPDATE attributes SET name = ?, slug = ?, type = ?, is_filterable = ?, is_visible = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $name,
            $slug,
            $_POST['type'] ?? $attribute['type'],
            isset($_POST['is_filterable']) ? 1 : 0,
            isset($_POST['is_visible']) ? 1 : 0,
            $id,
        ]);

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'Attribute updated']);
            return;
        }

        flash('success', 'Attribute updated successfully');
        $this->redirect('/admin/attributes');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        // Check if attribute is in use
        $stmt = $db->prepare("SELECT COUNT(*) FROM product_attributes WHERE attribute_id = ?");
        $stmt->execute([$id]);
        $inUse = (int) $stmt->fetchColumn();

        if ($inUse > 0) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => "Cannot delete - used by {$inUse} products"], 400);
                return;
            }
            flash('error', "Cannot delete - attribute is used by {$inUse} products");
            $this->redirect('/admin/attributes');
            return;
        }

        $stmt = $db->prepare("DELETE FROM attributes WHERE id = ?");
        $stmt->execute([$id]);

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'Attribute deleted']);
            return;
        }

        flash('success', 'Attribute deleted successfully');
        $this->redirect('/admin/attributes');
    }

    public function storeValue(int $attributeId): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $value = trim($_POST['value'] ?? '');
        if (empty($value)) {
            $this->json(['success' => false, 'error' => 'Value is required'], 422);
            return;
        }

        $db = Database::getInstance();

        $slug = slugify($value);

        $stmt = $db->prepare("SELECT MAX(sort_order) FROM attribute_values WHERE attribute_id = ?");
        $stmt->execute([$attributeId]);
        $maxSort = (int) $stmt->fetchColumn();

        $stmt = $db->prepare("
            INSERT INTO attribute_values (attribute_id, value, slug, color_code, sort_order, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $attributeId,
            $value,
            $slug,
            $_POST['color_code'] ?? null,
            $maxSort + 1,
        ]);

        $this->json(['success' => true, 'id' => $db->lastInsertId(), 'message' => 'Value added']);
    }

    public function updateValue(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $value = trim($_POST['value'] ?? '');
        if (empty($value)) {
            $this->json(['success' => false, 'error' => 'Value is required'], 422);
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("UPDATE attribute_values SET value = ?, slug = ?, color_code = ? WHERE id = ?");
        $stmt->execute([$value, slugify($value), $_POST['color_code'] ?? null, $id]);

        $this->json(['success' => true, 'message' => 'Value updated']);
    }

    public function destroyValue(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = Database::getInstance();

        $stmt = $db->prepare("DELETE FROM attribute_values WHERE id = ?");
        $stmt->execute([$id]);

        $this->json(['success' => true, 'message' => 'Value deleted']);
    }
}
