<!-- Admin Product Import -->
<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0">Import Products</h1>
    <div class="flex gap-2">
        <a href="<?= url('/admin/products/export') ?>" class="btn btn-secondary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export All Products
        </a>
        <a href="<?= url('/admin/products') ?>" class="btn btn-ghost">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Back to Products
        </a>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Import Form -->
    <div class="card">
        <div class="card-header">
            <h2 class="font-semibold">Upload CSV File</h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/admin/products/import') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="csv_file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" class="form-input" required>
                    <p class="form-help">Maximum file size: 10MB</p>
                </div>

                <div class="form-group mt-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="update_existing" value="1" class="form-checkbox">
                        <span>Update existing products (match by SKU)</span>
                    </label>
                    <p class="form-help">If unchecked, products with existing SKUs will be skipped</p>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Import Products
                    </button>
                </div>
            </form>

            <?php if (!empty($_SESSION['import_errors'])): ?>
            <div class="mt-6 p-4 bg-danger-50 border border-danger-200 rounded-lg">
                <h4 class="font-semibold text-danger-700 mb-2">Import Errors:</h4>
                <ul class="list-disc list-inside text-sm text-danger-600 space-y-1">
                    <?php foreach ($_SESSION['import_errors'] as $error): ?>
                    <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['import_errors']); ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Instructions -->
    <div class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">CSV Format Requirements</h2>
            </div>
            <div class="card-body">
                <p class="mb-4">Your CSV file must include the following required columns:</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 pr-4">Column</th>
                                <th class="text-left py-2 pr-4">Required</th>
                                <th class="text-left py-2">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">sku</td>
                                <td class="py-2 pr-4"><span class="badge badge-danger">Required</span></td>
                                <td class="py-2 text-muted">Unique product identifier</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">name</td>
                                <td class="py-2 pr-4"><span class="badge badge-danger">Required</span></td>
                                <td class="py-2 text-muted">Product name</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">price</td>
                                <td class="py-2 pr-4"><span class="badge badge-danger">Required</span></td>
                                <td class="py-2 text-muted">Product price (numeric)</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">description</td>
                                <td class="py-2 pr-4"><span class="badge badge-secondary">Optional</span></td>
                                <td class="py-2 text-muted">Full product description</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">short_description</td>
                                <td class="py-2 pr-4"><span class="badge badge-secondary">Optional</span></td>
                                <td class="py-2 text-muted">Brief description</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">status</td>
                                <td class="py-2 pr-4"><span class="badge badge-secondary">Optional</span></td>
                                <td class="py-2 text-muted">draft, active, inactive</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">compare_price</td>
                                <td class="py-2 pr-4"><span class="badge badge-secondary">Optional</span></td>
                                <td class="py-2 text-muted">Original price for discounts</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">stock_quantity</td>
                                <td class="py-2 pr-4"><span class="badge badge-secondary">Optional</span></td>
                                <td class="py-2 text-muted">Available stock (integer)</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">vendor</td>
                                <td class="py-2 pr-4"><span class="badge badge-secondary">Optional</span></td>
                                <td class="py-2 text-muted">Vendor name (must exist)</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">categories</td>
                                <td class="py-2 pr-4"><span class="badge badge-secondary">Optional</span></td>
                                <td class="py-2 text-muted">Category names separated by |</td>
                            </tr>
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs">is_featured</td>
                                <td class="py-2 pr-4"><span class="badge badge-secondary">Optional</span></td>
                                <td class="py-2 text-muted">yes/no or 1/0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Download Template</h2>
            </div>
            <div class="card-body">
                <p class="mb-4">Download a sample CSV template with all available columns:</p>
                <a href="<?= url('/admin/products/export') ?>" class="btn btn-outline">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download Template (Export Current Products)
                </a>
                <p class="form-help mt-2">This will export all existing products as a CSV file you can use as a template.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold">Tips</h2>
            </div>
            <div class="card-body">
                <ul class="space-y-2 text-sm text-muted">
                    <li class="flex items-start gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Save your Excel file as "CSV UTF-8 (Comma delimited)" for best compatibility</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>SKU values must be unique - duplicates will be skipped or updated</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Vendors and categories must already exist in the system</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>For multiple categories, separate them with | (e.g., "Electronics|Phones")</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Boolean fields accept: yes/no, true/false, 1/0</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.badge-danger { background: var(--color-danger-100); color: var(--color-danger-700); }
.badge-secondary { background: var(--color-gray-100); color: var(--color-gray-700); }
.bg-danger-50 { background: #fef2f2; }
.border-danger-200 { border-color: #fecaca; }
.text-danger-700 { color: #b91c1c; }
.text-danger-600 { color: #dc2626; }
</style>
