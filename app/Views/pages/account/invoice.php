<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= e($order->order_number) ?> | <?= e(config('app.name')) ?></title>
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

        .invoice {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 2rem;
            border-bottom: 2px solid #eee;
        }

        .invoice-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e91e63;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .invoice-number {
            font-size: 1rem;
            color: #666;
        }

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            padding: 2rem;
            background: #f9f9f9;
        }

        .invoice-meta-group {
            flex: 1;
        }

        .invoice-meta-group h3 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #999;
            margin-bottom: 0.5rem;
        }

        .invoice-meta-group p {
            margin: 0;
            line-height: 1.6;
        }

        .invoice-body {
            padding: 2rem;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .invoice-table th {
            text-align: left;
            padding: 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #999;
            border-bottom: 2px solid #eee;
        }

        .invoice-table th:last-child,
        .invoice-table td:last-child {
            text-align: right;
        }

        .invoice-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .invoice-table .item-name {
            font-weight: 600;
        }

        .invoice-table .item-sku {
            font-size: 0.875rem;
            color: #999;
        }

        .invoice-totals {
            display: flex;
            justify-content: flex-end;
        }

        .invoice-totals-table {
            width: 300px;
        }

        .invoice-totals-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }

        .invoice-totals-row.total {
            border-top: 2px solid #333;
            margin-top: 0.5rem;
            padding-top: 1rem;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .invoice-footer {
            padding: 2rem;
            background: #f9f9f9;
            text-align: center;
            font-size: 0.875rem;
            color: #666;
        }

        .invoice-footer p {
            margin: 0.25rem 0;
        }

        .invoice-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .invoice-status.paid {
            background: #d4edda;
            color: #155724;
        }

        .invoice-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        @media print {
            body {
                background: white;
            }

            .invoice {
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
    <div class="invoice">
        <div class="invoice-header">
            <div class="invoice-logo">
                <?= e(config('app.name', 'Pricetag')) ?>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p class="invoice-number">#<?= e($order->order_number) ?></p>
                <span class="invoice-status <?= $order->payment_status === 'completed' ? 'paid' : 'pending' ?>">
                    <?= $order->payment_status === 'completed' ? 'PAID' : 'PENDING' ?>
                </span>
            </div>
        </div>

        <div class="invoice-meta">
            <div class="invoice-meta-group">
                <h3>Invoice Date</h3>
                <p><?= date('d F Y', strtotime($order->created_at)) ?></p>
            </div>
            <div class="invoice-meta-group">
                <h3>Bill To</h3>
                <p>
                    <strong><?= e($order->billing_first_name . ' ' . $order->billing_last_name) ?></strong><br>
                    <?= e($order->billing_address_1) ?><br>
                    <?php if ($order->billing_address_2): ?>
                    <?= e($order->billing_address_2) ?><br>
                    <?php endif; ?>
                    <?= e($order->billing_city) ?>, <?= e($order->billing_province) ?> <?= e($order->billing_postal_code) ?><br>
                    <?= e($order->email) ?>
                </p>
            </div>
            <div class="invoice-meta-group">
                <h3>Ship To</h3>
                <p>
                    <strong><?= e($order->shipping_first_name . ' ' . $order->shipping_last_name) ?></strong><br>
                    <?= e($order->shipping_address_1) ?><br>
                    <?php if ($order->shipping_address_2): ?>
                    <?= e($order->shipping_address_2) ?><br>
                    <?php endif; ?>
                    <?= e($order->shipping_city) ?>, <?= e($order->shipping_province) ?> <?= e($order->shipping_postal_code) ?>
                </p>
            </div>
        </div>

        <div class="invoice-body">
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 50%">Item</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="item-name"><?= e($item['name']) ?></div>
                            <?php if (!empty($item['sku'])): ?>
                            <div class="item-sku">SKU: <?= e($item['sku']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['variant'])): ?>
                            <div class="item-sku"><?= e($item['variant']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= formatPrice($item['price']) ?></td>
                        <td><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="invoice-totals">
                <div class="invoice-totals-table">
                    <div class="invoice-totals-row">
                        <span>Subtotal</span>
                        <span><?= formatPrice($order->subtotal) ?></span>
                    </div>
                    <?php if ($order->discount > 0): ?>
                    <div class="invoice-totals-row">
                        <span>Discount</span>
                        <span>-<?= formatPrice($order->discount) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="invoice-totals-row">
                        <span>Shipping</span>
                        <span><?= $order->shipping > 0 ? formatPrice($order->shipping) : 'Free' ?></span>
                    </div>
                    <?php if ($order->tax > 0): ?>
                    <div class="invoice-totals-row">
                        <span>VAT (15%)</span>
                        <span><?= formatPrice($order->tax) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="invoice-totals-row total">
                        <span>Total</span>
                        <span><?= formatPrice($order->total) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p><strong><?= e(config('app.name', 'Pricetag')) ?></strong></p>
            <p><?= e(config('company.address', '123 Commerce Street, Johannesburg')) ?></p>
            <p>Email: <?= e(config('company.email', 'support@pricetag.co.za')) ?> | Tel: <?= e(config('company.phone', '+27 11 123 4567')) ?></p>
            <?php if (config('company.vat_number')): ?>
            <p>VAT Number: <?= e(config('company.vat_number')) ?></p>
            <?php endif; ?>
            <p style="margin-top: 1rem; color: #999;">Thank you for your business!</p>
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin: 2rem;">
        <button onclick="window.print()" style="padding: 0.75rem 2rem; background: #e91e63; color: white; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer;">
            Print Invoice
        </button>
    </div>
</body>
</html>
