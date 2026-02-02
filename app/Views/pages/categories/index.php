<!-- Categories Page -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <div class="breadcrumb-item"><a href="<?= url('/') ?>" class="breadcrumb-link">Home</a></div>
    <div class="breadcrumb-item"><span class="breadcrumb-current">Categories</span></div>
</nav>

<div class="container py-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-semibold mb-2">Shop by Category</h1>
        <p class="text-muted">Browse our wide selection of products by category</p>
    </div>

    <?php if (empty($categories)): ?>
    <div class="text-center py-16">
        <svg class="mx-auto mb-4 text-muted" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <h2 class="text-xl font-semibold mb-2">No categories available</h2>
        <p class="text-muted mb-6">Categories will appear here once they are added</p>
        <a href="<?= url('/') ?>" class="btn btn-primary">Back to Home</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($categories as $category): ?>
        <a href="<?= url('/categories/' . $category->slug) ?>" class="card category-card group">
            <div class="category-card-image">
                <?php if ($category->image): ?>
                <img src="<?= url('storage/uploads/' . $category->image) ?>"
                     alt="<?= e($category->name) ?>"
                     class="w-full h-48 object-cover">
                <?php else: ?>
                <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                    <?php if ($category->icon): ?>
                    <i class="<?= e($category->icon) ?> text-4xl text-muted"></i>
                    <?php else: ?>
                    <svg class="w-16 h-16 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body text-center">
                <h3 class="font-semibold text-lg group-hover:text-primary transition-colors">
                    <?= e($category->name) ?>
                </h3>
                <?php if (isset($category->product_count)): ?>
                <p class="text-sm text-muted"><?= $category->product_count ?> Products</p>
                <?php endif; ?>
                <?php if ($category->description): ?>
                <p class="text-sm text-muted mt-2"><?= e(truncate($category->description, 80)) ?></p>
                <?php endif; ?>

                <?php if (!empty($category->children)): ?>
                <div class="mt-3 pt-3 border-t">
                    <p class="text-xs text-muted mb-2">Subcategories:</p>
                    <div class="flex flex-wrap justify-center gap-1">
                        <?php foreach (array_slice($category->children, 0, 3) as $child): ?>
                        <span class="badge badge-light text-xs"><?= e($child->name) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($category->children) > 3): ?>
                        <span class="badge badge-light text-xs">+<?= count($category->children) - 3 ?> more</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.category-card {
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
}
.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}
.category-card-image {
    overflow: hidden;
}
.category-card-image img {
    transition: transform 0.3s;
}
.category-card:hover .category-card-image img {
    transform: scale(1.05);
}
</style>
