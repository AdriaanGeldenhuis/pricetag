<?php
/**
 * Testimonials Section
 * Displays customer testimonials with star ratings
 *
 * Available: $sectionConfig, $sectionTitle
 */

$items = $sectionConfig['testimonials'] ?? $testimonials ?? [];
$title = $sectionTitle ?? 'What Our Customers Say';

if (empty($items)) return;
?>

<section class="testimonials-section">
    <div class="container">
        <div class="testimonials-header">
            <span class="section-badge badge-accent">Testimonials</span>
            <h2 class="testimonials-title"><?= e($title) ?></h2>
            <p class="testimonials-subtitle">Real feedback from our valued customers</p>
        </div>

        <div class="testimonials-grid">
            <?php foreach ($items as $item): ?>
            <?php if (empty($item['name']) && empty($item['content'])) continue; ?>
            <div class="testimonial-card">
                <div class="testimonial-stars">
                    <?php $rating = (int) ($item['rating'] ?? 5); ?>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <svg class="testimonial-star <?= $i <= $rating ? 'star-filled' : 'star-empty' ?>" width="18" height="18" viewBox="0 0 24 24" fill="<?= $i <= $rating ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <?php endfor; ?>
                </div>
                <blockquote class="testimonial-content">
                    <?= e($item['content'] ?? '') ?>
                </blockquote>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">
                        <?= e(mb_strtoupper(mb_substr($item['name'] ?? '?', 0, 1))) ?>
                    </div>
                    <div class="testimonial-author-info">
                        <span class="testimonial-name"><?= e($item['name'] ?? 'Customer') ?></span>
                        <?php if (!empty($item['title'])): ?>
                        <span class="testimonial-location"><?= e($item['title']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.testimonials-section {
    padding: var(--space-16) 0;
    background: var(--color-background);
    position: relative;
    overflow: hidden;
}

.testimonials-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(ellipse at 20% 50%, rgba(139, 43, 43, 0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 50%, rgba(139, 43, 43, 0.04) 0%, transparent 60%);
    pointer-events: none;
}

.testimonials-header {
    text-align: center;
    margin-bottom: var(--space-12);
    position: relative;
    z-index: 1;
}

.testimonials-title {
    font-size: clamp(1.5rem, 3vw, var(--text-3xl));
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: var(--space-3) 0 var(--space-2);
    letter-spacing: var(--tracking-tight);
}

.testimonials-subtitle {
    font-size: var(--text-base);
    color: var(--color-text-muted);
    margin: 0;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
    position: relative;
    z-index: 1;
}

@media (min-width: 640px) {
    .testimonials-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .testimonials-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.testimonial-card {
    background: var(--color-background-secondary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

.testimonial-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
    border-color: rgba(139, 43, 43, 0.3);
}

.testimonial-stars {
    display: flex;
    gap: 2px;
}

.testimonial-star.star-filled {
    color: #f59e0b;
}

.testimonial-star.star-empty {
    color: var(--color-border);
}

.testimonial-content {
    font-size: var(--text-base);
    line-height: 1.7;
    color: var(--color-text-secondary);
    margin: 0;
    flex: 1;
    position: relative;
    padding-left: var(--space-4);
    border-left: 3px solid var(--color-primary);
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding-top: var(--space-3);
    border-top: 1px solid var(--color-border);
}

.testimonial-avatar {
    width: 2.75rem;
    height: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white;
    font-weight: var(--font-bold);
    font-size: var(--text-base);
    flex-shrink: 0;
}

.testimonial-author-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.testimonial-name {
    font-weight: var(--font-semibold);
    color: var(--color-text);
    font-size: var(--text-sm);
}

.testimonial-location {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
}
</style>
