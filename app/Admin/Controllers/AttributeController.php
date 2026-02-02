<?php
declare(strict_types=1);

/**
 * Admin Attribute Controller
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Manages product attributes and their values (colors, sizes, etc.)
 */

namespace Admin\Controllers;

use App\Core\Controller;

class AttributeController extends Controller
{
    public function index(): void
    {
        $db = db();

        // Get all attributes with value counts
        $attributes = $db->query("
            SELECT a.*,
                   COUNT(av.id) as value_count,
                   (SELECT COUNT(DISTINCT pa.product_id)
                    FROM product_attributes pa
                    WHERE pa.attribute_id = a.id) as product_count
            FROM attributes a
            LEFT JOIN attribute_values av ON a.id = av.attribute_id
            GROUP BY a.id
            ORDER BY a.sort_order, a.name
        ")->fetchAll();

        // Get attribute values grouped by attribute
        $attributeValues = [];
        foreach ($attributes as $attr) {
            $values = $db->query(
                "SELECT * FROM attribute_values WHERE attribute_id = ? ORDER BY sort_order, value",
                [$attr['id']]
            )->fetchAll();
            $attributeValues[$attr['id']] = $values;
        }

        $this->layout('admin/layouts/main');
        $this->view('admin/pages/attributes/index', [
            'title' => 'Attributes',
            'attributes' => $attributes,
            'attributeValues' => $attributeValues,
        ]);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:255',
            'type' => 'required',
        ]);

        if (!$validation['valid']) {
            if (isAjax()) {
                $this->json(['success' => false, 'errors' => $validation['errors']], 422);
                return;
            }
            flash('error', 'Please correct the errors below.');
            $this->redirect('/admin/attributes');
            return;
        }

        $db = db();

        // Generate slug
        $slug = $this->generateSlug($_POST['name']);

        // Get max sort order
        $maxSort = (int) $db->query("SELECT MAX(sort_order) FROM attributes")->fetchColumn();

        // Insert attribute
        $db->query("
            INSERT INTO attributes (name, slug, type, is_filterable, is_visible, sort_order, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [
            trim($_POST['name']),
            $slug,
            $_POST['type'],
            isset($_POST['is_filterable']) ? 1 : 0,
            isset($_POST['is_visible']) ? 1 : 0,
            $maxSort + 1,
        ]);

        $attributeId = $db->lastInsertId();

        // Add values if provided
        if (!empty($_POST['values'])) {
            $this->saveValues($attributeId, $_POST['values']);
        }

        if (isAjax()) {
            $this->json(['success' => true, 'id' => $attributeId, 'message' => 'Attribute created successfully.']);
            return;
        }

        flash('success', 'Attribute created successfully.');
        $this->redirect('/admin/attributes');
    }

