<!-- Categories Page - Dark Theme with Alphabetical Navigation -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <div class="breadcrumb-item"><a href="<?php echo url('/'); ?>" class="breadcrumb-link">Home</a></div>
    <div class="breadcrumb-item"><span class="breadcrumb-current">Categories</span></div>
</nav>

<div class="container py-8">
    <!-- Page Header -->
    <div class="categories-header">
        <div class="categories-intro">
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
        <svg class="no-categories-icon" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
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
            <h3 class="quick-nav-title">Quick Nav</h3>
            <div class="quick-nav-letters">
                <?php foreach (array_keys($groupedCategories) as $letter): ?>
                <a href="#letter-<?php echo $letter === '#' ? 'num' : $letter; ?>" class="quick-nav-letter">
                    <?php echo $letter; ?>
                </a>
                <?php
                // Show categories under this letter
                foreach ($groupedCategories[$letter] as $cat):
                ?>
                <a href="<?php echo url('/categories/' . $cat->slug); ?>" class="quick-nav-category">
                    <?php echo e($cat->name); ?>
                    <?php if (isset($cat->product_count)): ?>
                    <span class="quick-nav-count">(<?php echo $cat->product_count; ?>)</span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </aside>

        <!-- Categories Grid -->
        <div class="categories-main">
            <?php foreach ($groupedCategories as $letter => $cats): ?>
            <div class="category-group" id="letter-<?php echo $letter === '#' ? 'num' : $letter; ?>">
                <h2 class="category-group-letter"><?php echo $letter; ?></h2>

                <div class="category-cards">
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
                            </div>
                            <div class="category-card-content">
                                <h3 class="category-card-name"><?php echo e($category->name); ?></h3>
                            </div>
                        </a>

                        <!-- Subcategory Pills -->
                        <?php if (!empty($category->children)): ?>
                        <div class="category-card-pills">
                            <?php foreach ($category->children as $child): ?>
                            <a href="<?php echo url('/categories/' . $child->slug); ?>" class="category-pill">
                                <?php echo e($child->name); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Categories Header */
.categories-header {
    margin-bottom: var(--space-8);
}

.categories-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-2) 0;
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
    padding: var(--space-4);
    background-color: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-xl);
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
    border-radius: var(--radius-lg);
    color: var(--color-text);
    font-size: var(--text-base);
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

/* Categories Layout */
.categories-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-8);
}

@media (min-width: 1024px) {
    .categories-layout {
        grid-template-columns: 220px 1fr;
    }
}

/* Quick Nav */
.quick-nav {
    display: none;
    position: sticky;
    top: calc(var(--header-height) + var(--space-4));
    height: fit-content;
    max-height: calc(100vh - var(--header-height) - var(--space-8));
    overflow-y: auto;
    padding: var(--space-4);
    background-color: var(--color-background-elevated);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-xl);
}

@media (min-width: 1024px) {
    .quick-nav {
        display: block;
    }
}

.quick-nav-title {
    font-size: var(--text-base);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-4) 0;
}

.quick-nav-letters {
    display: flex;
    flex-direction: column;
}

.quick-nav-letter {
    font-size: var(--text-lg);
    font-weight: var(--font-bold);
    color: var(--color-accent);
    padding: var(--space-2) 0;
    margin-top: var(--space-3);
    border-bottom: var(--border-1) solid var(--color-border);
}

.quick-nav-letter:first-child {
    margin-top: 0;
}

.quick-nav-category {
    display: block;
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
    color: var(--color-text-muted);
    font-size: var(--text-xs);
}

/* Category Groups */
.category-group {
    margin-bottom: var(--space-8);
}

.category-group-letter {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: 0 0 var(--space-4) 0;
    padding-bottom: var(--space-2);
    border-bottom: var(--border-2) solid var(--color-border);
}

/* Category Cards */
.category-cards {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.category-card {
    background: linear-gradient(135deg, var(--color-background-elevated) 0%, var(--color-background-alt) 50%, var(--color-background-hover) 100%);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-xl);
    overflow: hidden;
    transition: var(--transition-all);
}

.category-card:hover {
    border-color: var(--color-primary);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}

.category-card-link {
    display: flex;
    align-items: center;
    padding: var(--space-4);
}

.category-card-image {
    width: 80px;
    height: 80px;
    border-radius: var(--radius-lg);
    overflow: hidden;
    flex-shrink: 0;
    background-color: var(--color-background);
}

.category-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
}

.category-card-placeholder svg {
    width: 32px;
    height: 32px;
    opacity: 0.5;
}

.category-card-content {
    flex: 1;
    padding-left: var(--space-4);
}

.category-card-name {
    font-size: var(--text-lg);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    margin: 0;
}

/* Subcategory Pills */
.category-card-pills {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    padding: 0 var(--space-4) var(--space-4) var(--space-4);
    margin-top: calc(-1 * var(--space-2));
}

.category-pill {
    padding: var(--space-1) var(--space-3);
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
    color: var(--color-text);
    background-color: var(--color-background-hover);
    border: var(--border-1) solid var(--color-border);
    border-radius: var(--radius-full);
    transition: var(--transition-colors);
}

.category-pill:hover {
    background-color: var(--color-primary);
    border-color: var(--color-primary);
}

/* No Categories */
.no-categories {
    text-align: center;
    padding: var(--space-16) var(--space-4);
}

.no-categories-icon {
    margin: 0 auto var(--space-6);
    color: var(--color-text-muted);
    opacity: 0.5;
}

.no-categories-title {
    font-size: var(--text-xl);
    font-weight: var(--font-semibold);
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
});
</script>
