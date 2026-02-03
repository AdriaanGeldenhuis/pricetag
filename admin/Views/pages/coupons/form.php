<!-- Admin Coupon Form -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $coupon ? 'Edit Coupon' : 'Create Coupon' ?></h1>
        <p class="admin-page-subtitle"><?= $coupon ? 'Update coupon settings' : 'Create a new discount coupon' ?></p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/coupons') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Coupons
        </a>
    </div>
</div>

<form action="<?= $coupon ? url('/admin/coupons/' . $coupon['id']) : url('/admin/coupons') ?>"
      method="POST" class="admin-form">
    <?= csrf_field() ?>
    <?php if ($coupon): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Coupon Details</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="code" name="code"
                                   value="<?= e($coupon['code'] ?? '') ?>"
                                   class="form-input font-mono uppercase" required
                                   placeholder="SUMMER20" style="text-transform: uppercase;">
                            <button type="button" onclick="generateCode()" class="admin-btn admin-btn-secondary">
                                Generate
                            </button>
                        </div>
                        <p class="form-help">Customers will enter this code at checkout</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-select" onchange="updateValueLabel()">
                                <option value="percentage" <?= ($coupon['type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage Discount</option>
                                <option value="fixed" <?= ($coupon['type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
                                <option value="free_shipping" <?= ($coupon['type'] ?? '') === 'free_shipping' ? 'selected' : '' ?>>Free Shipping</option>
                            </select>
                        </div>

                        <div class="form-group" id="valueGroup">
                            <label for="value" class="form-label">
                                <span id="valueLabel">Discount Value</span> <span class="text-danger">*</span>
                            </label>
                            <div class="input-with-addon">
                                <span class="input-addon" id="valuePrefix">%</span>
                                <input type="number" id="value" name="value"
                                       value="<?= e($coupon['value'] ?? '') ?>"
                                       class="form-input" min="0" step="0.01" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Restrictions -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Usage Restrictions</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="minimum_amount" class="form-label">Minimum Order Amount</label>
                            <div class="input-with-addon">
                                <span class="input-addon">R</span>
                                <input type="number" id="minimum_amount" name="minimum_amount"
                                       value="<?= e($coupon['minimum_amount'] ?? '') ?>"
                                       class="form-input" min="0" step="0.01"
                                       placeholder="No minimum">
                            </div>
                            <p class="form-help">Leave empty for no minimum</p>
                        </div>

                        <div class="form-group" id="maxDiscountGroup">
                            <label for="maximum_discount" class="form-label">Maximum Discount</label>
                            <div class="input-with-addon">
                                <span class="input-addon">R</span>
                                <input type="number" id="maximum_discount" name="maximum_discount"
                                       value="<?= e($coupon['maximum_discount'] ?? '') ?>"
                                       class="form-input" min="0" step="0.01"
                                       placeholder="No limit">
                            </div>
                            <p class="form-help">Cap the maximum discount (for percentage coupons)</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="usage_limit" class="form-label">Total Usage Limit</label>
                            <input type="number" id="usage_limit" name="usage_limit"
                                   value="<?= e($coupon['usage_limit'] ?? '') ?>"
                                   class="form-input" min="1" placeholder="Unlimited">
                            <p class="form-help">How many times can this coupon be used in total?</p>
                        </div>

                        <div class="form-group">
                            <label for="usage_per_user" class="form-label">Usage Per Customer</label>
                            <input type="number" id="usage_per_user" name="usage_per_user"
                                   value="<?= e($coupon['usage_per_user'] ?? 1) ?>"
                                   class="form-input" min="1" placeholder="1">
                            <p class="form-help">How many times can each customer use this?</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Schedule (Optional)</h2>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="starts_at" class="form-label">Start Date</label>
                            <input type="datetime-local" id="starts_at" name="starts_at"
                                   value="<?= $coupon && $coupon['starts_at'] ? date('Y-m-d\TH:i', strtotime($coupon['starts_at'])) : '' ?>"
                                   class="form-input">
                            <p class="form-help">When should this coupon become active?</p>
                        </div>

                        <div class="form-group">
                            <label for="expires_at" class="form-label">Expiry Date</label>
                            <input type="datetime-local" id="expires_at" name="expires_at"
                                   value="<?= $coupon && $coupon['expires_at'] ? date('Y-m-d\TH:i', strtotime($coupon['expires_at'])) : '' ?>"
                                   class="form-input">
                            <p class="form-help">When should this coupon expire?</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($usage)): ?>
            <!-- Usage History -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Recent Usage</h2>
                </div>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Discount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usage as $use): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('/admin/orders/' . $use['order_id']) ?>" class="text-primary">
                                        #<?= e($use['order_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($use['email']): ?>
                                    <?= e($use['first_name'] . ' ' . $use['last_name']) ?>
                                    <span class="text-muted text-xs block"><?= e($use['email']) ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">Guest</span>
                                    <?php endif; ?>
                                </td>
                                <td>R <?= number_format($use['discount_amount'], 2) ?></td>
                                <td class="text-muted"><?= date('M j, Y H:i', strtotime($use['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Status</h2>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   <?= ($coupon['is_active'] ?? 1) ? 'checked' : '' ?> class="form-checkbox">
                            <span>Coupon Active</span>
                        </label>
                        <p class="form-help">Inactive coupons cannot be used at checkout</p>
                    </div>
                </div>
            </div>

            <?php if ($coupon): ?>
            <!-- Usage Stats -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Usage Statistics</h2>
                </div>
                <div class="card-body">
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-muted">Times Used:</dt>
                            <dd class="font-semibold"><?= $coupon['used_count'] ?><?= $coupon['usage_limit'] ? '/' . $coupon['usage_limit'] : '' ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Created:</dt>
                            <dd><?= date('M j, Y', strtotime($coupon['created_at'])) ?></dd>
                        </div>
                        <?php if ($coupon['updated_at']): ?>
                        <div class="flex justify-between">
                            <dt class="text-muted">Last Updated:</dt>
                            <dd><?= date('M j, Y', strtotime($coupon['updated_at'])) ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <?php endif; ?>

            <!-- Preview -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Preview</h2>
                </div>
                <div class="card-body">
                    <div class="coupon-preview">
                        <div class="coupon-preview-code" id="previewCode"><?= e($coupon['code'] ?? 'CODE') ?></div>
                        <div class="coupon-preview-value" id="previewValue">
                            <?php if ($coupon): ?>
                                <?= $coupon['type'] === 'percentage' ? $coupon['value'] . '% OFF' : ($coupon['type'] === 'fixed' ? 'R' . number_format($coupon['value'], 2) . ' OFF' : 'FREE SHIPPING') ?>
                            <?php else: ?>
                                --% OFF
                            <?php endif; ?>
                        </div>
                        <div class="coupon-preview-terms" id="previewTerms">
                            <?php if ($coupon && $coupon['minimum_amount']): ?>
                            Min. order R<?= number_format($coupon['minimum_amount']) ?>
                            <?php else: ?>
                            No minimum order
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3">
                <button type="submit" class="admin-btn admin-btn-primary w-full">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" class="mr-2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <?= $coupon ? 'Update Coupon' : 'Create Coupon' ?>
                </button>

                <?php if ($coupon): ?>
                <button type="button" onclick="deleteCoupon()"
                        class="admin-btn admin-btn-danger w-full">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" class="mr-2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                    </svg>
                    Delete Coupon
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<?php if ($coupon): ?>
<!-- Delete Modal -->
<div class="admin-modal-overlay" id="deleteModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Coupon</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete this coupon? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form action="<?= url('/admin/coupons/' . $coupon['id']) ?>" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Coupon</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.input-with-addon {
    display: flex;
    align-items: stretch;
}

.input-addon {
    display: flex;
    align-items: center;
    padding: 0 var(--space-3);
    background: var(--color-background-alt);
    border: 1px solid var(--color-border);
    border-right: none;
    border-radius: var(--radius-md) 0 0 var(--radius-md);
    font-weight: var(--font-medium);
    color: var(--color-text-muted);
}

.input-with-addon .form-input {
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
}

.coupon-preview {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark, #1e40af) 100%);
    color: white;
    padding: var(--space-4);
    border-radius: var(--radius-lg);
    text-align: center;
}

.coupon-preview-code {
    font-family: monospace;
    font-size: var(--text-xl);
    font-weight: var(--font-bold);
    letter-spacing: 2px;
    margin-bottom: var(--space-2);
}

.coupon-preview-value {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    margin-bottom: var(--space-2);
}

.coupon-preview-terms {
    font-size: var(--text-xs);
    opacity: 0.8;
}
</style>

<script>
function generateCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('code').value = code;
    updatePreview();
}

function updateValueLabel() {
    const type = document.getElementById('type').value;
    const valueGroup = document.getElementById('valueGroup');
    const valuePrefix = document.getElementById('valuePrefix');
    const maxDiscountGroup = document.getElementById('maxDiscountGroup');

    if (type === 'free_shipping') {
        valueGroup.style.display = 'none';
        maxDiscountGroup.style.display = 'none';
    } else {
        valueGroup.style.display = 'block';
        if (type === 'percentage') {
            valuePrefix.textContent = '%';
            maxDiscountGroup.style.display = 'block';
        } else {
            valuePrefix.textContent = 'R';
            maxDiscountGroup.style.display = 'none';
        }
    }
    updatePreview();
}

function updatePreview() {
    const code = document.getElementById('code').value || 'CODE';
    const type = document.getElementById('type').value;
    const value = document.getElementById('value').value || 0;
    const minAmount = document.getElementById('minimum_amount').value;

    document.getElementById('previewCode').textContent = code.toUpperCase();

    let valueText = '';
    if (type === 'percentage') {
        valueText = value + '% OFF';
    } else if (type === 'fixed') {
        valueText = 'R' + parseFloat(value).toFixed(2) + ' OFF';
    } else {
        valueText = 'FREE SHIPPING';
    }
    document.getElementById('previewValue').textContent = valueText;

    let termsText = minAmount ? 'Min. order R' + parseFloat(minAmount).toFixed(0) : 'No minimum order';
    document.getElementById('previewTerms').textContent = termsText;
}

// Event listeners
document.getElementById('code').addEventListener('input', updatePreview);
document.getElementById('type').addEventListener('change', updatePreview);
document.getElementById('value').addEventListener('input', updatePreview);
document.getElementById('minimum_amount').addEventListener('input', updatePreview);

// Initialize
updateValueLabel();

// Delete modal
function deleteCoupon() {
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
