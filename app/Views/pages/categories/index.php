<!-- Categories Page - Full Width Modern Design -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs">
    <div class="categories-container">
        <div class="breadcrumb-item"><a href="<?php echo url('/'); ?>" class="breadcrumb-link">Home</a></div>
        <div class="breadcrumb-item"><span class="breadcrumb-current">Categories</span></div>
    </div>
</nav>

<div class="categories-page">
    <div class="categories-container">
        <!-- Page Header -->
        <div class="categories-header">
            <div class="categories-header-content">
                <h1 class="categories-title">Shop by Category</h1>
                <p class="categories-subtitle">Browse our wide selection of products organized by category. Find exactly what you're looking for.</p>
            </div>
        </div>

        <!-- Search and Sort Bar -->
        <div class="categories-toolbar">
            <div class="category-search">
                <svg class="category-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="M21 21l-4.35-4.35"></path>
                </svg>
                <input type="text"
                       id="category-search-input"
                       class="category-search-input"
                       placeholder="Type to search categories..."
                       autocomplete="off">
            </div>
            <div class="category-sort">
                <label class="sort-label">Sort By:</label>
                <select id="category-sort-select" class="sort-select">
                    <option value="alphabet">Alphabet</option>
                    <option value="products">Most Products</option>
                </select>
            </div>
        </div>

        <?php if (empty($categories)): ?>
        <div class="no-categories">
            <div class="no-categories-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <h2 class="no-categories-title">No categories available</h2>
            <p class="no-categories-text">Categories will appear here once they are added</p>
            <a href="<?php echo url('/'); ?>" class="btn btn-primary">Back to Home</a>
        </div>
        <?php else: ?>

        <?php
        // Group categories by first letter
        $groupedCategories = [];
        foreach ($categories as $category) {
            $firstLetter = strtoupper(substr($category->name, 0, 1));
            if (!ctype_alpha($firstLetter)) {
                $firstLetter = '#';
            }
            if (!isset($groupedCategories[$firstLetter])) {
                $groupedCategories[$firstLetter] = [];
            }
            $groupedCategories[$firstLetter][] = $category;
        }
        ksort($groupedCategories);
        ?>

        <div class="categories-layout">
            <!-- Quick Nav Sidebar -->
            <aside class="quick-nav">
                <div class="quick-nav-panel">
                    <h3 class="quick-nav-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        Quick Nav
                    </h3>
                    <div class="quick-nav-letters">
                        <?php foreach (array_keys($groupedCategories) as $letter): ?>
                        <a href="#letter-<?php echo $letter === '#' ? 'num' : $letter; ?>" class="quick-nav-letter-link">
                            <?php echo $letter; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="quick-nav-categories">
                        <?php foreach ($groupedCategories as $letter => $cats): ?>
                        <div class="quick-nav-group">
                            <span class="quick-nav-letter"><?php echo $letter; ?></span>
                            <?php foreach ($cats as $cat): ?>
                            <a href="<?php echo url('/categories/' . $cat->slug); ?>" class="quick-nav-category">
                                <?php echo e($cat->name); ?>
                                <?php if (isset($cat->product_count)): ?>
                                <span class="quick-nav-count"><?php echo $cat->product_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>

            <!-- Categories Grid -->
            <main class="categories-main">
                <?php foreach ($groupedCategories as $letter => $cats): ?>
                <div class="category-group" id="letter-<?php echo $letter === '#' ? 'num' : $letter; ?>">
                    <div class="category-group-header">
                        <h2 class="category-group-letter"><?php echo $letter; ?></h2>
                        <span class="category-group-count"><?php echo count($cats); ?> categories</span>
                    </div>

                    <div class="category-cards-grid">
                        <?php foreach ($cats as $category): ?>
                        <div class="category-card" data-name="<?php echo e(strtolower($category->name)); ?>">
                            <a href="<?php echo url('/categories/' . $category->slug); ?>" class="category-card-link">
                                <div class="category-card-image">
                                    <?php if ($category->image): ?>
                                    <img src="<?php echo url('storage/uploads/' . $category->image); ?>"
                                         alt="<?php echo e($category->name); ?>"
                                         loading="lazy">
                                    <?php else: ?>
                                    <div class="category-card-placeholder">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="14" width="7" height="7"></rect>
                                            <rect x="3" y="14" width="7" height="7"></rect>
                                        </svg>
                                    </div>
                                    <?php endif; ?>
                                    <div class="category-card-overlay"></div>
                                </div>
                                <div class="category-card-content">
                                    <h3 class="category-card-name"><?php echo e($category->name); ?></h3>
                                    <?php if (isset($category->product_count)): ?>
                                    <span class="category-card-count"><?php echo $category->product_count; ?> products</span>
                                    <?php endif; ?>
                                </div>
                            </a>

                            <!-- Subcategory Pills -->
                            <?php if (!empty($category->children)): ?>
                            <div class="category-card-pills">
                                <?php foreach (array_slice($category->children, 0, 4) as $child): ?>
                                <a href="<?php echo url('/categories/' . $child->slug); ?>" class="category-pill">
                                    <?php echo e($child->name); ?>
                                </a>
                                <?php endforeach; ?>
                                <?php if (count($category->children) > 4): ?>
                                <span class="category-pill category-pill-more">+<?php echo count($category->children) - 4; ?> more</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </main>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* =========================================================================
   CATEGORIES PAGE - Full Width Modern Design
   ========================================================================= */