    public function update(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        $attribute = $db->query("SELECT * FROM attributes WHERE id = ?", [$id])->fetch();

        if (!$attribute) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => 'Attribute not found.'], 404);
                return;
            }
            flash('error', 'Attribute not found.');
            $this->redirect('/admin/attributes');
            return;
        }

        $validation = $this->validate([
            'name' => 'required|min:2|max:255',
            'type' => 'required',
        ]);

        if (!$validation['valid']) {
            if (isAjax()) {
                $this->json(['success' => false, 'errors' => $validation['errors']], 422);
                return;
            }
            flash('error', 'Please correct the errors below.');
            $this->redirect('/admin/attributes');
            return;
        }

        // Update slug if name changed
        $slug = $attribute['slug'];
        if ($_POST['name'] !== $attribute['name']) {
            $slug = $this->generateSlug($_POST['name'], $id);
        }

        // Update attribute
        $db->query("
            UPDATE attributes SET
                name = ?, slug = ?, type = ?, is_filterable = ?, is_visible = ?,
                sort_order = ?, updated_at = NOW()
            WHERE id = ?
        ", [
            trim($_POST['name']),
            $slug,
            $_POST['type'],
            isset($_POST['is_filterable']) ? 1 : 0,
            isset($_POST['is_visible']) ? 1 : 0,
            (int) ($_POST['sort_order'] ?? $attribute['sort_order']),
            $id,
        ]);

        // Update values if provided
        if (isset($_POST['values'])) {
            $this->saveValues($id, $_POST['values']);
        }

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'Attribute updated successfully.']);
            return;
        }

        flash('success', 'Attribute updated successfully.');
        $this->redirect('/admin/attributes');
    }

    public function destroy(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        // Check if attribute is in use
        $inUse = (int) $db->query(
            "SELECT COUNT(*) FROM product_attributes WHERE attribute_id = ?",
            [$id]
        )->fetchColumn();

        if ($inUse > 0) {
            if (isAjax()) {
                $this->json(['success' => false, 'error' => "Cannot delete attribute. It's used by {$inUse} product(s)."], 400);
                return;
            }
            flash('error', "Cannot delete attribute. It's used by {$inUse} product(s).");
            $this->redirect('/admin/attributes');
            return;
        }

        // Delete attribute (values will cascade)
        $db->query("DELETE FROM attributes WHERE id = ?", [$id]);

        if (isAjax()) {
            $this->json(['success' => true, 'message' => 'Attribute deleted successfully.']);
            return;
        }

        flash('success', 'Attribute deleted successfully.');
        $this->redirect('/admin/attributes');
    }

    /**
     * API endpoint to add a value to an attribute
     */
    public function addValue(): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $attributeId = (int) ($_POST['attribute_id'] ?? 0);
        $value = trim($_POST['value'] ?? '');

        if (!$attributeId || !$value) {
            $this->json(['success' => false, 'error' => 'Attribute ID and value are required.'], 400);
            return;
        }

        $db = db();

        // Check attribute exists
        $attribute = $db->query("SELECT * FROM attributes WHERE id = ?", [$attributeId])->fetch();
        if (!$attribute) {
            $this->json(['success' => false, 'error' => 'Attribute not found.'], 404);
            return;
        }

        // Generate slug for value
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

        // Get max sort order
        $maxSort = (int) $db->query(
            "SELECT MAX(sort_order) FROM attribute_values WHERE attribute_id = ?",
            [$attributeId]
        )->fetchColumn();

        // Insert value
        $db->query("
            INSERT INTO attribute_values (attribute_id, value, slug, color_code, sort_order, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ", [
            $attributeId,
            $value,
            $slug,
            $_POST['color_code'] ?? null,
            $maxSort + 1,
        ]);

        $this->json([
            'success' => true,
            'id' => $db->lastInsertId(),
            'message' => 'Value added successfully.',
        ]);
    }

    /**
     * API endpoint to delete a value
     */
    public function deleteValue(int $id): void
    {
        if (!$this->validateCsrf()) {
            return;
        }

        $db = db();

        // Check if value is in use
        $inUse = (int) $db->query(
            "SELECT COUNT(*) FROM product_variant_attributes WHERE attribute_value_id = ?",
            [$id]
        )->fetchColumn();

        if ($inUse > 0) {
            $this->json(['success' => false, 'error' => "Cannot delete value. It's used by product variants."], 400);
            return;
        }

        $db->query("DELETE FROM attribute_values WHERE id = ?", [$id]);

        $this->json(['success' => true, 'message' => 'Value deleted successfully.']);
    }

    private function generateSlug(string $name, ?int $excludeId = null): string
    {
        $db = db();
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT id FROM attributes WHERE slug = ?";
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

    private function saveValues(int $attributeId, array $values): void
    {
        $db = db();

        // Get existing values
        $existingIds = $db->query(
            "SELECT id FROM attribute_values WHERE attribute_id = ?",
            [$attributeId]
        )->fetchAll(\PDO::FETCH_COLUMN);

        $submittedIds = [];

        foreach ($values as $index => $valueData) {
            if (is_string($valueData)) {
                $valueData = ['value' => $valueData];
            }

            $value = trim($valueData['value'] ?? '');
            if (empty($value)) {
                continue;
            }

            $id = $valueData['id'] ?? null;
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));
            $colorCode = $valueData['color_code'] ?? null;

            if ($id && in_array($id, $existingIds)) {
                // Update existing
                $db->query("
                    UPDATE attribute_values SET value = ?, slug = ?, color_code = ?, sort_order = ?
                    WHERE id = ? AND attribute_id = ?
                ", [$value, $slug, $colorCode, $index, $id, $attributeId]);
                $submittedIds[] = $id;
            } else {
                // Insert new
                $db->query("
                    INSERT INTO attribute_values (attribute_id, value, slug, color_code, sort_order, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ", [$attributeId, $value, $slug, $colorCode, $index]);
                $submittedIds[] = $db->lastInsertId();
            }
        }

        // Delete removed values (only if not in use)
        $toDelete = array_diff($existingIds, $submittedIds);
        foreach ($toDelete as $deleteId) {
            $inUse = (int) $db->query(
                "SELECT COUNT(*) FROM product_variant_attributes WHERE attribute_value_id = ?",
                [$deleteId]
            )->fetchColumn();

            if ($inUse === 0) {
                $db->query("DELETE FROM attribute_values WHERE id = ?", [$deleteId]);
            }
        }
    }
}
