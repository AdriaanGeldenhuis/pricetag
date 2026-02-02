<!-- Admin SEO Settings -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($title) ?></h1>
        <p class="admin-page-subtitle">Manage your site's SEO and analytics</p>
    </div>
    <div class="admin-page-actions">
        <form method="POST" action="<?= url('/admin/seo/sitemap') ?>" style="display: inline;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="admin-btn admin-btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                Regenerate Sitemap
            </button>
        </form>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Main Content -->
    <div>
        <form method="POST" action="<?= url('/admin/seo') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Meta Tags -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Default Meta Tags</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Site Title</label>
                        <input type="text" name="site_title" value="<?= e($settings['site_title']) ?>"
                               class="admin-form-input" placeholder="Your Store Name">
                        <p class="admin-form-hint">Used as the default title suffix (e.g., "Page | Your Store Name")</p>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Site Description</label>
                        <textarea name="site_description" class="admin-form-textarea" rows="3"
                                  maxlength="160" placeholder="Brief description of your store..."><?= e($settings['site_description']) ?></textarea>
                        <p class="admin-form-hint">
                            Default meta description (max 160 characters).
                            <span id="descCharCount"><?= strlen($settings['site_description']) ?></span>/160
                        </p>
                    </div>

                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Keywords</label>
                        <input type="text" name="site_keywords" value="<?= e($settings['site_keywords']) ?>"
                               class="admin-form-input" placeholder="online store, e-commerce, South Africa">
                        <p class="admin-form-hint">Comma-separated keywords (less important for modern SEO)</p>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Social Media</h3>
                </div>
                <div class="admin-card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Twitter Handle</label>
                            <input type="text" name="twitter_handle" value="<?= e($settings['twitter_handle']) ?>"
                                   class="admin-form-input" placeholder="@yourstorename">
                            <p class="admin-form-hint">For Twitter Cards attribution</p>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Facebook App ID</label>
                            <input type="text" name="facebook_app_id" value="<?= e($settings['facebook_app_id']) ?>"
                                   class="admin-form-input" placeholder="123456789">
                            <p class="admin-form-hint">For Facebook Open Graph insights</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Search Engine Verification</h3>
                </div>
                <div class="admin-card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="admin-form-group">
                            <label class="admin-form-label">Google Search Console</label>
                            <input type="text" name="google_verification" value="<?= e($settings['google_verification']) ?>"
                                   class="admin-form-input" placeholder="Verification code">
                            <p class="admin-form-hint">Content from google-site-verification meta tag</p>
                        </div>
                        <div class="admin-form-group">
                            <label class="admin-form-label">Bing Webmaster</label>
                            <input type="text" name="bing_verification" value="<?= e($settings['bing_verification']) ?>"
                                   class="admin-form-input" placeholder="Verification code">
                            <p class="admin-form-hint">Content from msvalidate.01 meta tag</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Analytics & Tracking</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Google Analytics ID</label>
                        <input type="text" name="google_analytics" value="<?= e($settings['google_analytics']) ?>"
                               class="admin-form-input" placeholder="G-XXXXXXXXXX or UA-XXXXXXXXX-X">
                        <p class="admin-form-hint">GA4 or Universal Analytics Measurement ID</p>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label">Google Tag Manager ID</label>
                        <input type="text" name="gtm_id" value="<?= e($settings['gtm_id']) ?>"
                               class="admin-form-input" placeholder="GTM-XXXXXXX">
                        <p class="admin-form-hint">For managing all tracking tags centrally</p>
                    </div>

                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Facebook Pixel ID</label>
                        <input type="text" name="facebook_pixel" value="<?= e($settings['facebook_pixel']) ?>"
                               class="admin-form-input" placeholder="123456789">
                        <p class="admin-form-hint">For Facebook Ads conversion tracking</p>
                    </div>
                </div>
            </div>

            <!-- Robots.txt -->
            <div class="admin-card" style="margin-bottom: 1.5rem;">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Robots.txt Configuration</h3>
                </div>
                <div class="admin-card-body">
                    <div class="admin-form-group">
                        <label class="admin-form-label">Allow Paths</label>
                        <input type="text" name="robots_allow" value="<?= e($settings['robots_allow']) ?>"
                               class="admin-form-input" placeholder="/,/products/,/categories/">
                        <p class="admin-form-hint">Comma-separated paths to allow crawling</p>
                    </div>

                    <div class="admin-form-group" style="margin-bottom: 0;">
                        <label class="admin-form-label">Disallow Paths</label>
                        <input type="text" name="robots_disallow" value="<?= e($settings['robots_disallow']) ?>"
                               class="admin-form-input" placeholder="/admin/,/cart/,/checkout/">
                        <p class="admin-form-hint">Comma-separated paths to block from crawling</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Save SEO Settings
            </button>
        </form>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Sitemap Status -->
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Sitemap Status</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--admin-success);"></div>
                    <span>Sitemap is active</span>
                </div>

                <div style="display: grid; gap: 0.5rem; font-size: 0.875rem;">
                    <div style="display: flex; justify-content: space-between;">
                        <span class="text-muted">Products:</span>
                        <span class="font-semibold"><?= number_format($sitemapStats['products']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span class="text-muted">Categories:</span>
                        <span class="font-semibold"><?= number_format($sitemapStats['categories']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span class="text-muted">Pages:</span>
                        <span class="font-semibold"><?= number_format($sitemapStats['pages']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 0.5rem; border-top: 1px solid var(--admin-border);">
                        <span class="text-muted">Total URLs:</span>
                        <span class="font-semibold"><?= number_format($sitemapStats['products'] + $sitemapStats['categories'] + $sitemapStats['pages'] + 1) ?></span>
                    </div>
                </div>

                <a href="<?= url('/sitemap.xml') ?>" target="_blank" class="admin-btn admin-btn-secondary admin-btn-sm" style="width: 100%; margin-top: 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    View Sitemap
                </a>
            </div>
        </div>

        <!-- SEO Health -->
        <div class="admin-card" style="margin-bottom: 1.5rem;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">SEO Health</h3>
            </div>
            <div class="admin-card-body">
                <?php $productsCount = count($productsWithoutSeo); ?>
                <?php $pagesCount = count($pagesWithoutMeta); ?>

                <?php if ($productsCount === 0 && $pagesCount === 0): ?>
                <div style="text-align: center; padding: 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; color: var(--admin-success); margin: 0 auto 0.5rem;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <p class="font-semibold" style="color: var(--admin-success);">All content is optimized!</p>
                </div>
                <?php else: ?>
                <div style="display: grid; gap: 0.75rem;">
                    <?php if ($productsCount > 0): ?>
                    <div style="padding: 0.75rem; background: #fef3c7; border-radius: var(--admin-radius);">
                        <div class="font-semibold" style="color: #92400e; margin-bottom: 0.25rem;">
                            <?= $productsCount ?> Products Missing Descriptions
                        </div>
                        <p class="text-muted" style="font-size: 0.75rem; margin: 0;">
                            Add short descriptions for better SEO
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($pagesCount > 0): ?>
                    <div style="padding: 0.75rem; background: #fef3c7; border-radius: var(--admin-radius);">
                        <div class="font-semibold" style="color: #92400e; margin-bottom: 0.25rem;">
                            <?= $pagesCount ?> Pages Missing Meta
                        </div>
                        <p class="text-muted" style="font-size: 0.75rem; margin: 0;">
                            Add meta descriptions to pages
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Quick Links</h3>
            </div>
            <div class="admin-card-body">
                <div style="display: grid; gap: 0.5rem;">
                    <a href="<?= url('/robots.txt') ?>" target="_blank" class="admin-dropdown-item" style="padding: 0.5rem; border-radius: var(--admin-radius);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                        robots.txt
                    </a>
                    <a href="<?= url('/sitemap.xml') ?>" target="_blank" class="admin-dropdown-item" style="padding: 0.5rem; border-radius: var(--admin-radius);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                        sitemap.xml
                    </a>
                    <a href="https://search.google.com/search-console" target="_blank" class="admin-dropdown-item" style="padding: 0.5rem; border-radius: var(--admin-radius);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        Google Search Console
                    </a>
                    <a href="https://analytics.google.com" target="_blank" class="admin-dropdown-item" style="padding: 0.5rem; border-radius: var(--admin-radius);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        Google Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Products Missing Descriptions -->
<?php if (!empty($productsWithoutSeo)): ?>
<div class="admin-card" style="margin-top: 1.5rem;">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Products Missing Descriptions</h3>
        <a href="<?= url('/admin/products') ?>" class="admin-btn admin-btn-ghost admin-btn-sm">View All</a>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>URL</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productsWithoutSeo as $product): ?>
                <tr>
                    <td class="font-semibold"><?= e($product['name']) ?></td>
                    <td class="text-muted">/products/<?= e($product['slug']) ?></td>
                    <td>
                        <a href="<?= url('/admin/products/' . $product['id'] . '/edit') ?>" class="admin-btn admin-btn-sm admin-btn-primary">
                            Add Description
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
document.querySelector('textarea[name="site_description"]')?.addEventListener('input', function() {
    document.getElementById('descCharCount').textContent = this.value.length;
});
</script>