.categories-page {
    min-height: 100vh;
    padding-bottom: var(--space-16);
}

.categories-container {
    width: 100%;
    max-width: 1920px;
    margin: 0 auto;
    padding: 0 var(--space-4);
}

@media (min-width: 1024px) {
    .categories-container {
        padding: 0 var(--space-6);
    }
}

@media (min-width: 1440px) {
    .categories-container {
        padding: 0 var(--space-8);
    }
}

/* Breadcrumbs */
.breadcrumbs .categories-container {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

/* Categories Header */
.categories-header {
    padding: var(--space-8) 0;
    margin-bottom: var(--space-6);
    border-bottom: var(--border-1) solid var(--color-border);
}

.categories-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-2) 0;
    background: linear-gradient(135deg, var(--color-text) 0%, var(--color-primary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.categories-subtitle {
    font-size: var(--text-base);
    color: var(--color-text-secondary);
    margin: 0;
    max-width: 600px;
}

/* Toolbar */
.categories-toolbar {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    margin-bottom: var(--space-8);
    padding: var(--space-5);
    background: linear-gradient(145deg, var(--color-background-elevated) 0%, var(--color-background-alt) 100%);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-2xl);
}

@media (min-width: 768px) {
    .categories-toolbar {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.category-search {
    position: relative;
    flex: 1;
    max-width: 500px;
}

.category-search-icon {
    position: absolute;
    left: var(--space-4);
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    color: var(--color-text-muted);
}

.category-search-input {
    width: 100%;
    height: var(--input-height);
    padding: 0 var(--space-4) 0 var(--space-12);
    background-color: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-xl);
    color: var(--color-text);
    font-size: var(--text-base);
    transition: var(--transition-all);
}

.category-search-input::placeholder {
    color: var(--color-text-muted);
}

.category-search-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(139, 43, 43, 0.2);
}

.category-sort {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.sort-label {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    white-space: nowrap;
}

.sort-select {
    padding: var(--space-2) var(--space-10) var(--space-2) var(--space-4);
    background-color: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-lg);
    color: var(--color-text);
    font-size: var(--text-sm);
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23a1a1aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
    background-position: right var(--space-3) center;
    background-repeat: no-repeat;
    background-size: 16px;
    cursor: pointer;
}

.sort-select:focus {
    outline: none;
    border-color: var(--color-primary);
}

/* Categories Layout */
.categories-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-8);
}

@media (min-width: 1024px) {
    .categories-layout {
        grid-template-columns: 280px 1fr;
    }
}

/* Quick Nav Sidebar */
.quick-nav {
    display: none;
}

@media (min-width: 1024px) {
    .quick-nav {
        display: block;
    }
}

.quick-nav-panel {
    position: sticky;
    top: calc(var(--header-height) + var(--space-4) + 48px);
    max-height: calc(100vh - var(--header-height) - var(--space-8) - 48px);
    overflow-y: auto;
    background: linear-gradient(145deg, var(--color-background-elevated) 0%, var(--color-background-alt) 100%);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-2xl);
    padding: var(--space-5);
}

.quick-nav-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-base);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-4) 0;
    padding-bottom: var(--space-3);
    border-bottom: var(--border-2) solid var(--color-primary);
}

.quick-nav-letters {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-1);
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-4);
    border-bottom: var(--border-1) solid var(--color-border);
}

.quick-nav-letter-link {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-xs);
    font-weight: var(--font-bold);
    color: var(--color-text-muted);
    background-color: var(--color-background);
    border-radius: var(--radius-md);
    transition: var(--transition-all);
}

