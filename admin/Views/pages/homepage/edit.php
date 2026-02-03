<!-- Admin Homepage Section Edit -->
<?php
$sectionInfo = $sectionTypes[$section['type']] ?? ['name' => ucfirst($section['type'])];
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Edit: <?= e($sectionInfo['name']) ?></h1>
        <p class="admin-page-subtitle"><?= e($sectionInfo['description'] ?? '') ?></p>
    </div>
    <div class="admin-page-actions">
        <a href="<?= url('/admin/homepage') ?>" class="admin-btn admin-btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to Homepage Builder
        </a>
    </div>
</div>

<form action="<?= url('/admin/homepage/' . $section['id']) ?>" method="POST" class="admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="_method" value="PUT">

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Settings -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Section Settings</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="title" class="form-label">Section Title</label>
                        <input type="text" id="title" name="title"
                               value="<?= e($section['title']) ?>" class="form-input"
                               placeholder="e.g., Shop by Category">
                    </div>

                    <div class="form-group">
                        <label for="subtitle" class="form-label">Subtitle (Optional)</label>
                        <input type="text" id="subtitle" name="subtitle"
                               value="<?= e($section['subtitle']) ?>" class="form-input"
                               placeholder="e.g., Browse our most popular categories">
                    </div>
                </div>
            </div>

            <!-- Section-Specific Configuration -->
            <?php if ($section['type'] === 'trust_badges'): ?>
            <!-- Trust Badges Config -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Trust Badges</h2>
                </div>
                <div class="card-body">
                    <div id="badgesList">
                        <?php
                        $badges = $section['config']['badges'] ?? [
                            ['icon' => 'truck', 'title' => 'Free Shipping', 'description' => 'On orders over R500'],
                            ['icon' => 'shield', 'title' => 'Secure Payment', 'description' => '100% secure checkout'],
                            ['icon' => 'refresh-cw', 'title' => 'Easy Returns', 'description' => '30-day return policy'],
                            ['icon' => 'headphones', 'title' => '24/7 Support', 'description' => 'Here to help'],
                        ];
                        foreach ($badges as $i => $badge):
                        ?>
                        <div class="badge-item mb-4 p-4 bg-neutral-50 rounded-lg">
                            <div class="grid md:grid-cols-3 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Icon</label>
                                    <select name="badge_icons[]" class="form-select">
                                        <?php
                                        $icons = ['truck', 'shield', 'refresh-cw', 'headphones', 'award', 'heart', 'check-circle', 'star', 'lock', 'clock'];
                                        foreach ($icons as $icon): ?>
                                        <option value="<?= $icon ?>" <?= ($badge['icon'] ?? '') === $icon ? 'selected' : '' ?>><?= ucfirst(str_replace('-', ' ', $icon)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="badge_titles[]" value="<?= e($badge['title'] ?? '') ?>" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="badge_descriptions[]" value="<?= e($badge['description'] ?? '') ?>" class="form-input">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addBadge()" class="admin-btn admin-btn-secondary admin-btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Badge
                    </button>
                </div>
            </div>

            <?php elseif ($section['type'] === 'featured_categories'): ?>
            <!-- Featured Categories Config -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Category Selection</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="columns" class="form-label">Columns</label>
                            <select id="columns" name="columns" class="form-select">
                                <?php for ($i = 2; $i <= 6; $i++): ?>
                                <option value="<?= $i ?>" <?= ($section['config']['columns'] ?? 6) == $i ? 'selected' : '' ?>><?= $i ?> columns</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="show_product_count" value="1"
                                       <?= !empty($section['config']['show_product_count']) ? 'checked' : '' ?> class="form-checkbox">
                                <span>Show product count</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Select Categories to Feature</label>
                        <div class="categories-grid">
                            <?php foreach ($categories as $category): ?>
                            <label class="category-checkbox">
                                <input type="checkbox" name="category_ids[]" value="<?= $category['id'] ?>"
                                       <?= in_array($category['id'], $section['config']['category_ids'] ?? []) ? 'checked' : '' ?>>
                                <span><?= e($category['name']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="form-help">Leave empty to auto-select featured categories</p>
                    </div>
                </div>
            </div>

            <?php elseif (in_array($section['type'], ['trending_products', 'new_arrivals'])): ?>
            <!-- Product Section Config -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Display Settings</h2>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="limit" class="form-label">Number of Products</label>
                            <select id="limit" name="limit" class="form-select">
                                <?php foreach ([4, 8, 12, 16, 20] as $num): ?>
                                <option value="<?= $num ?>" <?= ($section['config']['limit'] ?? 8) == $num ? 'selected' : '' ?>><?= $num ?> products</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="columns" class="form-label">Columns</label>
                            <select id="columns" name="columns" class="form-select">
                                <?php for ($i = 2; $i <= 6; $i++): ?>
                                <option value="<?= $i ?>" <?= ($section['config']['columns'] ?? 4) == $i ? 'selected' : '' ?>><?= $i ?> columns</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($section['type'] === 'testimonials'): ?>
            <!-- Testimonials Config -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Testimonials</h2>
                </div>
                <div class="card-body">
                    <div id="testimonialsList">
                        <?php
                        $testimonials = $section['config']['testimonials'] ?? [];
                        if (empty($testimonials)) {
                            $testimonials = [['name' => '', 'title' => '', 'content' => '', 'rating' => 5]];
                        }
                        foreach ($testimonials as $i => $testimonial):
                        ?>
                        <div class="testimonial-item mb-4 p-4 bg-neutral-50 rounded-lg">
                            <div class="grid md:grid-cols-2 gap-4 mb-3">
                                <div class="form-group">
                                    <label class="form-label">Customer Name</label>
                                    <input type="text" name="testimonial_names[]" value="<?= e($testimonial['name'] ?? '') ?>" class="form-input" placeholder="John Doe">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Title/Location</label>
                                    <input type="text" name="testimonial_titles[]" value="<?= e($testimonial['title'] ?? '') ?>" class="form-input" placeholder="Cape Town">
                                </div>
                            </div>
                            <div class="grid md:grid-cols-4 gap-4">
                                <div class="form-group md:col-span-3">
                                    <label class="form-label">Testimonial</label>
                                    <textarea name="testimonial_contents[]" rows="2" class="form-input"><?= e($testimonial['content'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Rating</label>
                                    <select name="testimonial_ratings[]" class="form-select">
                                        <?php for ($r = 5; $r >= 1; $r--): ?>
                                        <option value="<?= $r ?>" <?= ($testimonial['rating'] ?? 5) == $r ? 'selected' : '' ?>><?= $r ?> stars</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addTestimonial()" class="admin-btn admin-btn-secondary admin-btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Testimonial
                    </button>
                </div>
            </div>

            <?php elseif ($section['type'] === 'newsletter'): ?>
            <!-- Newsletter Config -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Newsletter Settings</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="show_name_field" value="1"
                                   <?= !empty($section['config']['show_name_field']) ? 'checked' : '' ?> class="form-checkbox">
                            <span>Show name field</span>
                        </label>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="button_text" class="form-label">Button Text</label>
                            <input type="text" id="button_text" name="button_text"
                                   value="<?= e($section['config']['button_text'] ?? 'Subscribe') ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="placeholder" class="form-label">Email Placeholder</label>
                            <input type="text" id="placeholder" name="placeholder"
                                   value="<?= e($section['config']['placeholder'] ?? 'Enter your email') ?>" class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <?php elseif ($section['type'] === 'custom_html'): ?>
            <!-- Custom HTML Config -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Custom Code</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label for="custom_html" class="form-label">HTML Content</label>
                        <textarea id="custom_html" name="custom_html" rows="10" class="form-input font-mono text-sm"><?= e($section['config']['html'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="custom_css" class="form-label">Custom CSS (Optional)</label>
                        <textarea id="custom_css" name="custom_css" rows="6" class="form-input font-mono text-sm"><?= e($section['config']['css'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <?php elseif ($section['type'] === 'hero' || $section['type'] === 'banner'): ?>
            <!-- Hero/Banner Info -->
            <div class="admin-card">
                <div class="card-body">
                    <div class="flex items-start gap-4 p-4 bg-blue-50 rounded-lg">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" class="text-blue-500 flex-shrink-0">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div>
                            <h4 class="font-semibold mb-1">Configure Banners</h4>
                            <p class="text-sm text-muted mb-2">
                                This section displays banners from the Banner Manager.
                                <?php if ($section['type'] === 'hero'): ?>
                                Banners with location "hero" will be shown in the hero slider.
                                <?php else: ?>
                                Banners with location "homepage_middle" will be shown here.
                                <?php endif; ?>
                            </p>
                            <a href="<?= url('/admin/banners') ?>" class="admin-btn admin-btn-primary admin-btn-sm">
                                Manage Banners
                            </a>
                        </div>
                    </div>
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
                                   <?= $section['is_active'] ? 'checked' : '' ?> class="form-checkbox">
                            <span>Section Active</span>
                        </label>
                        <p class="form-help">Inactive sections won't appear on the homepage</p>
                    </div>
                </div>
            </div>

            <!-- Section Info -->
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="font-semibold">Section Info</h2>
                </div>
                <div class="card-body">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-muted">Type:</dt>
                            <dd class="font-medium"><?= e($sectionInfo['name']) ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Position:</dt>
                            <dd class="font-medium">#<?= $section['sort_order'] ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Created:</dt>
                            <dd class="font-medium"><?= date('M j, Y', strtotime($section['created_at'])) ?></dd>
                        </div>
                    </dl>
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
                    Save Changes
                </button>

                <button type="button" onclick="deleteSection()"
                        class="admin-btn admin-btn-danger w-full">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" class="mr-2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                    </svg>
                    Delete Section
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Delete Modal -->
<div class="admin-modal-overlay" id="deleteModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Delete Section</h3>
            <button class="admin-modal-close" onclick="closeDeleteModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete this section? This action cannot be undone.</p>
        </div>
        <div class="admin-modal-footer">
            <button class="admin-btn admin-btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form action="<?= url('/admin/homepage/' . $section['id']) ?>" method="POST" style="display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="admin-btn admin-btn-danger">Delete Section</button>
            </form>
        </div>
    </div>
</div>

<style>
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: var(--space-2);
    margin-top: var(--space-2);
}

.category-checkbox {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2);
    border: 1px solid var(--color-border-light);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: var(--transition-colors);
}

.category-checkbox:hover {
    border-color: var(--color-primary);
}

.category-checkbox input:checked + span {
    color: var(--color-primary);
    font-weight: var(--font-medium);
}
</style>

<script>
function deleteSection() {
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

function addBadge() {
    const list = document.getElementById('badgesList');
    const item = document.createElement('div');
    item.className = 'badge-item mb-4 p-4 bg-neutral-50 rounded-lg';
    item.innerHTML = `
        <div class="grid md:grid-cols-3 gap-4">
            <div class="form-group">
                <label class="form-label">Icon</label>
                <select name="badge_icons[]" class="form-select">
                    <option value="truck">Truck</option>
                    <option value="shield">Shield</option>
                    <option value="refresh-cw">Refresh</option>
                    <option value="headphones">Headphones</option>
                    <option value="award">Award</option>
                    <option value="heart">Heart</option>
                    <option value="check-circle">Check Circle</option>
                    <option value="star">Star</option>
                    <option value="lock">Lock</option>
                    <option value="clock">Clock</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="badge_titles[]" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <input type="text" name="badge_descriptions[]" class="form-input">
            </div>
        </div>
    `;
    list.appendChild(item);
}

function addTestimonial() {
    const list = document.getElementById('testimonialsList');
    const item = document.createElement('div');
    item.className = 'testimonial-item mb-4 p-4 bg-neutral-50 rounded-lg';
    item.innerHTML = `
        <div class="grid md:grid-cols-2 gap-4 mb-3">
            <div class="form-group">
                <label class="form-label">Customer Name</label>
                <input type="text" name="testimonial_names[]" class="form-input" placeholder="John Doe">
            </div>
            <div class="form-group">
                <label class="form-label">Title/Location</label>
                <input type="text" name="testimonial_titles[]" class="form-input" placeholder="Cape Town">
            </div>
        </div>
        <div class="grid md:grid-cols-4 gap-4">
            <div class="form-group md:col-span-3">
                <label class="form-label">Testimonial</label>
                <textarea name="testimonial_contents[]" rows="2" class="form-input"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Rating</label>
                <select name="testimonial_ratings[]" class="form-select">
                    <option value="5">5 stars</option>
                    <option value="4">4 stars</option>
                    <option value="3">3 stars</option>
                    <option value="2">2 stars</option>
                    <option value="1">1 star</option>
                </select>
            </div>
        </div>
    `;
    list.appendChild(item);
}
</script>
