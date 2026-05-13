<?php
/** @var array $log */
/** @var array $aiRows */

$summaryTotal = (int) ($log['total_products'] ?? 0);
$created = (int) ($log['created_products'] ?? 0);
$updated = (int) ($log['updated_products'] ?? 0);
$failed = (int) ($log['failed_products'] ?? 0);
$errors = $log['errors'] ?? [];

$confidenceColor = static function (string $c): string {
    return match ($c) {
        'high' => 'var(--admin-success, #059669)',
        'low' => 'var(--admin-warning, #f59e0b)',
        default => 'var(--admin-text-muted, #6b7280)',
    };
};

$totalCost = 0.0;
$totalInputTokens = 0;
$totalOutputTokens = 0;
foreach ($aiRows as $r) {
    $totalCost += (float) ($r['estimated_cost_usd'] ?? 0);
    $totalInputTokens += (int) ($r['input_tokens'] ?? 0);
    $totalOutputTokens += (int) ($r['output_tokens'] ?? 0);
}
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">
            <?= e($log['filename'] ?? 'Import') ?> &middot;
            <?= e($log['created_at']) ?>
        </p>
    </div>
    <div class="admin-page-actions">
        <?php if (!empty($errors)): ?>
            <a href="<?= url('/admin/products/import/history/' . $log['id'] . '/failed.csv') ?>" class="admin-btn admin-btn-secondary">
                Download failed rows CSV
            </a>
        <?php endif; ?>
        <a href="<?= url('/admin/products/import') ?>" class="admin-btn admin-btn-secondary">
            &larr; Back to Import
        </a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-body">
        <div class="preview-stats" style="display:grid;">
            <div class="stat-item stat-total">
                <span class="stat-value"><?= $summaryTotal ?></span>
                <span class="stat-label">Total Rows</span>
            </div>
            <div class="stat-item stat-new">
                <span class="stat-value"><?= $created ?></span>
                <span class="stat-label">Created</span>
            </div>
            <div class="stat-item stat-update">
                <span class="stat-value"><?= $updated ?></span>
                <span class="stat-label">Updated</span>
            </div>
            <div class="stat-item stat-error">
                <span class="stat-value"><?= $failed ?></span>
                <span class="stat-label">Failed</span>
            </div>
        </div>

        <?php if (!empty($aiRows)): ?>
            <div style="margin-top:1.5rem;padding:1rem;background:var(--admin-bg);border-radius:var(--admin-radius);font-size:0.85rem;color:var(--admin-text-muted);">
                AI usage: <strong><?= number_format($totalInputTokens) ?></strong> input +
                <strong><?= number_format($totalOutputTokens) ?></strong> output tokens &middot;
                estimated cost: <strong>$<?= number_format($totalCost, 4) ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="admin-card" style="margin-top:1rem;">
    <div class="admin-card-header"><h3 class="admin-card-title">Errors (<?= count($errors) ?>)</h3></div>
    <div class="admin-card-body">
        <ul style="margin:0;padding-left:1.25rem;font-size:0.85rem;color:var(--admin-text);">
            <?php foreach (array_slice($errors, 0, 50) as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php if (count($errors) > 50): ?>
            <p style="margin-top:0.5rem;color:var(--admin-text-muted);font-size:0.8rem;">
                Showing first 50 of <?= count($errors) ?> errors. Download the CSV for the full list.
            </p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($aiRows)): ?>
<div class="admin-card" style="margin-top:1rem;">
    <div class="admin-card-header"><h3 class="admin-card-title">Per-row AI audit (<?= count($aiRows) ?>)</h3></div>
    <div class="admin-card-body">
        <table class="admin-table preview-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Confidence</th>
                    <th>Method</th>
                    <th>Image</th>
                    <th>Tokens (in/out)</th>
                    <th>Cost (USD)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aiRows as $r): ?>
                    <tr>
                        <td><code><?= e($r['sku']) ?></code></td>
                        <td>
                            <?php if (!empty($r['product_id'])): ?>
                                <a href="<?= url('/admin/products/' . (int) $r['product_id'] . '/edit') ?>">
                                    <?= e($r['product_name'] ?? '(unnamed)') ?>
                                </a>
                                <?php if (!empty($r['product_status'])): ?>
                                    <span style="font-size:0.7rem;color:var(--admin-text-muted);">(<?= e($r['product_status']) ?>)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <em style="color:var(--admin-text-muted);">not created</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="display:inline-block;padding:0.1rem 0.5rem;border-radius:4px;background:rgba(0,0,0,0.05);color:<?= $confidenceColor($r['confidence']) ?>;font-size:0.75rem;font-weight:600;">
                                <?= e($r['confidence']) ?>
                            </span>
                        </td>
                        <td><?= e($r['ai_method']) ?></td>
                        <td>
                            <?php if (!empty($r['image_url'])): ?>
                                <a href="<?= e($r['image_url']) ?>" target="_blank" rel="noopener" style="font-size:0.75rem;">view</a>
                            <?php else: ?>
                                <span style="color:var(--admin-text-muted);">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.75rem;color:var(--admin-text-muted);">
                            <?= number_format((int) $r['input_tokens']) ?> / <?= number_format((int) $r['output_tokens']) ?>
                        </td>
                        <td style="font-size:0.75rem;">
                            $<?= number_format((float) $r['estimated_cost_usd'], 4) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="admin-card" style="margin-top:1rem;">
    <div class="admin-card-body">
        <p style="margin:0;color:var(--admin-text-muted);">
            No per-row AI audit data for this import. (Either this was not an AI import, or migration 018 was not yet applied at the time.)
        </p>
    </div>
</div>
<?php endif; ?>
