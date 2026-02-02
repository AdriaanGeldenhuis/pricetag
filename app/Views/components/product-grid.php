<?php
/**
 * Product Grid Component
 * Used for AJAX loading of products in search results
 */
?>
<?php foreach ($products as $product): ?>
<?php include APP_PATH . '/Views/components/product-card.php'; ?>
<?php endforeach; ?>
