<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Homepage Builder</h1>
        <p class="admin-page-subtitle">Manage homepage sections</p>
    </div>
</div>

<div class="admin-card">
    <div class="card-body">
        <?php if (empty($sections)): ?>
        <div class="text-center py-8">
            <p class="text-muted">No sections configured yet.</p>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($sections as $section): ?>
            <div class="flex items-center justify-between p-4 border rounded">
                <div>
                    <strong><?php echo e($section['title'] ?: $section['type']); ?></strong>
                    <span class="text-muted ml-2"><?php echo e($section['type']); ?></span>
                </div>
                <div class="flex gap-2">
                    <?php if (!empty($section['is_active'])): ?>
                    <span class="admin-badge success">Active</span>
                    <?php else: ?>
                    <span class="admin-badge neutral">Inactive</span>
                    <?php endif; ?>
                    <a href="<?php echo url('/admin/homepage/' . $section['id'] . '/edit'); ?>" class="admin-btn admin-btn-sm">Edit</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
