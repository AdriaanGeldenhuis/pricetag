<?php
/**
 * Advanced Invoice Template
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Supports: Tax Invoice, Proforma Invoice, Credit Note, Quote
 * Options: Print, Download PDF, Toggle sections
 */

$invoiceType = $invoiceType ?? 'tax_invoice';
$taxRate = $taxRate ?? 15;
$taxInclusive = $taxInclusive ?? true;
$company = $company ?? [];
$payment = $payment ?? null;
$branding = $branding ?? getBranding();

$typeLabels = [
    'tax_invoice'  => 'TAX INVOICE',
    'proforma'     => 'PROFORMA INVOICE',
    'credit_note'  => 'CREDIT NOTE',
    'quote'        => 'QUOTATION',
];

$typeLabel = $typeLabels[$invoiceType] ?? 'TAX INVOICE';

// Get primary color from branding
$primaryColor = $branding['primary_color'] ?? '#e91e63';

// Calculate per-line-item tax for detailed breakdown
$lineItems = [];
$totalExclVat = 0;
$totalVat = 0;
$totalQty = 0;

foreach ($items as $item) {
    $lineTotal = $item['price'] * $item['quantity'];
    $totalQty += (int) $item['quantity'];

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

// Payment terms and due date
$paymentTermsSetting = $company['payment_terms'] ?? 'due_on_receipt';
$termsDays = [
    'due_on_receipt' => 0,
    'net_7' => 7,
    'net_14' => 14,
    'net_30' => 30,
    'net_60' => 60,
];
$termsLabels = [
    'due_on_receipt' => 'Due on Receipt',
    'net_7' => 'Net 7',
    'net_14' => 'Net 14',
    'net_30' => 'Net 30',
    'net_60' => 'Net 60',
];
$dueDays = $termsDays[$paymentTermsSetting] ?? 30;
$dueDate = $dueDays > 0
    ? date('d F Y', strtotime($order->created_at . ' +' . $dueDays . ' days'))
    : date('d F Y', strtotime($order->created_at));
$paymentTermsLabel = $termsLabels[$paymentTermsSetting] ?? 'Due on Receipt';
$isPaid = in_array($order->payment_status, ['paid', 'completed'], true);

// Logo
$logoUrl = !empty($branding['logo']) ? url($branding['logo']) : '';
$logoHeight = $branding['logo_height'] ?? '50';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($typeLabel) ?> #<?= e($order->invoice_number ?? $order->order_number) ?> | <?= e($company['name'] ?? config('app.name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: <?= e($primaryColor) ?>;
            --brand-dark: color-mix(in srgb, <?= e($primaryColor) ?>, #000 25%);
            --brand-light: color-mix(in srgb, <?= e($primaryColor) ?>, #fff 90%);
            --brand-glow: color-mix(in srgb, <?= e($primaryColor) ?>, transparent 85%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #1a1a2e;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
        }

        /* ================================================================
           TOOLBAR
           ================================================================ */
        .invoice-toolbar {
            max-width: 900px;
            margin: 1.5rem auto 0;
            padding: 0.85rem 1.5rem;
            background: #fff;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 -2px 20px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            border-bottom: 1px solid #eef0f4;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .toolbar-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1a1a2e;
        }

        .toolbar-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.65rem;
            border-radius: 100px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .toolbar-badge.paid { background: #ecfdf5; color: #059669; }
        .toolbar-badge.pending { background: #fffbeb; color: #d97706; }
        .toolbar-badge.refunded { background: #fef2f2; color: #dc2626; }

        .toolbar-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            border: 1px solid #e2e5eb;
            border-radius: 8px;
            background: #fff;
            color: #555;
            font-size: 0.78rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-toolbar:hover {
            background: #f8f9fb;
            border-color: #c8cdd5;
            color: #1a1a2e;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .btn-toolbar.primary {
            background: var(--brand);
            color: #fff;
            border-color: var(--brand);
        }

        .btn-toolbar.primary:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
            box-shadow: 0 4px 14px var(--brand-glow);
        }

        .btn-toolbar svg { width: 15px; height: 15px; flex-shrink: 0; }

        .type-selector {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .type-selector label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .type-selector select {
            padding: 0.4rem 0.6rem;
            border: 1px solid #e2e5eb;
            border-radius: 8px;
            font-size: 0.78rem;
            font-family: inherit;
            background: #fff;
            color: #333;
            cursor: pointer;
            font-weight: 500;
        }

        /* ================================================================
           DISPLAY OPTIONS
           ================================================================ */
        .invoice-options {
            max-width: 900px;
            margin: 0 auto;
            padding: 0.6rem 1.5rem;
            background: #f8f9fb;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex-wrap: wrap;
            font-size: 0.78rem;
            border-left: 1px solid #eef0f4;
            border-right: 1px solid #eef0f4;
        }

        .invoice-options label {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            color: #666;
            cursor: pointer;
            user-select: none;
            font-weight: 500;
            transition: color 0.15s;
        }

        .invoice-options label:hover { color: #333; }

        .invoice-options input[type="checkbox"] { accent-color: var(--brand); }

        .options-label {
            font-weight: 700;
            color: #999;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* ================================================================
           INVOICE DOCUMENT
           ================================================================ */
        .invoice {
            max-width: 900px;
            margin: 0 auto 3rem;
            background: #fff;
            box-shadow: 0 4px 40px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
            border-radius: 0 0 12px 12px;
            overflow: hidden;
        }

        /* ---- Header with gradient accent ---- */
        .invoice-header {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 2.25rem 2.5rem 2rem;
            background: linear-gradient(135deg, #fff 0%, var(--brand-light) 100%);
        }

        .invoice-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 2.5rem;
            right: 2.5rem;
            height: 3px;
            background: linear-gradient(90deg, var(--brand), var(--brand-dark));
            border-radius: 3px;
        }

        .invoice-brand { flex: 1; }

        .invoice-logo-img {
            max-height: 56px;
            width: auto;
            display: block;
        }

        .invoice-logo-text {
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.03em;
        }

        .invoice-company-details {
            margin-top: 0.65rem;
            font-size: 0.78rem;
            color: #6b7280;
            line-height: 1.7;
        }

        .invoice-title-block { text-align: right; }

        .invoice-type-label {
            font-size: 1.5rem;
            font-weight: 900;
            color: #1a1a2e;
            letter-spacing: 0.06em;
            line-height: 1.2;
        }

        .invoice-ref {
            margin-top: 0.75rem;
            font-size: 0.82rem;
            color: #6b7280;
            line-height: 1.9;
        }

        .invoice-ref-row {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .invoice-ref-label {
            color: #9ca3af;
            font-weight: 500;
            min-width: 90px;
            text-align: right;
        }

        .invoice-ref-value {
            color: #1a1a2e;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .invoice-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.85rem;
            padding: 0.35rem 1rem;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .invoice-status::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .invoice-status.paid { background: #ecfdf5; color: #059669; }
        .invoice-status.pending { background: #fffbeb; color: #d97706; }
        .invoice-status.refunded { background: #fef2f2; color: #dc2626; }

        /* ---- Address / Meta Grid ---- */
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            padding: 1.75rem 2.5rem;
            border-bottom: 1px solid #f0f1f5;
        }

        .invoice-meta-group h3 {
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #9ca3af;
            margin-bottom: 0.6rem;
            font-weight: 700;
        }

        .invoice-meta-group p {
            margin: 0;
            line-height: 1.75;
            font-size: 0.82rem;
            color: #4b5563;
        }

        .invoice-meta-group p strong {
            font-weight: 600;
            color: #1a1a2e;
        }

        /* ---- Quick Stats Bar ---- */
        .invoice-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            border-bottom: 1px solid #f0f1f5;
        }

        .invoice-stat {
            padding: 1rem 1.25rem;
            text-align: center;
            border-right: 1px solid #f0f1f5;
            transition: background 0.2s;
        }

        .invoice-stat:last-child { border-right: none; }
        .invoice-stat:hover { background: #fafbfd; }

        .invoice-stat .stat-label {
            font-size: 0.58rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .invoice-stat .stat-value {
            font-size: 0.88rem;
            font-weight: 700;
            color: #1a1a2e;
            font-variant-numeric: tabular-nums;
        }

        .invoice-stat.highlight .stat-value { color: var(--brand); }

        /* ---- Items Table ---- */
        .invoice-body { padding: 1.75rem 2.5rem 2rem; }

        .invoice-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1.75rem;
        }

        .invoice-table thead th {
            text-align: left;
            padding: 0.7rem 0.75rem;
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #fff;
            background: linear-gradient(135deg, #1a1a2e, #2d2d4e);
            font-weight: 700;
            white-space: nowrap;
        }

        .invoice-table thead th:first-child { border-radius: 8px 0 0 0; }
        .invoice-table thead th:last-child { border-radius: 0 8px 0 0; }

        .invoice-table thead th.text-right,
        .invoice-table td.text-right { text-align: right; }

        .invoice-table thead th.text-center,
        .invoice-table td.text-center { text-align: center; }

        .invoice-table tbody tr { transition: background 0.15s; }
        .invoice-table tbody tr:hover { background: #fafbfd; }

        .invoice-table tbody td {
            padding: 0.85rem 0.75rem;
            border-bottom: 1px solid #f0f1f5;
            vertical-align: top;
            font-size: 0.84rem;
        }

        .invoice-table tbody tr:last-child td {
            border-bottom: 2px solid #e5e7eb;
        }

        .item-name { font-weight: 600; color: #1a1a2e; }

        .item-sku {
            font-size: 0.72rem;
            color: #9ca3af;
            margin-top: 0.15rem;
            font-family: 'SF Mono', 'Cascadia Code', 'Consolas', monospace;
        }

        .item-variant {
            display: inline-block;
            margin-top: 0.2rem;
            padding: 0.1rem 0.45rem;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 0.7rem;
            color: #6b7280;
            font-weight: 500;
        }

        /* ---- Totals ---- */
        .invoice-totals-wrapper {
            display: flex;
            justify-content: flex-end;
        }

        .invoice-totals { width: 360px; }

        .invoice-totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            font-size: 0.84rem;
        }

        .invoice-totals-row .label { color: #6b7280; font-weight: 500; }
        .invoice-totals-row .value { font-weight: 600; color: #1a1a2e; font-variant-numeric: tabular-nums; }

        .invoice-totals-row.discount .value { color: var(--brand); }

        .invoice-totals-row.grand-total {
            border-top: 2px solid #1a1a2e;
            margin-top: 0.6rem;
            padding-top: 0.85rem;
        }

        .invoice-totals-row.grand-total .label {
            font-size: 1rem;
            font-weight: 800;
            color: #1a1a2e;
        }

        .invoice-totals-row.grand-total .value {
            font-size: 1.2rem;
            font-weight: 900;
            color: #1a1a2e;
        }

        .invoice-totals-row.amount-due {
            background: linear-gradient(135deg, var(--brand-light), #fff);
            margin: 0.6rem -0.85rem 0;
            padding: 0.85rem;
            border-radius: 10px;
            border: 1px solid var(--brand-glow);
        }

        .invoice-totals-row.amount-due .label { font-weight: 700; color: var(--brand); }

        .invoice-totals-row.amount-due .value {
            font-weight: 900;
            color: var(--brand);
            font-size: 1.1rem;
        }

        .invoice-totals-row.amount-due.paid-state {
            background: linear-gradient(135deg, #ecfdf5, #fff);
            border-color: rgba(5, 150, 105, 0.15);
        }

        .invoice-totals-row.amount-due.paid-state .label { color: #059669; }
        .invoice-totals-row.amount-due.paid-state .value { color: #059669; }

        .invoice-totals-row.paid-row .value { color: #059669; font-weight: 700; }

        /* ---- Info Sections (Payment & Banking) ---- */
        .invoice-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-top: 1px solid #f0f1f5;
        }

        .invoice-section {
            padding: 1.5rem 2.5rem;
        }

        .invoice-section:first-child { border-right: 1px solid #f0f1f5; }

        .invoice-section h3 {
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            margin-bottom: 0.85rem;
            font-weight: 700;
            padding-bottom: 0.55rem;
            border-bottom: 1px solid #f0f1f5;
        }

        .invoice-section p {
            font-size: 0.82rem;
            margin: 0.3rem 0;
            line-height: 1.7;
            color: #6b7280;
        }

        .invoice-section .info-row {
            display: flex;
            gap: 0.5rem;
            padding: 0.3rem 0;
            font-size: 0.82rem;
        }

        .invoice-section .info-label {
            color: #9ca3af;
            min-width: 100px;
            font-weight: 600;
        }

        .invoice-section .info-value {
            color: #1a1a2e;
            font-weight: 500;
        }

        /* ---- Notes ---- */
        .invoice-notes {
            padding: 1.25rem 2.5rem;
            border-top: 1px solid #f0f1f5;
        }

        .invoice-notes h3 {
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .invoice-notes p { font-size: 0.82rem; color: #6b7280; line-height: 1.7; }

        /* ---- Terms ---- */
        .invoice-terms {
            padding: 1.25rem 2.5rem;
            border-top: 1px solid #f0f1f5;
            background: #fafbfd;
        }

        .invoice-terms h3 {
            font-size: 0.62rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .invoice-terms ol {
            font-size: 0.76rem;
            color: #9ca3af;
            line-height: 1.9;
            padding-left: 1.25rem;
        }

        /* ---- Footer ---- */
        .invoice-footer {
            position: relative;
            padding: 1.75rem 2.5rem;
            background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4e 100%);
            text-align: center;
            color: rgba(255,255,255,0.6);
            font-size: 0.78rem;
        }

        .invoice-footer p { margin: 0.2rem 0; }

        .invoice-footer .footer-brand {
            font-weight: 800;
            color: #fff;
            font-size: 0.9rem;
            letter-spacing: -0.01em;
        }

        .invoice-footer .footer-divider {
            width: 40px;
            height: 2px;
            background: var(--brand);
            margin: 0.6rem auto;
            border-radius: 2px;
        }

        .invoice-footer .footer-legal {
            margin-top: 0.75rem;
            font-size: 0.68rem;
            color: rgba(255,255,255,0.35);
        }

        .invoice-footer .footer-thankyou {
            margin-top: 0.75rem;
            font-size: 0.88rem;
            color: var(--brand);
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        /* ================================================================
           RESPONSIVE
           ================================================================ */
        @media (max-width: 720px) {
            .invoice-header { flex-direction: column; gap: 1.5rem; }
            .invoice-title-block { text-align: left; }
            .invoice-ref-row { justify-content: flex-start; }
            .invoice-ref-label { text-align: left; }
            .invoice-meta { grid-template-columns: 1fr; gap: 1.25rem; }
            .invoice-stats { grid-template-columns: repeat(3, 1fr); }
            .invoice-stat:nth-child(3) { border-right: none; }
            .invoice-table { font-size: 0.75rem; }
            .invoice-totals { width: 100%; }
            .invoice-sections { grid-template-columns: 1fr; }
            .invoice-section:first-child { border-right: none; border-bottom: 1px solid #f0f1f5; }
            .toolbar-left, .toolbar-right { width: 100%; justify-content: center; }
            .invoice-options { justify-content: center; }
        }

        /* ================================================================
           PRINT
           ================================================================ */
        @media print {
            body { background: white; font-size: 11px; }
            .no-print, .invoice-toolbar, .invoice-options { display: none !important; }
            .invoice { box-shadow: none; margin: 0; max-width: 100%; border-radius: 0; }
            .invoice-header { background: #fff !important; }
            .invoice-logo-text {
                background: none !important;
                -webkit-text-fill-color: var(--brand) !important;
                color: var(--brand) !important;
            }
            .invoice-table thead th {
                background: #1a1a2e !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .invoice-footer {
                background: #1a1a2e !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .invoice-stats .invoice-stat { border-color: #e5e7eb; }
        }

        .section-hidden { display: none !important; }
    </style>
</head>
<body>

    <!-- ================================================================
         TOOLBAR
         ================================================================ -->
    <div class="invoice-toolbar no-print">
        <div class="toolbar-left">
            <a href="<?= url('/account/orders/' . $order->id) ?>" class="btn-toolbar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                Back
            </a>
            <span class="toolbar-title"><?= e($typeLabel) ?></span>
            <span class="toolbar-badge <?= $isPaid ? 'paid' : ($order->payment_status === 'refunded' ? 'refunded' : 'pending') ?>">
                <?= $isPaid ? 'Paid' : ucfirst($order->payment_status ?? 'Pending') ?>
            </span>
        </div>
        <div class="toolbar-right">
            <div class="type-selector">
                <label for="invoice-type">Type</label>
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
            <button class="btn-toolbar primary" onclick="window.print()" title="Download as PDF">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download PDF
            </button>
        </div>
    </div>

    <!-- ================================================================
         DISPLAY OPTIONS
         ================================================================ -->
    <div class="invoice-options no-print">
        <span class="options-label">Display:</span>
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
                <?php if ($logoUrl): ?>
                    <img src="<?= e($logoUrl) ?>" alt="<?= e($company['name'] ?? config('app.name')) ?>" class="invoice-logo-img" style="height: <?= e($logoHeight) ?>px; width: auto;">
                <?php else: ?>
                    <div class="invoice-logo-text"><?= e($company['name'] ?? config('app.name', 'Pricetag')) ?></div>
                <?php endif; ?>
                <div class="invoice-company-details">
                    <?= e($company['address']) ?><br>
                    <?= e($company['email']) ?> | <?= e($company['phone']) ?>
                    <?php if ($company['website']): ?><br><?= e($company['website']) ?><?php endif; ?>
                    <?php if ($company['vat_number']): ?><br>VAT: <?= e($company['vat_number']) ?><?php endif; ?>
                    <?php if ($company['reg_number']): ?> | Reg: <?= e($company['reg_number']) ?><?php endif; ?>
                </div>
            </div>
            <div class="invoice-title-block">
                <div class="invoice-type-label" id="invoice-type-label"><?= e($typeLabel) ?></div>
                <div class="invoice-ref">
                    <div class="invoice-ref-row">
                        <span class="invoice-ref-label">Invoice #</span>
                        <span class="invoice-ref-value"><?= e($order->invoice_number ?? $order->order_number) ?></span>
                    </div>
                    <div class="invoice-ref-row">
                        <span class="invoice-ref-label">Order #</span>
                        <span class="invoice-ref-value"><?= e($order->order_number) ?></span>
                    </div>
                    <div class="invoice-ref-row">
                        <span class="invoice-ref-label">Date</span>
                        <span class="invoice-ref-value"><?= date('d F Y', strtotime($order->created_at)) ?></span>
                    </div>
                    <?php if (!$isPaid && $invoiceType === 'tax_invoice'): ?>
                    <div class="invoice-ref-row">
                        <span class="invoice-ref-label">Due Date</span>
                        <span class="invoice-ref-value"><?= $dueDate ?></span>
                    </div>
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
                    <?php if (!empty($order->billing_company)): ?><?= e($order->billing_company) ?><br><?php endif; ?>
                    <?= e($order->billing_address_1) ?><br>
                    <?php if ($order->billing_address_2): ?><?= e($order->billing_address_2) ?><br><?php endif; ?>
                    <?= e($order->billing_city) ?>, <?= e($order->billing_province) ?> <?= e($order->billing_postal_code) ?><br>
                    <?php if (!empty($order->customer_email)): ?><?= e($order->customer_email) ?>
                    <?php elseif (!empty($order->email)): ?><?= e($order->email) ?><?php endif; ?>
                    <?php if (!empty($order->customer_phone)): ?><br><?= e($order->customer_phone) ?><?php endif; ?>
                </p>
            </div>
            <div class="invoice-meta-group" id="shipping-address">
                <h3>Ship To</h3>
                <p>
                    <strong><?= e($order->shipping_first_name . ' ' . $order->shipping_last_name) ?></strong><br>
                    <?php if (!empty($order->shipping_company)): ?><?= e($order->shipping_company) ?><br><?php endif; ?>
                    <?= e($order->shipping_address_1) ?><br>
                    <?php if ($order->shipping_address_2): ?><?= e($order->shipping_address_2) ?><br><?php endif; ?>
                    <?= e($order->shipping_city) ?>, <?= e($order->shipping_province) ?> <?= e($order->shipping_postal_code) ?>
                </p>
            </div>
            <div class="invoice-meta-group">
                <h3>Invoice Details</h3>
                <p>
                    <strong>Date:</strong> <?= date('d/m/Y', strtotime($order->created_at)) ?><br>
                    <?php if (!$isPaid && $invoiceType === 'tax_invoice'): ?>
                    <strong>Due:</strong> <?= date('d/m/Y', strtotime($order->created_at . ' +30 days')) ?><br>
                    <?php endif; ?>
                    <strong>Terms:</strong> <?= $isPaid ? 'Paid' : e($paymentTermsLabel) ?><br>
                    <strong>Currency:</strong> ZAR<br>
                    <?php if (!empty($order->shipping_method)): ?>
                    <strong>Shipping:</strong> <?= ucfirst(e($order->shipping_method)) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Quick Stats Bar -->
        <div class="invoice-stats">
            <div class="invoice-stat">
                <div class="stat-label">Invoice</div>
                <div class="stat-value"><?= e($order->invoice_number ?? $order->order_number) ?></div>
            </div>
            <div class="invoice-stat">
                <div class="stat-label">Order</div>
                <div class="stat-value"><?= e($order->order_number) ?></div>
            </div>
            <div class="invoice-stat">
                <div class="stat-label">Date</div>
                <div class="stat-value"><?= date('d M Y', strtotime($order->created_at)) ?></div>
            </div>
            <div class="invoice-stat">
                <div class="stat-label">Items</div>
                <div class="stat-value"><?= $totalQty ?></div>
            </div>
            <div class="invoice-stat highlight">
                <div class="stat-label"><?= $isPaid ? 'Balance Due' : 'Amount Due' ?></div>
                <div class="stat-value"><?= $isPaid ? 'R0.00' : formatPrice($order->total) ?></div>
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
                        <th class="text-right tax-breakdown-cell" id="tax-col-header">VAT (<?= $taxRate ?>%)</th>
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
                            <span class="item-variant"><?= e($item['variant_name']) ?></span>
                            <?php elseif (!empty($item['variant'])): ?>
                            <span class="item-variant"><?= e($item['variant']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= (int) $item['quantity'] ?></td>
                        <td class="text-right"><?= formatPrice($item['price']) ?></td>
                        <td class="text-right tax-breakdown-cell"><?= formatPrice($item['vat_amount']) ?></td>
                        <td class="text-right" style="font-weight: 600;"><?= formatPrice($item['line_total']) ?></td>
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
                    <div class="invoice-totals-row" id="tax-breakdown-excl" style="font-size: 0.78rem;">
                        <span class="label" style="color: #c0c4cc;">Total excl. VAT</span>
                        <span class="value" style="color: #c0c4cc; font-weight: 500;"><?= formatPrice($totalExclVat) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="invoice-totals-row grand-total">
                        <span class="label">Total</span>
                        <span class="value"><?= formatPrice($order->total) ?></span>
                    </div>

                    <?php if ($isPaid): ?>
                    <div class="invoice-totals-row paid-row">
                        <span class="label">Amount Paid</span>
                        <span class="value"><?= formatPrice($order->total) ?></span>
                    </div>
                    <div class="invoice-totals-row amount-due paid-state">
                        <span class="label">Balance Due</span>
                        <span class="value">R0.00</span>
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
                    <span class="info-label">Status</span>
                    <span class="info-value"><?= ucfirst($payment['status'] ?? 'Pending') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gateway</span>
                    <span class="info-value"><?= ucfirst($payment['gateway'] ?? 'Card') ?></span>
                </div>
                <?php if (!empty($payment['method'])): ?>
                <div class="info-row">
                    <span class="info-label">Method</span>
                    <span class="info-value"><?= ucfirst($payment['method']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($payment['card_last_four'])): ?>
                <div class="info-row">
                    <span class="info-label">Card</span>
                    <span class="info-value"><?= ucfirst($payment['card_brand'] ?? 'Card') ?> **** <?= e($payment['card_last_four']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($payment['transaction_id'])): ?>
                <div class="info-row">
                    <span class="info-label">Transaction</span>
                    <span class="info-value" style="font-family: 'SF Mono', 'Consolas', monospace; font-size: 0.78rem;"><?= e($payment['transaction_id']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($payment['paid_at'])): ?>
                <div class="info-row">
                    <span class="info-label">Paid At</span>
                    <span class="info-value"><?= date('d M Y, H:i', strtotime($payment['paid_at'])) ?></span>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <p>Payment pending.</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($company['bank_name'])): ?>
            <div class="invoice-section" id="banking-details">
                <h3>Banking Details (EFT)</h3>
                <div class="info-row">
                    <span class="info-label">Bank</span>
                    <span class="info-value"><?= e($company['bank_name']) ?></span>
                </div>
                <?php if ($company['bank_account']): ?>
                <div class="info-row">
                    <span class="info-label">Account No</span>
                    <span class="info-value" style="font-family: 'SF Mono', 'Consolas', monospace;"><?= e($company['bank_account']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($company['bank_branch']): ?>
                <div class="info-row">
                    <span class="info-label">Branch Code</span>
                    <span class="info-value"><?= e($company['bank_branch']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($company['bank_type']): ?>
                <div class="info-row">
                    <span class="info-label">Account Type</span>
                    <span class="info-value"><?= e($company['bank_type']) ?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Reference</span>
                    <span class="info-value" style="font-weight: 700;"><?= e($order->order_number) ?></span>
                </div>
            </div>
            <?php else: ?>
            <div class="invoice-section" id="banking-details">
                <h3>Payment Method</h3>
                <p>Online payment via secure card processing.</p>
                <p style="margin-top: 0.4rem; font-size: 0.76rem;">
                    Processed by <?= e(config('payment.gateways.yoco.name', 'Yoco')) ?> - PCI DSS compliant.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Notes -->
        <?php if (!empty($order->customer_notes) || !empty($company['notes'])): ?>
        <div class="invoice-notes" id="notes-section">
            <h3>Notes</h3>
            <?php if (!empty($order->customer_notes)): ?>
            <p><strong>Customer:</strong> <?= nl2br(e($order->customer_notes)) ?></p>
            <?php endif; ?>
            <?php if (!empty($company['notes'])): ?>
            <p><?= nl2br(e($company['notes'])) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Terms & Conditions -->
        <div class="invoice-terms" id="terms-section">
            <h3>Terms &amp; Conditions</h3>
            <ol>
                <li>Payment terms: <?= e($paymentTermsLabel) ?>. Late payments may incur additional charges.</li>
                <li>Goods remain the property of <?= e($company['name'] ?? config('app.name', 'Pricetag')) ?> until paid in full.</li>
                <li>Returns and refunds are subject to our returns policy. Items must be returned within 30 days of delivery in original condition.</li>
                <li>All prices are quoted in South African Rand (ZAR) and include VAT at <?= $taxRate ?>% where applicable.</li>
                <li>E&amp;OE - Errors and omissions excepted.</li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p class="footer-brand"><?= e($company['name'] ?? config('app.name', 'Pricetag')) ?></p>
            <div class="footer-divider"></div>
            <p><?= e($company['address']) ?></p>
            <p><?= e($company['email']) ?> | <?= e($company['phone']) ?></p>
            <?php if ($company['vat_number']): ?>
            <p>VAT: <?= e($company['vat_number']) ?></p>
            <?php endif; ?>
            <?php if ($company['reg_number']): ?>
            <p>Reg: <?= e($company['reg_number']) ?></p>
            <?php endif; ?>
            <p class="footer-legal"><?= e(!empty($company['footer_text']) ? $company['footer_text'] : 'This document is computer generated and is valid without signature.') ?></p>
            <p class="footer-thankyou">Thank you for your business!</p>
        </div>
    </div>

    <!-- ================================================================
         JAVASCRIPT
         ================================================================ -->
    <script>
        function toggleSection(id, visible) {
            var el = document.getElementById(id);
            if (!el) return;
            el.classList.toggle('section-hidden', !visible);

            if (id === 'tax-breakdown') {
                var cells = document.querySelectorAll('.tax-breakdown-cell');
                var taxExcl = document.getElementById('tax-breakdown-excl');
                cells.forEach(function(cell) { cell.style.display = visible ? '' : 'none'; });
                if (taxExcl) taxExcl.style.display = visible ? '' : 'none';
            }
        }

        function changeInvoiceType(type) {
            var url = new URL(window.location.href);
            url.searchParams.set('type', type);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
