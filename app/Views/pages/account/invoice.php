<?php
/**
 * Advanced Invoice Template
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Supports: Tax Invoice, Proforma Invoice, Credit Note, Quote
 * Options: Print, Download PDF, Email, Toggle sections
 */

$invoiceType = $invoiceType ?? 'tax_invoice';
$taxRate = $taxRate ?? 15;
$taxInclusive = $taxInclusive ?? true;
$company = $company ?? [];
$payment = $payment ?? null;

$typeLabels = [
    'tax_invoice'  => 'TAX INVOICE',
    'proforma'     => 'PROFORMA INVOICE',
    'credit_note'  => 'CREDIT NOTE',
    'quote'        => 'QUOTATION',
];

$typeLabel = $typeLabels[$invoiceType] ?? 'TAX INVOICE';

// Calculate per-line-item tax for detailed breakdown
$lineItems = [];
$totalExclVat = 0;
$totalVat = 0;

foreach ($items as $item) {
    $lineTotal = $item['price'] * $item['quantity'];

    if ($taxInclusive) {
        $exclVat = $lineTotal / (1 + ($taxRate / 100));
        $vatAmount = $lineTotal - $exclVat;
    } else {
        $exclVat = $lineTotal;
        $vatAmount = $lineTotal * ($taxRate / 100);
    }

    $totalExclVat += $exclVat;
    $totalVat += $vatAmount;

    $lineItems[] = array_merge($item, [
        'line_total' => $lineTotal,
        'excl_vat' => $exclVat,
        'vat_amount' => $vatAmount,
    ]);
}

