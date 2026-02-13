<!-- Admin Quote Detail -->
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="<?= url('/admin/quotes') ?>" class="text-sm text-muted hover:text-primary">&larr; Back to Quotes</a>
        <h1 class="admin-page-title mb-0">Quote #<?= e($quote->quote_number) ?></h1>
        <p class="text-muted"><?= date('d M Y \a\t H:i', strtotime($quote->created_at)) ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?= url('/account/quotes/' . $quote->id . '/print') ?>" target="_blank" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            Print
        </a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Quote Info -->
        <?php if ($quote->title): ?>
        <div class="card">
            <div class="card-body">
                <h2 class="font-semibold" style="margin: 0 0 0.25rem;"><?= e($quote->title) ?></h2>
                <?php if ($quote->customer_notes): ?>
                <p class="text-muted text-sm" style="margin: 0; white-space: pre-wrap;"><?= e($quote->customer_notes) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quote Items -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Quote Items (<?= count($items) ?>)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-12">No items in this quote</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <span class="font-medium"><?= e($item['name']) ?></span>
                                <?php if ($item['sku']): ?>
                                <span class="text-xs text-muted block">SKU: <?= e($item['sku']) ?></span>
                                <?php endif; ?>
                                <?php if ($item['variant_name']): ?>
                                <span class="text-xs text-muted"><?= e($item['variant_name']) ?></span>
                                <?php endif; ?>
                                <?php if ($item['notes']): ?>
                                <span class="text-xs text-muted block" style="font-style: italic;"><?= e($item['notes']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= formatPrice((float)$item['price']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-right font-medium"><?= formatPrice((float)$item['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">Subtotal</td>
                            <td class="text-right"><?= formatPrice((float)$quote->subtotal) ?></td>
                        </tr>
                        <?php if ((float)$quote->tax_amount > 0): ?>
                        <tr>
                            <td colspan="3" class="text-right">VAT (incl.)</td>
                            <td class="text-right"><?= formatPrice((float)$quote->tax_amount) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="font-bold">
                            <td colspan="3" class="text-right">Total</td>
                            <td class="text-right"><?= formatPrice((float)$quote->total) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Contact Details -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Contact Details</h2>
            </div>
            <div class="card-body">
                <div class="grid md:grid-cols-2 gap-4">
                    <?php if ($quote->contact_name): ?>
                    <div>
                        <p class="text-xs text-muted" style="text-transform: uppercase;">Contact Name</p>
                        <p class="font-medium"><?= e($quote->contact_name) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($quote->contact_email): ?>
                    <div>
                        <p class="text-xs text-muted" style="text-transform: uppercase;">Email</p>
                        <p class="font-medium"><?= e($quote->contact_email) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($quote->contact_phone): ?>
                    <div>
                        <p class="text-xs text-muted" style="text-transform: uppercase;">Phone</p>
                        <p class="font-medium"><?= e($quote->contact_phone) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($quote->company_name): ?>
                    <div>
                        <p class="text-xs text-muted" style="text-transform: uppercase;">Company</p>
                        <p class="font-medium"><?= e($quote->company_name) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Status Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Quote Status</h2>
            </div>
            <div class="card-body">
                <?php
                $statusColors = [
                    'draft' => 'neutral',
                    'submitted' => 'primary',
                    'reviewed' => 'warning',
                    'accepted' => 'success',
                    'rejected' => 'danger',
                    'expired' => 'neutral',
                    'converted' => 'success',
                ];
                $color = $statusColors[$quote->status] ?? 'neutral';
                ?>
                <div class="text-center mb-4">
                    <span class="badge badge-<?= $color ?> badge-lg"><?= ucfirst($quote->status) ?></span>
                </div>

                <form action="<?= url('/admin/quotes/' . $quote->id . '/status') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="status" class="form-label">Update Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="draft" <?= $quote->status === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="submitted" <?= $quote->status === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                            <option value="reviewed" <?= $quote->status === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                            <option value="accepted" <?= $quote->status === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                            <option value="rejected" <?= $quote->status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="expired" <?= $quote->status === 'expired' ? 'selected' : '' ?>>Expired</option>
                            <option value="converted" <?= $quote->status === 'converted' ? 'selected' : '' ?>>Converted</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Update Status</button>
                </form>
            </div>
        </div>

        <!-- Quote Info -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Quote Info</h2>
            </div>
            <div class="card-body">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-muted">Quote #</span>
                    <span class="font-medium"><?= e($quote->quote_number) ?></span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-muted">Created</span>
                    <span><?= date('d M Y', strtotime($quote->created_at)) ?></span>
                </div>
                <?php if ($quote->valid_until): ?>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-muted">Valid Until</span>
                    <span <?= $quote->isExpired() ? 'style="color: var(--color-danger);"' : '' ?>>
                        <?= date('d M Y', strtotime($quote->valid_until)) ?>
                        <?= $quote->isExpired() ? '(Expired)' : '' ?>
                    </span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between items-center">
                    <span class="text-muted">Total</span>
                    <span class="font-bold"><?= formatPrice((float)$quote->total) ?></span>
                </div>
            </div>
        </div>

        <!-- Customer -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Customer</h2>
            </div>
            <div class="card-body">
                <?php if ($quoteUser): ?>
                <p class="font-medium"><?= e($quoteUser->first_name) ?> <?= e($quoteUser->last_name) ?></p>
                <p class="text-sm text-muted"><?= e($quoteUser->email) ?></p>
                <?php if ($quoteUser->phone): ?>
                <p class="text-sm text-muted"><?= e($quoteUser->phone) ?></p>
                <?php endif; ?>
                <a href="<?= url('/admin/customers/' . $quoteUser->id) ?>" class="btn btn-outline btn-sm mt-3">
                    View Customer
                </a>
                <?php else: ?>
                <p class="text-muted">Unknown customer</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Admin Notes -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Admin Notes</h2>
            </div>
            <div class="card-body">
                <?php if ($quote->admin_notes): ?>
                <div style="padding: 0.75rem; background: var(--color-background-alt); border-radius: var(--radius-md); margin-bottom: 1rem; font-size: 0.875rem; white-space: pre-wrap;">
                    <?= e($quote->admin_notes) ?>
                </div>
                <?php endif; ?>

                <form action="<?= url('/admin/quotes/' . $quote->id . '/note') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <textarea name="admin_notes" rows="3" class="form-input"
                                  placeholder="Add a note (visible to customer)..."><?= e($quote->admin_notes ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline w-full">Save Note</button>
                </form>
            </div>
        </div>
    </div>
</div>
