<!-- Account - Create Quote -->
<div class="account-page">
    <div class="container py-8">
        <div class="account-layout">
            <!-- Sidebar Navigation -->
            <?php include APP_PATH . '/Views/pages/account/_sidebar.php'; ?>

            <!-- Main Content -->
            <div class="account-main">
                <!-- Breadcrumb -->
                <div class="account-breadcrumb">
                    <a href="<?= url('/account/quotes') ?>">Quotes</a>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <span>New Quote</span>
                </div>

                <!-- Page Header -->
                <div class="account-page-header">
                    <div>
                        <h1 class="account-page-title">Create New Quote</h1>
                        <p class="text-muted text-sm">Start a quote request - you can add products after creating it</p>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php if ($msg = flash('error')): ?>
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <div class="alert-content"><?= e($msg) ?></div>
                </div>
                <?php endif; ?>

                <!-- Create Form -->
                <div class="card">
                    <div class="card-body">
                        <form action="<?= url('/account/quotes') ?>" method="POST">
                            <?= csrfField() ?>

                            <div class="form-group">
                                <label class="form-label" for="title">Quote Title *</label>
                                <input type="text" id="title" name="title" class="form-input" required
                                       placeholder="e.g. Office supplies for Q1, Bulk order for project X"
                                       value="<?= e(old('title')) ?>">
                                <p class="form-hint">Give your quote a descriptive name so you can easily find it later.</p>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="contact_name">Contact Name</label>
                                    <input type="text" id="contact_name" name="contact_name" class="form-input"
                                           value="<?= e(old('contact_name', $user->first_name . ' ' . $user->last_name)) ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="contact_email">Contact Email</label>
                                    <input type="email" id="contact_email" name="contact_email" class="form-input"
                                           value="<?= e(old('contact_email', $user->email)) ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="contact_phone">Contact Phone</label>
                                    <input type="tel" id="contact_phone" name="contact_phone" class="form-input"
                                           value="<?= e(old('contact_phone', $user->phone ?? '')) ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="company_name">Company Name</label>
                                    <input type="text" id="company_name" name="company_name" class="form-input"
                                           value="<?= e(old('company_name')) ?>"
                                           placeholder="Optional">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="valid_until">Valid Until</label>
                                <input type="date" id="valid_until" name="valid_until" class="form-input"
                                       value="<?= e(old('valid_until', date('Y-m-d', strtotime('+30 days')))) ?>"
                                       min="<?= date('Y-m-d') ?>">
                                <p class="form-hint">Quote validity period. Defaults to 30 days from today.</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="customer_notes">Notes</label>
                                <textarea id="customer_notes" name="customer_notes" class="form-textarea" rows="3"
                                          placeholder="Any special requirements, delivery preferences, etc."><?= e(old('customer_notes')) ?></textarea>
                            </div>

                            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--color-border);">
                                <a href="<?= url('/account/quotes') ?>" class="btn btn-ghost">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    Create Quote
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_PATH . '/Views/pages/account/_styles.php'; ?>

<style>
.form-textarea {
    resize: vertical;
    min-height: 80px;
}
</style>
