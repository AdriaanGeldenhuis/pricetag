<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Banners & Sliders</h1>
        <p class="admin-page-subtitle">Manage promotional banners</p>
    </div>
    <a href="<?php echo url('/admin/banners/create'); ?>" class="admin-btn admin-btn-primary">Add Banner</a>
</div>

<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($banners)): ?>
                <tr>
                    <td colspan="5" class="text-center py-8">No banners yet. <a href="<?php echo url('/admin/banners/create'); ?>">Add one</a></td>
                </tr>
                <?php else: ?>
                <?php foreach ($banners as $banner): ?>
                <tr>
                    <td>
                        <?php if (!empty($banner['image'])): ?>
                        <img src="<?php echo e($banner['image']); ?>" alt="" style="width:80px;height:40px;object-fit:cover;">
                        <?php else: ?>
                        <span class="text-muted">No image</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($banner['title'] ?: 'Untitled'); ?></td>
                    <td><?php echo e($banner['location']); ?></td>
                    <td>
                        <?php if (!empty($banner['is_active'])): ?>
                        <span class="admin-badge success">Active</span>
                        <?php else: ?>
                        <span class="admin-badge neutral">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo url('/admin/banners/' . $banner['id'] . '/edit'); ?>" class="admin-btn admin-btn-sm">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
