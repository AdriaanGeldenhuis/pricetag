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
        'high' => '#10b981',
        'low' => '#f59e0b',
        default => '#9ca3af',
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
<style>
.import-detail-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 16px; }
.import-detail-stat { padding: 16px; border-radius: 8px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); }
.import-detail-stat .v { font-size: 24px; font-weight: 700; }
.import-detail-stat .l { font-size: 12px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.04em; }
.import-detail-table { width: 100%; border-collapse: collapse; }
.import-detail-table th, .import-detail-table td { padding: 8px 12px; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.85rem; }
.import-detail-table th { text-align: left; font-weight: 600; opacity: 0.75; font-size: 0.75rem; text-transform: uppercase; }
.confidence-pill { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; background: rgba(255,255,255,0.05); }
</style>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;font-size:24px;font-weight:700;">Import #<?= (int) $log['id'] ?></h1>
        <p style="margin:0.25rem 0 0;opacity:0.7;font-size:0.875rem;">
            <?= e($log['filename'] ?? 'Import') ?> &middot; <?= e($log['created_at']) ?>
        </p>
    </div>
    <div>
        <?php if (!empty($errors)): ?>
            <a href="<?= url('/admin/products/import/history/' . (int) $log['id'] . '/failed.csv') ?>" class="btn btn-secondary">
                Download failed rows CSV
            </a>
        <?php endif; ?>
        <a href="<?= url('/admin/products/import') ?>" class="btn btn-outline">&larr; Back to Import</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="import-detail-stats">
            <div class="import-detail-stat"><div class="v"><?= $summaryTotal ?></div><div class="l">Total Rows</div></div>
            <div class="import-detail-stat" style="background:rgba(34,197,94,0.08);"><div class="v"><?= $created ?></div><div class="l">Created</div></div>
            <div class="import-detail-stat" style="background:rgba(59,130,246,0.08);"><div class="v"><?= $updated ?></div><div class="l">Updated</div></div>
            <div class="import-detail-stat" style="background:rgba(239,68,68,0.08);"><div class="v"><?= $failed ?></div><div class="l">Failed</div></div>
        </div>

        <?php if (!empty($aiRows)): ?>
            <div style="margin-top:1rem;padding:0.75rem 1rem;background:rgba(255,255,255,0.03);border-radius:6px;font-size:0.85rem;opacity:0.85;">
                AI usage: <strong><?= number_format($totalInputTokens) ?></strong> input +
                <strong><?= number_format($totalOutputTokens) ?></strong> output tokens &middot;
                estimated cost: <strong>$<?= number_format($totalCost, 4) ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="card" style="margin-top:1rem;">
    <div class="card-header"><h3 class="card-title">Errors (<?= count($errors) ?>)</h3></div>
    <div class="card-body">
        <ul style="margin:0;padding-left:1.25rem;font-size:0.85rem;">
            <?php foreach (array_slice($errors, 0, 50) as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php if (count($errors) > 50): ?>
            <p style="margin-top:0.5rem;opacity:0.7;font-size:0.8rem;">
                Showing first 50 of <?= count($errors) ?> errors. Download the CSV for the full list.
            </p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($aiRows)): ?>
<div class="card" style="margin-top:1rem;">
    <div class="card-header"><h3 class="card-title">Per-row AI audit (<?= count($aiRows) ?>)</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="import-detail-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Product</th>
                    <th>Confidence</th>
                    <th>Method</th>
                    <th>Image</th>
                    <th style="text-align:right;">Tokens in/out</th>
                    <th style="text-align:right;">Cost</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aiRows as $r): ?>
                    <tr>
                        <td><code style="font-size:0.8rem;"><?= e($r['sku']) ?></code></td>
                        <td>
                            <?php if (!empty($r['product_id'])): ?>
                                <a href="<?= url('/admin/products/' . (int) $r['product_id'] . '/edit') ?>" style="color:inherit;">
                                    <?= e($r['product_name'] ?? '(unnamed)') ?>
                                </a>
                                <?php if (!empty($r['product_status'])): ?>
                                    <span style="font-size:0.7rem;opacity:0.6;">(<?= e($r['product_status']) ?>)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <em style="opacity:0.6;">not created</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="confidence-pill" style="color:<?= $confidenceColor($r['confidence']) ?>;">
                                <?= e($r['confidence']) ?>
                            </span>
                        </td>
                        <td><?= e($r['ai_method']) ?></td>
                        <td>
                            <?php if (!empty($r['image_url'])): ?>
                                <a href="<?= e($r['image_url']) ?>" target="_blank" rel="noopener" style="font-size:0.75rem;">view</a>
                            <?php else: ?>
                                <span style="opacity:0.5;">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;font-size:0.78rem;opacity:0.8;">
                            <?= number_format((int) $r['input_tokens']) ?> / <?= number_format((int) $r['output_tokens']) ?>
                        </td>
                        <td style="text-align:right;font-size:0.78rem;">
                            $<?= number_format((float) $r['estimated_cost_usd'], 4) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card" style="margin-top:1rem;">
    <div class="card-body">
        <p style="margin:0;opacity:0.7;">
            No per-row AI audit data for this import. (Either this was not an AI import, or migration 018 was not yet applied.)
        </p>
    </div>
</div>
<?php endif; ?>