// Due date (30 days from order for pending, or paid date)
$dueDate = date('d F Y', strtotime($order->created_at . ' +30 days'));
$isPaid = in_array($order->payment_status, ['paid', 'completed'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($typeLabel) ?> #<?= e($order->invoice_number ?? $order->order_number) ?> | <?= e($company['name'] ?? config('app.name')) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #333;
            background: #f0f2f5;
        }

        /* ============================================================
           TOOLBAR (no-print)
           ============================================================ */
        .invoice-toolbar {
            max-width: 880px;
            margin: 1.5rem auto 0;
            padding: 1rem 1.5rem;
            background: #fff;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .toolbar-title {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }

        .toolbar-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .toolbar-badge.paid { background: #d4edda; color: #155724; }
        .toolbar-badge.pending { background: #fff3cd; color: #856404; }
        .toolbar-badge.refunded { background: #f8d7da; color: #721c24; }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
            color: #555;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-toolbar:hover {
            background: #f8f9fa;
            border-color: #bbb;
            color: #333;
        }

        .btn-toolbar.primary {
            background: #e91e63;
            color: #fff;
            border-color: #e91e63;
        }

        .btn-toolbar.primary:hover {
            background: #c2185b;
            border-color: #c2185b;
        }

        .btn-toolbar svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Invoice Type Selector */
        .type-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .type-selector label {
            font-size: 0.8rem;
            font-weight: 500;
            color: #666;
        }

        .type-selector select {
            padding: 0.4rem 0.6rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.8rem;
            background: #fff;
            color: #333;
            cursor: pointer;
        }

        /* Toggle Options */
        .invoice-options {
            max-width: 880px;
            margin: 0 auto;
            padding: 0.75rem 1.5rem;
            background: #f8f9fa;
            border-left: 1px solid #e9ecef;
            border-right: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
            font-size: 0.8rem;
        }

        .invoice-options label {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #555;
            cursor: pointer;
            user-select: none;
        }

        .invoice-options input[type="checkbox"] {
            accent-color: #e91e63;
        }

        .options-label {
            font-weight: 600;
            color: #888;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* ============================================================
           INVOICE DOCUMENT
           ============================================================ */
        .invoice {
            max-width: 880px;
            margin: 0 auto 2rem;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 2rem 2.5rem;
            border-bottom: 3px solid #e91e63;
        }

        .invoice-brand {
            flex: 1;
        }

        .invoice-logo {
            font-size: 1.6rem;
            font-weight: 800;
            color: #e91e63;
            letter-spacing: -0.02em;
        }

        .invoice-company-details {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #777;
            line-height: 1.7;
        }

        .invoice-title-block {
            text-align: right;
        }

        .invoice-type-label {
            font-size: 1.6rem;
            font-weight: 800;
            color: #333;
            letter-spacing: 0.04em;
        }

        .invoice-ref {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            color: #555;
        }

        .invoice-ref strong {
            display: inline-block;
            min-width: 110px;
            text-align: right;
            color: #888;
            font-weight: 500;
        }

        .invoice-status {
            display: inline-block;
            margin-top: 0.75rem;
            padding: 0.3rem 1rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .invoice-status.paid { background: #d4edda; color: #155724; }
        .invoice-status.pending { background: #fff3cd; color: #856404; }
        .invoice-status.refunded { background: #f8d7da; color: #721c24; }

        /* Meta / Address Row */
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            padding: 1.75rem 2.5rem;
            background: #fafbfc;
            border-bottom: 1px solid #eee;
        }

        .invoice-meta-group h3 {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #999;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .invoice-meta-group p {
            margin: 0;
            line-height: 1.7;
            font-size: 0.85rem;
        }

        .invoice-meta-group p strong {
            font-weight: 600;
            color: #333;
        }

        /* Invoice Details Row */
        .invoice-details-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1.25rem;
            padding: 1.25rem 2.5rem;
            background: #fff;
            border-bottom: 1px solid #eee;
        }

        .invoice-detail-item {
            text-align: center;
        }

        .invoice-detail-item .detail-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #999;
            font-weight: 600;
        }

        .invoice-detail-item .detail-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin-top: 0.2rem;
        }

        /* Items Table */
        .invoice-body {
            padding: 1.5rem 2.5rem 2rem;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .invoice-table thead th {
            text-align: left;
            padding: 0.75rem 0.5rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #999;
            border-bottom: 2px solid #e0e0e0;
            font-weight: 600;
            white-space: nowrap;
        }

        .invoice-table thead th.text-right,
        .invoice-table td.text-right {
            text-align: right;
        }

        .invoice-table thead th.text-center,
        .invoice-table td.text-center {
            text-align: center;
        }

        .invoice-table tbody td {
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
            font-size: 0.85rem;
        }

        .invoice-table tbody tr:last-child td {
            border-bottom: 2px solid #e0e0e0;
        }

        .item-name {
            font-weight: 600;
            color: #333;
        }

        .item-sku {
            font-size: 0.75rem;
            color: #999;
            margin-top: 0.15rem;
        }

        .item-variant {
            font-size: 0.75rem;
            color: #777;
            margin-top: 0.1rem;
        }

        /* Totals */
        .invoice-totals-wrapper {
            display: flex;
            justify-content: flex-end;
        }

        .invoice-totals {
            width: 340px;
        }

        .invoice-totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.45rem 0;
            font-size: 0.85rem;
        }

        .invoice-totals-row .label {
            color: #666;
        }

        .invoice-totals-row .value {
            font-weight: 500;
            color: #333;
        }

        .invoice-totals-row.discount .value {
            color: #e91e63;
        }

        .invoice-totals-row.grand-total {
            border-top: 2px solid #333;
            margin-top: 0.5rem;
            padding-top: 0.75rem;
        }

        .invoice-totals-row.grand-total .label {
            font-size: 1rem;
            font-weight: 700;
            color: #333;
        }

        .invoice-totals-row.grand-total .value {
            font-size: 1.15rem;
            font-weight: 800;
            color: #333;
        }

        .invoice-totals-row.amount-due {
            background: #fafbfc;
            margin: 0.5rem -0.75rem 0;
            padding: 0.75rem;
            border-radius: 6px;
        }

        .invoice-totals-row.amount-due .label {
            font-weight: 700;
            color: #e91e63;
        }

        .invoice-totals-row.amount-due .value {
            font-weight: 800;
            color: #e91e63;
            font-size: 1.1rem;
        }

        .invoice-totals-row.paid-badge .value {
            color: #155724;
            font-weight: 700;
        }

        /* Payment & Banking Section */
        .invoice-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 1.5rem 2.5rem;
            border-top: 1px solid #eee;
        }

        .invoice-section h3 {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #999;
            margin-bottom: 0.75rem;
            font-weight: 600;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .invoice-section p {
            font-size: 0.85rem;
            margin: 0.3rem 0;
            line-height: 1.6;
        }

        .invoice-section .info-row {
            display: flex;
            gap: 0.5rem;
        }

        .invoice-section .info-label {
            color: #888;
            min-width: 100px;
            font-weight: 500;
        }

        .invoice-section .info-value {
            color: #333;
            font-weight: 500;
        }

        /* Notes */
        .invoice-notes {
            padding: 1.25rem 2.5rem;
            border-top: 1px solid #eee;
        }

        .invoice-notes h3 {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #999;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .invoice-notes p {
            font-size: 0.85rem;
            color: #555;
            line-height: 1.6;
        }

        /* Terms */
        .invoice-terms {
            padding: 1.25rem 2.5rem;
            border-top: 1px solid #eee;
        }

        .invoice-terms h3 {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #999;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .invoice-terms ol {
            font-size: 0.8rem;
            color: #777;
            line-height: 1.8;
            padding-left: 1.25rem;
        }

        /* Footer */
        .invoice-footer {
            padding: 1.5rem 2.5rem;
            background: #fafbfc;
            border-top: 2px solid #e91e63;
            text-align: center;
            font-size: 0.8rem;
            color: #888;
        }

        .invoice-footer p {
            margin: 0.2rem 0;
        }

        .invoice-footer .footer-brand {
            font-weight: 700;
            color: #333;
            font-size: 0.85rem;
        }

        .invoice-footer .footer-legal {
            margin-top: 0.5rem;
            font-size: 0.7rem;
            color: #aaa;
        }

        .invoice-footer .footer-thankyou {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            color: #e91e63;
            font-weight: 500;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 720px) {
            .invoice-header {
                flex-direction: column;
                gap: 1.5rem;
            }

            .invoice-title-block {
                text-align: left;
            }

            .invoice-ref strong {
                text-align: left;
            }

            .invoice-meta {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .invoice-details-row {
                grid-template-columns: 1fr 1fr;
            }

            .invoice-table {
                font-size: 0.75rem;
            }

            .invoice-totals {
                width: 100%;
            }

            .invoice-sections {
                grid-template-columns: 1fr;
            }

            .toolbar-left,
            .toolbar-right {
                width: 100%;
                justify-content: center;
            }

            .invoice-options {
                justify-content: center;
            }
        }

        /* ============================================================
           PRINT STYLES
           ============================================================ */
        @media print {
            body {
                background: white;
                font-size: 11px;
            }

            .no-print,
            .invoice-toolbar,
            .invoice-options {
                display: none !important;
            }

            .invoice {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }

            .invoice-header {
                border-bottom-color: #e91e63;
            }

            .invoice-footer {
                border-top-color: #e91e63;
            }

            .invoice-table thead th {
                border-bottom-color: #ccc;
            }

            .invoice-table tbody tr:last-child td {
                border-bottom-color: #ccc;
            }
        }

        /* ============================================================
           HIDDEN SECTIONS (toggled via JS)
           ============================================================ */
        .section-hidden {
            display: none !important;
        }
    </style>
</head>
<body>

    <!-- ================================================================
         TOOLBAR (no-print)
         ================================================================ -->
    <div class="invoice-toolbar no-print">
        <div class="toolbar-left">
            <a href="<?= url('/account/orders/' . $order->id) ?>" class="btn-toolbar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Back to Order
            </a>
            <span class="toolbar-title"><?= e($typeLabel) ?> #<?= e($order->invoice_number ?? $order->order_number) ?></span>
            <span class="toolbar-badge <?= $isPaid ? 'paid' : ($order->payment_status === 'refunded' ? 'refunded' : 'pending') ?>">
                <?= $isPaid ? 'Paid' : ucfirst($order->payment_status ?? 'Pending') ?>
            </span>
        </div>
        <div class="toolbar-right">
            <div class="type-selector">
                <label for="invoice-type">Type:</label>
                <select id="invoice-type" onchange="changeInvoiceType(this.value)">
                    <option value="tax_invoice" <?= $invoiceType === 'tax_invoice' ? 'selected' : '' ?>>Tax Invoice</option>
                    <option value="proforma" <?= $invoiceType === 'proforma' ? 'selected' : '' ?>>Proforma Invoice</option>
                    <option value="credit_note" <?= $invoiceType === 'credit_note' ? 'selected' : '' ?>>Credit Note</option>
                    <option value="quote" <?= $invoiceType === 'quote' ? 'selected' : '' ?>>Quotation</option>
                </select>
            </div>
            <button class="btn-toolbar" onclick="window.print()" title="Print Invoice">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print
            </button>
            <button class="btn-toolbar" onclick="downloadPDF()" title="Download as PDF">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download PDF
            </button>
        </div>
    </div>

    <!-- ================================================================
         DISPLAY OPTIONS (no-print)
         ================================================================ -->
    <div class="invoice-options no-print">
        <span class="options-label">Show:</span>
        <label><input type="checkbox" checked onchange="toggleSection('shipping-address', this.checked)"> Shipping Address</label>
        <label><input type="checkbox" checked onchange="toggleSection('tax-breakdown', this.checked)"> Tax Breakdown</label>
        <label><input type="checkbox" checked onchange="toggleSection('payment-info', this.checked)"> Payment Info</label>
        <?php if ($company['bank_name']): ?>
        <label><input type="checkbox" checked onchange="toggleSection('banking-details', this.checked)"> Banking Details</label>
        <?php endif; ?>
        <label><input type="checkbox" checked onchange="toggleSection('notes-section', this.checked)"> Notes</label>
        <label><input type="checkbox" checked onchange="toggleSection('terms-section', this.checked)"> Terms</label>
    </div>

    <!-- ================================================================
         INVOICE DOCUMENT
         ================================================================ -->
    <div class="invoice" id="invoice-document">

        <!-- Header -->
        <div class="invoice-header">
            <div class="invoice-brand">
                <div class="invoice-logo"><?= e($company['name'] ?? config('app.name', 'Pricetag')) ?></div>
                <div class="invoice-company-details">
                    <?= e($company['address']) ?><br>
                    <?= e($company['email']) ?> | <?= e($company['phone']) ?><br>
                    <?php if ($company['website']): ?>
                    <?= e($company['website']) ?><br>
                    <?php endif; ?>
                    <?php if ($company['vat_number']): ?>
                    VAT: <?= e($company['vat_number']) ?><br>
                    <?php endif; ?>
                    <?php if ($company['reg_number']): ?>
                    Reg: <?= e($company['reg_number']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="invoice-title-block">
                <div class="invoice-type-label" id="invoice-type-label"><?= e($typeLabel) ?></div>
                <div class="invoice-ref">
                    <strong>Invoice #:</strong> <?= e($order->invoice_number ?? $order->order_number) ?><br>
                    <strong>Order #:</strong> <?= e($order->order_number) ?><br>
                    <strong>Date:</strong> <?= date('d F Y', strtotime($order->created_at)) ?><br>
                    <?php if (!$isPaid && $invoiceType === 'tax_invoice'): ?>
                    <strong>Due Date:</strong> <?= $dueDate ?>
                    <?php endif; ?>
                </div>
                <span class="invoice-status <?= $isPaid ? 'paid' : ($order->payment_status === 'refunded' ? 'refunded' : 'pending') ?>">
                    <?= $isPaid ? 'PAID' : strtoupper($order->payment_status ?? 'PENDING') ?>
                </span>
            </div>
        </div>

        <!-- Addresses -->
        <div class="invoice-meta">
            <div class="invoice-meta-group">
                <h3>Bill To</h3>
                <p>
                    <strong><?= e($order->billing_first_name . ' ' . $order->billing_last_name) ?></strong><br>
                    <?php if (!empty($order->billing_company)): ?>
                    <?= e($order->billing_company) ?><br>
                    <?php endif; ?>
                    <?= e($order->billing_address_1) ?><br>
                    <?php if ($order->billing_address_2): ?>
                    <?= e($order->billing_address_2) ?><br>
                    <?php endif; ?>
                    <?= e($order->billing_city) ?>, <?= e($order->billing_province) ?> <?= e($order->billing_postal_code) ?><br>
                    <?php if (!empty($order->customer_email)): ?>
                    <?= e($order->customer_email) ?>
                    <?php elseif (!empty($order->email)): ?>
                    <?= e($order->email) ?>
                    <?php endif; ?>
                    <?php if (!empty($order->customer_phone)): ?>
                    <br><?= e($order->customer_phone) ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="invoice-meta-group" id="shipping-address">
                <h3>Ship To</h3>
                <p>
                    <strong><?= e($order->shipping_first_name . ' ' . $order->shipping_last_name) ?></strong><br>
                    <?php if (!empty($order->shipping_company)): ?>
                    <?= e($order->shipping_company) ?><br>
                    <?php endif; ?>
                    <?= e($order->shipping_address_1) ?><br>
                    <?php if ($order->shipping_address_2): ?>
                    <?= e($order->shipping_address_2) ?><br>
                    <?php endif; ?>
                    <?= e($order->shipping_city) ?>, <?= e($order->shipping_province) ?> <?= e($order->shipping_postal_code) ?>
                </p>
            </div>
            <div class="invoice-meta-group">
                <h3>Invoice Details</h3>
                <p>
                    <strong>Invoice Date:</strong> <?= date('d/m/Y', strtotime($order->created_at)) ?><br>
                    <?php if (!$isPaid && $invoiceType === 'tax_invoice'): ?>
                    <strong>Due Date:</strong> <?= date('d/m/Y', strtotime($order->created_at . ' +30 days')) ?><br>
                    <?php endif; ?>
                    <strong>Payment Terms:</strong> <?= $isPaid ? 'Paid' : 'Due on Receipt' ?><br>
                    <strong>Currency:</strong> ZAR (South African Rand)<br>
                    <?php if (!empty($order->shipping_method)): ?>
                    <strong>Shipping:</strong> <?= ucfirst(e($order->shipping_method)) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Quick Detail Badges -->
        <div class="invoice-details-row">
            <div class="invoice-detail-item">
                <div class="detail-label">Invoice Number</div>
                <div class="detail-value"><?= e($order->invoice_number ?? $order->order_number) ?></div>
            </div>
            <div class="invoice-detail-item">
                <div class="detail-label">Order Number</div>
                <div class="detail-value"><?= e($order->order_number) ?></div>
            </div>
            <div class="invoice-detail-item">
                <div class="detail-label">Date Issued</div>
                <div class="detail-value"><?= date('d M Y', strtotime($order->created_at)) ?></div>
            </div>
            <div class="invoice-detail-item">
                <div class="detail-label">Total Items</div>
                <div class="detail-value"><?= count($items) ?></div>
            </div>
            <div class="invoice-detail-item">
                <div class="detail-label">Amount Due</div>
                <div class="detail-value"><?= $isPaid ? 'R0.00' : formatPrice($order->total) ?></div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="invoice-body">
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 35%">Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right" id="tax-col-header">VAT (<?= $taxRate ?>%)</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineItems as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <div class="item-name"><?= e($item['name']) ?></div>
                            <?php if (!empty($item['sku'])): ?>
                            <div class="item-sku">SKU: <?= e($item['sku']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['variant_name'])): ?>
                            <div class="item-variant"><?= e($item['variant_name']) ?></div>
                            <?php elseif (!empty($item['variant'])): ?>
                            <div class="item-variant"><?= e($item['variant']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= (int) $item['quantity'] ?></td>
                        <td class="text-right"><?= formatPrice($item['price']) ?></td>
                        <td class="text-right tax-breakdown-cell"><?= formatPrice($item['vat_amount']) ?></td>
                        <td class="text-right"><?= formatPrice($item['line_total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="invoice-totals-wrapper">
                <div class="invoice-totals">
                    <div class="invoice-totals-row">
                        <span class="label">Subtotal</span>
                        <span class="value"><?= formatPrice($order->subtotal) ?></span>
                    </div>

                    <?php if (($order->discount_amount ?? $order->discount ?? 0) > 0): ?>
                    <div class="invoice-totals-row discount">
                        <span class="label">Discount<?= !empty($order->coupon_code) ? ' (' . e($order->coupon_code) . ')' : '' ?></span>
                        <span class="value">-<?= formatPrice($order->discount_amount ?? $order->discount) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="invoice-totals-row">
                        <span class="label">Shipping (<?= ucfirst(e($order->shipping_method ?? 'Standard')) ?>)</span>
                        <span class="value"><?= ($order->shipping_amount ?? $order->shipping ?? 0) > 0 ? formatPrice($order->shipping_amount ?? $order->shipping) : 'Free' ?></span>
                    </div>

                    <div class="invoice-totals-row" id="tax-breakdown">
                        <span class="label">VAT (<?= $taxRate ?>%<?= $taxInclusive ? ' incl.' : '' ?>)</span>
                        <span class="value"><?= formatPrice($order->tax_amount ?? $order->tax ?? $totalVat) ?></span>
                    </div>

                    <?php if ($taxInclusive): ?>
                    <div class="invoice-totals-row" id="tax-breakdown-excl" style="font-size: 0.8rem;">
                        <span class="label" style="color: #999;">Total excl. VAT</span>
                        <span class="value" style="color: #999;"><?= formatPrice($totalExclVat) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="invoice-totals-row grand-total">
                        <span class="label">Total</span>
                        <span class="value"><?= formatPrice($order->total) ?></span>
                    </div>

                    <?php if ($isPaid): ?>
                    <div class="invoice-totals-row paid-badge">
                        <span class="label">Amount Paid</span>
                        <span class="value"><?= formatPrice($order->total) ?></span>
                    </div>
                    <div class="invoice-totals-row amount-due">
                        <span class="label">Balance Due</span>
                        <span class="value" style="color: #155724;">R0.00</span>
                    </div>
                    <?php else: ?>
                    <div class="invoice-totals-row amount-due">
                        <span class="label">Amount Due</span>
                        <span class="value"><?= formatPrice($order->total) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Payment Info & Banking Details -->
        <div class="invoice-sections">
            <div class="invoice-section" id="payment-info">
                <h3>Payment Information</h3>
                <?php if ($payment): ?>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value"><?= ucfirst($payment['status'] ?? 'Pending') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gateway:</span>
                    <span class="info-value"><?= ucfirst($payment['gateway'] ?? 'Card') ?></span>
                </div>
                <?php if (!empty($payment['method'])): ?>
                <div class="info-row">
                    <span class="info-label">Method:</span>
                    <span class="info-value"><?= ucfirst($payment['method']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($payment['card_last_four'])): ?>
                <div class="info-row">
                    <span class="info-label">Card:</span>
                    <span class="info-value"><?= ucfirst($payment['card_brand'] ?? 'Card') ?> **** <?= e($payment['card_last_four']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($payment['transaction_id'])): ?>
                <div class="info-row">
                    <span class="info-label">Transaction:</span>
                    <span class="info-value" style="font-family: monospace; font-size: 0.8rem;"><?= e($payment['transaction_id']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($payment['paid_at'])): ?>
                <div class="info-row">
                    <span class="info-label">Paid At:</span>
                    <span class="info-value"><?= date('d M Y, H:i', strtotime($payment['paid_at'])) ?></span>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <p style="color: #888;">Payment pending.</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($company['bank_name'])): ?>
            <div class="invoice-section" id="banking-details">
                <h3>Banking Details (EFT)</h3>
                <div class="info-row">
                    <span class="info-label">Bank:</span>
                    <span class="info-value"><?= e($company['bank_name']) ?></span>
                </div>
                <?php if ($company['bank_account']): ?>
                <div class="info-row">
                    <span class="info-label">Account No:</span>
                    <span class="info-value" style="font-family: monospace;"><?= e($company['bank_account']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($company['bank_branch']): ?>
                <div class="info-row">
                    <span class="info-label">Branch Code:</span>
                    <span class="info-value"><?= e($company['bank_branch']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($company['bank_type']): ?>
                <div class="info-row">
                    <span class="info-label">Account Type:</span>
                    <span class="info-value"><?= e($company['bank_type']) ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Reference:</span>
                    <span class="info-value" style="font-weight: 600;"><?= e($order->order_number) ?></span>
                </div>
            </div>
            <?php else: ?>
            <div class="invoice-section" id="banking-details">
                <h3>Payment Method</h3>
                <p>Online payment via secure card processing.</p>
                <p style="margin-top: 0.5rem; color: #888; font-size: 0.8rem;">
                    Processed by <?= e(config('payment.gateways.yoco.name', 'Yoco')) ?> - PCI DSS compliant.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Customer Notes -->
        <?php if (!empty($order->customer_notes)): ?>
        <div class="invoice-notes" id="notes-section">
            <h3>Customer Notes</h3>
            <p><?= nl2br(e($order->customer_notes)) ?></p>
        </div>
        <?php endif; ?>

        <!-- Terms & Conditions -->
        <div class="invoice-terms" id="terms-section">
            <h3>Terms &amp; Conditions</h3>
            <ol>
                <li>Payment is due within 30 days of the invoice date unless otherwise stated.</li>
                <li>Goods remain the property of <?= e($company['name'] ?? config('app.name', 'Pricetag')) ?> until paid in full.</li>
                <li>Returns and refunds are subject to our returns policy. Items must be returned within 30 days of delivery in original condition.</li>
                <li>All prices are quoted in South African Rand (ZAR) and include VAT at <?= $taxRate ?>% where applicable.</li>
                <li>E&amp;OE - Errors and omissions excepted.</li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p class="footer-brand"><?= e($company['name'] ?? config('app.name', 'Pricetag')) ?></p>
            <p><?= e($company['address']) ?></p>
            <p>Email: <?= e($company['email']) ?> | Tel: <?= e($company['phone']) ?></p>
            <?php if ($company['vat_number']): ?>
            <p>VAT Number: <?= e($company['vat_number']) ?></p>
            <?php endif; ?>
            <?php if ($company['reg_number']): ?>
            <p>Company Registration: <?= e($company['reg_number']) ?></p>
            <?php endif; ?>
            <p class="footer-legal">This document is computer generated and is valid without signature.</p>
            <p class="footer-thankyou">Thank you for your business!</p>
        </div>
    </div>

    <!-- ================================================================
         JAVASCRIPT
         ================================================================ -->
    <script>
        /**
         * Toggle section visibility
         */
        function toggleSection(id, visible) {
            var el = document.getElementById(id);
            if (!el) return;
            if (visible) {
                el.classList.remove('section-hidden');
            } else {
                el.classList.add('section-hidden');
            }

            // Handle tax breakdown column in table
            if (id === 'tax-breakdown') {
                var taxHeader = document.getElementById('tax-col-header');
                var taxCells = document.querySelectorAll('.tax-breakdown-cell');
                var taxExcl = document.getElementById('tax-breakdown-excl');

                if (taxHeader) taxHeader.style.display = visible ? '' : 'none';
                taxCells.forEach(function(cell) {
                    cell.style.display = visible ? '' : 'none';
                });
                if (taxExcl) taxExcl.style.display = visible ? '' : 'none';
            }
        }

        /**
         * Change invoice type via URL parameter
         */
        function changeInvoiceType(type) {
            var url = new URL(window.location.href);
            url.searchParams.set('type', type);
            window.location.href = url.toString();
        }

        /**
         * Download as PDF using browser print dialog
         * with save-as-PDF instructions
         */
        function downloadPDF() {
            // Use the browser's print functionality which supports "Save as PDF"
            window.print();
        }
    </script>
</body>
</html>