.quick-nav-letter-link:hover {
    background-color: var(--color-primary);
    color: var(--color-text);
    transform: scale(1.1);
}

.quick-nav-categories {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.quick-nav-group {
    display: flex;
    flex-direction: column;
}

.quick-nav-letter {
    font-size: var(--text-sm);
    font-weight: var(--font-bold);
    color: var(--color-primary);
    padding: var(--space-2) 0;
    border-bottom: var(--border-1) solid var(--color-border);
    margin-bottom: var(--space-2);
}

.quick-nav-category {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-2) var(--space-3);
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    transition: var(--transition-colors);
    border-radius: var(--radius-md);
}

.quick-nav-category:hover {
    color: var(--color-text);
    background-color: var(--color-background-hover);
}

.quick-nav-count {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
    background-color: var(--color-background);
    padding: var(--space-0-5) var(--space-2);
    border-radius: var(--radius-full);
}

/* Category Groups */
.category-group {
    margin-bottom: var(--space-10);
}

.category-group-header {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
    padding-bottom: var(--space-3);
    border-bottom: var(--border-2) solid var(--color-border);
}

.category-group-letter {
    font-size: var(--text-4xl);
    font-weight: var(--font-bold);
    color: var(--color-primary);
    margin: 0;
    line-height: 1;
}

.category-group-count {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

/* Category Cards Grid */
.category-cards-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: var(--space-6);
}

@media (min-width: 640px) {
    .category-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .category-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1280px) {
    .category-cards-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1536px) {
    .category-cards-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Category Card */
.category-card {
    background: linear-gradient(145deg, var(--color-background-elevated) 0%, var(--color-background-alt) 100%);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-2xl);
    overflow: hidden;
    transition: var(--transition-all);
}

.category-card:hover {
    border-color: var(--color-primary);
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
}

.category-card-link {
    display: block;
}

.category-card-image {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    background-color: var(--color-background);
}

.category-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--duration-500) var(--ease-out);
}

.category-card:hover .category-card-image img {
    transform: scale(1.1);
}

.category-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.6) 0%, transparent 50%);
    pointer-events: none;
}

.category-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
    background: linear-gradient(145deg, var(--color-background) 0%, var(--color-background-alt) 100%);
}

.category-card-placeholder svg {
    width: 48px;
    height: 48px;
    opacity: 0.3;
}

.category-card-content {
    padding: var(--space-5);
}

.category-card-name {
    font-size: var(--text-lg);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    margin: 0 0 var(--space-1) 0;
}

.category-card-count {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

/* Subcategory Pills */
.category-card-pills {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    padding: 0 var(--space-5) var(--space-5);
}

.category-pill {
    padding: var(--space-1) var(--space-3);
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
    color: var(--color-text);
    background-color: var(--color-background);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-full);
    transition: var(--transition-all);
}

.category-pill:hover {
    background-color: var(--color-primary);
    border-color: var(--color-primary);
}

.category-pill-more {
    color: var(--color-text-muted);
    cursor: default;
}

.category-pill-more:hover {
    background-color: var(--color-background);
    border-color: var(--color-border);
}

/* No Categories */
.no-categories {
    text-align: center;
    padding: var(--space-16) var(--space-4);
    background: linear-gradient(145deg, var(--color-background-elevated) 0%, var(--color-background-alt) 100%);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-2xl);
}

.no-categories-icon {
    margin: 0 auto var(--space-6);
    color: var(--color-text-muted);
    opacity: 0.3;
}

.no-categories-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-2) 0;
}

.no-categories-text {
    font-size: var(--text-base);
    color: var(--color-text-muted);
    margin: 0 0 var(--space-6) 0;
}

/* Hidden class for search filter */
.category-card.hidden {
    display: none;
}

.category-group.hidden {
    display: none;
}
</style>

<script>
// Category search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('category-search-input');
    const categoryCards = document.querySelectorAll('.category-card');
    const categoryGroups = document.querySelectorAll('.category-group');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();

            categoryCards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                if (searchTerm === '' || name.includes(searchTerm)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });

            // Hide empty groups
            categoryGroups.forEach(group => {
                const visibleCards = group.querySelectorAll('.category-card:not(.hidden)');
                if (visibleCards.length === 0) {
                    group.classList.add('hidden');
                } else {
                    group.classList.remove('hidden');
                }
            });
        });
    }

    // Smooth scroll for quick nav
    document.querySelectorAll('.quick-nav-letter-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>
