<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote #<?= e($quote->quote_number) ?> | <?= e(config('app.name')) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            background: #f5f5f5;
        }

        .quote {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .quote-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 2rem;
            border-bottom: 2px solid #eee;
        }

        .quote-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e91e63;
        }

        .quote-title {
            text-align: right;
        }

        .quote-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .quote-number {
            font-size: 1rem;
            color: #666;
        }

        .quote-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .quote-status.draft {
            background: #f3f4f6;
            color: #6b7280;
        }

        .quote-status.submitted {
            background: #dbeafe;
            color: #1e40af;
        }

        .quote-status.accepted {
            background: #d4edda;
            color: #155724;
        }

        .quote-status.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .quote-meta {
            display: flex;
            justify-content: space-between;
            padding: 2rem;
            background: #f9f9f9;
        }

        .quote-meta-group {
            flex: 1;
        }

        .quote-meta-group h3 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #999;
            margin-bottom: 0.5rem;
        }

        .quote-meta-group p {
            margin: 0;
            line-height: 1.6;
        }

        .quote-body {
            padding: 2rem;
        }

        .quote-name {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .quote-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .quote-table th {
            text-align: left;
            padding: 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #999;
            border-bottom: 2px solid #eee;
        }

        .quote-table th:last-child,
        .quote-table td:last-child {
            text-align: right;
        }

        .quote-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .quote-table .item-name {
            font-weight: 600;
        }

        .quote-table .item-sku {
            font-size: 0.875rem;
            color: #999;
        }

        .quote-totals {
            display: flex;
            justify-content: flex-end;
        }

        .quote-totals-table {
            width: 300px;
        }

        .quote-totals-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }

        .quote-totals-row.total {
            border-top: 2px solid #333;
            margin-top: 0.5rem;
            padding-top: 1rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .quote-notes {
            margin-top: 2rem;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .quote-notes h3 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #999;
            margin-bottom: 0.5rem;
        }

        .quote-notes p {
            margin: 0;
            white-space: pre-wrap;
        }

        .quote-validity {
            margin-top: 1.5rem;
            padding: 1rem;
            border: 1px dashed #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 0.875rem;
            color: #666;
        }

        .quote-footer {
            padding: 2rem;
            background: #f9f9f9;
            text-align: center;
            font-size: 0.875rem;
            color: #666;
        }

        .quote-footer p {
            margin: 0.25rem 0;
        }

        @media print {
            body {
                background: white;
            }

            .quote {
                box-shadow: none;
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="quote">
        <div class="quote-header">
            <div class="quote-logo">
                <?= e(config('app.name', 'Pricetag')) ?>
            </div>
            <div class="quote-title">
                <h1>QUOTE</h1>
                <p class="quote-number">#<?= e($quote->quote_number) ?></p>
                <span class="quote-status <?= e($quote->status) ?>">
                    <?= strtoupper($quote->status) ?>
                </span>
            </div>
        </div>

        <div class="quote-meta">
            <div class="quote-meta-group">
                <h3>Quote Date</h3>
                <p><?= date('d F Y', strtotime($quote->created_at)) ?></p>
                <?php if ($quote->valid_until): ?>
                <h3 style="margin-top: 1rem;">Valid Until</h3>
                <p><?= date('d F Y', strtotime($quote->valid_until)) ?></p>
                <?php endif; ?>
            </div>
            <div class="quote-meta-group">
                <h3>Prepared For</h3>
                <p>
                    <?php if ($quote->contact_name): ?>
                    <strong><?= e($quote->contact_name) ?></strong><br>
                    <?php endif; ?>
                    <?php if ($quote->company_name): ?>
                    <?= e($quote->company_name) ?><br>
                    <?php endif; ?>
                    <?php if ($quote->contact_email): ?>
                    <?= e($quote->contact_email) ?><br>
                    <?php endif; ?>
                    <?php if ($quote->contact_phone): ?>
                    <?= e($quote->contact_phone) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="quote-meta-group">
                <h3>Prepared By</h3>
                <p>
                    <strong><?= e(config('app.name', 'Pricetag')) ?></strong><br>
                    <?= e(config('company.address', '123 Commerce Street, Johannesburg')) ?><br>
                    <?= e(config('company.email', 'support@pricetag.co.za')) ?><br>
                    <?= e(config('company.phone', '+27 11 123 4567')) ?>
                </p>
            </div>
        </div>

        <div class="quote-body">
            <?php if ($quote->title): ?>
            <div class="quote-name"><?= e($quote->title) ?></div>
            <?php endif; ?>

            <table class="quote-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">#</th>
                        <th style="width: 40%;">Item</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <div class="item-name"><?= e($item['name']) ?></div>
                            <?php if (!empty($item['sku'])): ?>
                            <div class="item-sku">SKU: <?= e($item['sku']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['variant_name'])): ?>
                            <div class="item-sku"><?= e($item['variant_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= formatPrice((float)$item['price']) ?></td>
                        <td><?= formatPrice((float)$item['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="quote-totals">
                <div class="quote-totals-table">
                    <div class="quote-totals-row">
                        <span>Subtotal</span>
                        <span><?= formatPrice((float)$quote->subtotal) ?></span>
                    </div>
                    <?php if ((float)$quote->tax_amount > 0): ?>
                    <div class="quote-totals-row">
                        <span>VAT (incl.)</span>
                        <span><?= formatPrice((float)$quote->tax_amount) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="quote-totals-row total">
                        <span>Total</span>
                        <span><?= formatPrice((float)$quote->total) ?></span>
                    </div>
                </div>
            </div>

            <?php if ($quote->customer_notes): ?>
            <div class="quote-notes">
                <h3>Notes</h3>
                <p><?= e($quote->customer_notes) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($quote->valid_until): ?>
            <div class="quote-validity">
                This quote is valid until <strong><?= date('d F Y', strtotime($quote->valid_until)) ?></strong>.
                Prices are subject to change after this date.
            </div>
            <?php endif; ?>
        </div>

        <div class="quote-footer">
            <p><strong><?= e(config('app.name', 'Pricetag')) ?></strong></p>
            <p><?= e(config('company.address', '123 Commerce Street, Johannesburg')) ?></p>
            <p>Email: <?= e(config('company.email', 'support@pricetag.co.za')) ?> | Tel: <?= e(config('company.phone', '+27 11 123 4567')) ?></p>
            <?php if (config('company.vat_number')): ?>
            <p>VAT Number: <?= e(config('company.vat_number')) ?></p>
            <?php endif; ?>
            <p style="margin-top: 1rem; color: #999;">Thank you for your interest!</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin: 2rem;">
        <button onclick="window.print()" style="padding: 0.75rem 2rem; background: #e91e63; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer;">
            Print Quote
        </button>
    </div>
</body>
</html>
