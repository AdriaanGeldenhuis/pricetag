<!-- Admin Reviews -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Product Reviews</h1>
        <p class="admin-page-subtitle">
            <?= $stats['pending'] ?? 0 ?> pending review<?= ($stats['pending'] ?? 0) != 1 ? 's' : '' ?>
        </p>
    </div>
</div>

<!-- Stats -->
<div class="admin-stats-grid mb-6">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Reviews</div>
        <div class="admin-stat-value"><?= $stats['total'] ?? 0 ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Pending Approval</div>
        <div class="admin-stat-value text-warning"><?= $stats['pending'] ?? 0 ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Average Rating</div>
        <div class="admin-stat-value"><?= number_format($stats['avg_rating'] ?? 0, 1) ?> / 5</div>
    </div>
</div>

<!-- Filters -->
<div class="admin-card mb-6">
    <form method="GET" class="admin-filters p-4">
        <div class="admin-filter-group">
            <label>Status</label>
            <select name="status" class="admin-select" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
            </select>
        </div>
        <div class="admin-filter-group">
            <label>Rating</label>
            <select name="rating" class="admin-select" onchange="this.form.submit()">
                <option value="">All Ratings</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>" <?= ($filters['rating'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </form>
</div>

<!-- Reviews -->
<div class="reviews-list">
    <?php if (empty($reviews)): ?>
    <div class="admin-card">
        <div class="admin-empty">
            <h3 class="admin-empty-title">No reviews yet</h3>
            <p class="admin-empty-text">Reviews will appear here when customers submit them.</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($reviews as $review): ?>
    <div class="admin-card review-card mb-4" data-id="<?= $review['id'] ?>">
        <div class="review-header">
            <div class="review-product">
                <a href="<?= url('/admin/products/' . $review['product_id'] . '/edit') ?>" class="font-semibold">
                    <?= e($review['product_name']) ?>
                </a>
            </div>
            <div class="review-meta">
                <div class="review-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg viewBox="0 0 24 24" fill="<?= $i <= $review['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" width="16" height="16" class="<?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <?php endfor; ?>
                </div>
                <span class="admin-badge <?= $review['is_approved'] ? 'active' : 'warning' ?>">
                    <?= $review['is_approved'] ? 'Approved' : 'Pending' ?>
                </span>
                <?php if ($review['is_verified']): ?>
                <span class="admin-badge neutral">Verified Purchase</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="review-content">
            <?php if ($review['title']): ?>
            <h4><?= e($review['title']) ?></h4>
            <?php endif; ?>
            <p><?= e($review['content']) ?></p>
        </div>

        <div class="review-footer">
            <div class="review-author">
                <?php if ($review['first_name']): ?>
                <strong><?= e($review['first_name'] . ' ' . $review['last_name']) ?></strong>
                <span class="text-muted"><?= e($review['email']) ?></span>
                <?php else: ?>
                <span class="text-muted">Anonymous</span>
                <?php endif; ?>
                <span class="text-muted">- <?= date('M j, Y', strtotime($review['created_at'])) ?></span>
            </div>
            <div class="review-actions">
                <?php if (!$review['is_approved']): ?>
                <button type="button" onclick="approveReview(<?= $review['id'] ?>)" class="admin-btn admin-btn-success admin-btn-sm">Approve</button>
                <?php else: ?>
                <button type="button" onclick="rejectReview(<?= $review['id'] ?>)" class="admin-btn admin-btn-secondary admin-btn-sm">Unapprove</button>
                <?php endif; ?>
                <form method="POST" action="<?= url('/admin/reviews/' . $review['id']) ?>" style="display: inline;" onsubmit="return confirm('Delete this review?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.review-card { padding: var(--space-4); }
.review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-3); flex-wrap: wrap; gap: var(--space-2); }
.review-meta { display: flex; align-items: center; gap: var(--space-3); }
.review-rating { display: flex; gap: 2px; }
.review-content { margin-bottom: var(--space-3); }
.review-content h4 { font-size: var(--text-base); margin-bottom: var(--space-1); }
.review-content p { color: var(--color-text-muted); }
.review-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-2); }
.review-author { font-size: var(--text-sm); display: flex; gap: var(--space-2); align-items: center; flex-wrap: wrap; }
.review-actions { display: flex; gap: var(--space-2); }
</style>

<script>
async function approveReview(id) {
    await fetch('/admin/reviews/' + id + '/approve', {
        method: 'POST',
        headers: { 'X-CSRF-Token': '<?= csrf_token() ?>' }
    });
    location.reload();
}

async function rejectReview(id) {
    await fetch('/admin/reviews/' + id + '/reject', {
        method: 'POST',
        headers: { 'X-CSRF-Token': '<?= csrf_token() ?>' }
    });
    location.reload();
}
</script>
