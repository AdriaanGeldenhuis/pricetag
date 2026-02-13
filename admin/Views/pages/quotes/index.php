<!-- Admin Quotes List -->
<div class="flex justify-between items-center mb-6">
    <h1 class="admin-page-title mb-0">Quotes</h1>
</div>

<!-- Status Tabs -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="<?= url('/admin/quotes') ?>"
       class="btn <?= empty($currentStatus) ? 'btn-primary' : 'btn-outline' ?> btn-sm">
        All <?= !empty($statusCounts) ? '(' . array_sum($statusCounts) . ')' : '' ?>
    </a>
    <?php
    $tabs = [
        'submitted' => 'Submitted',
        'reviewed' => 'Reviewed',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
        'draft' => 'Draft',
        'converted' => 'Converted',
    ];
    foreach ($tabs as $key => $label):
        $count = $statusCounts[$key] ?? 0;
    ?>
    <a href="<?= url('/admin/quotes?status=' . $key) ?>"
       class="btn <?= $currentStatus === $key ? 'btn-primary' : 'btn-outline' ?> btn-sm">
        <?= $label ?> (<?= $count ?>)
    </a>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card mb-6">
    <div class="card-body">
        <form action="<?= url('/admin/quotes') ?>" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search quotes by number, title, contact..."
                       class="form-input">
            </div>
            <?php if ($currentStatus): ?>
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search || $currentStatus): ?>
            <a href="<?= url('/admin/quotes') ?>" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Quotes Table -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Quote</th>
                    <th>Title</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quotes)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-12">
                        No quotes found
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($quotes as $quote): ?>
                <tr>
                    <td>
                        <a href="<?= url('/admin/quotes/' . $quote['id']) ?>" class="font-medium text-primary">
                            #<?= e($quote['quote_number']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="font-medium"><?= e(truncate($quote['title'] ?? 'Untitled', 35)) ?></span>
                        <?php if ($quote['company_name']): ?>
                        <span class="text-xs text-muted block"><?= e($quote['company_name']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="font-medium">
                            <?= e($quote['first_name'] ?? '') ?> <?= e($quote['last_name'] ?? '') ?>
                        </span>
                        <span class="text-xs text-muted block"><?= e($quote['user_email'] ?? $quote['contact_email'] ?? '') ?></span>
                    </td>
                    <td class="text-muted"><?= $quote['item_count'] ?? 0 ?></td>
                    <td class="font-medium"><?= formatPrice((float)$quote['total']) ?></td>
                    <td>
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
                        $color = $statusColors[$quote['status']] ?? 'neutral';
                        ?>
                        <span class="badge badge-<?= $color ?>"><?= ucfirst($quote['status']) ?></span>
                    </td>
                    <td class="text-muted text-sm">
                        <?= date('d M Y', strtotime($quote['created_at'])) ?>
                        <span class="block text-xs"><?= date('H:i', strtotime($quote['created_at'])) ?></span>
                    </td>
                    <td>
                        <a href="<?= url('/admin/quotes/' . $quote['id']) ?>"
                           class="btn btn-ghost btn-sm btn-icon" title="View">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($pagination['last_page'] > 1): ?>
<div class="mt-6">
    <div class="flex justify-center gap-2">
        <?php if ($pagination['current_page'] > 1): ?>
        <a href="<?= url('/admin/quotes?page=' . ($pagination['current_page'] - 1) . ($currentStatus ? '&status=' . $currentStatus : '') . ($search ? '&search=' . urlencode($search) : '')) ?>"
           class="btn btn-outline btn-sm">&larr; Previous</a>
        <?php endif; ?>

        <span class="btn btn-ghost btn-sm" style="pointer-events: none;">
            Page <?= $pagination['current_page'] ?> of <?= $pagination['last_page'] ?>
        </span>

        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
        <a href="<?= url('/admin/quotes?page=' . ($pagination['current_page'] + 1) . ($currentStatus ? '&status=' . $currentStatus : '') . ($search ? '&search=' . urlencode($search) : '')) ?>"
           class="btn btn-outline btn-sm">Next &rarr;</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
