<!-- CMS Page -->

<!-- Breadcrumbs -->
<nav class="breadcrumbs container">
    <div class="breadcrumb-item"><a href="<?= url('/') ?>" class="breadcrumb-link">Home</a></div>
    <div class="breadcrumb-item"><span class="breadcrumb-current"><?= e($page['title']) ?></span></div>
</nav>

<div class="container py-8">
    <div class="cms-page">
        <h1 class="cms-page-title"><?= e($page['title']) ?></h1>

        <?php if (!empty($page['excerpt'])): ?>
        <p class="cms-page-excerpt"><?= e($page['excerpt']) ?></p>
        <?php endif; ?>

        <div class="cms-page-content">
            <?= $page['content'] ?>
        </div>

        <?php if (!empty($page['updated_at'])): ?>
        <div class="cms-page-updated">
            Last updated: <?= date('F j, Y', strtotime($page['updated_at'])) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.cms-page {
    max-width: 860px;
    margin: 0 auto;
}

.cms-page-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin-bottom: var(--space-3);
    letter-spacing: -0.02em;
}

.cms-page-excerpt {
    font-size: var(--text-lg);
    color: var(--color-text-muted);
    margin-bottom: var(--space-8);
    line-height: 1.6;
}

.cms-page-content {
    color: var(--color-text-secondary);
    line-height: 1.75;
    font-size: var(--text-base);
}

/* Typography */
.cms-page-content h2 {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--color-text);
    margin: var(--space-10) 0 var(--space-4);
    padding-bottom: var(--space-3);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.cms-page-content h2:first-child {
    margin-top: 0;
}

.cms-page-content h3 {
    font-size: var(--text-xl);
    font-weight: var(--font-semibold);
    color: var(--color-text);
    margin: var(--space-8) 0 var(--space-3);
}

.cms-page-content p {
    margin-bottom: var(--space-4);
}

.cms-page-content a {
    color: var(--color-primary);
    text-decoration: underline;
    text-underline-offset: 2px;
}

.cms-page-content a:hover {
    color: var(--color-accent);
}

.cms-page-content ul,
.cms-page-content ol {
    margin: var(--space-4) 0;
    padding-left: var(--space-6);
}

.cms-page-content li {
    margin-bottom: var(--space-2);
}

.cms-page-content ul li {
    list-style: disc;
}

.cms-page-content ol li {
    list-style: decimal;
}

.cms-page-content strong {
    color: var(--color-text);
    font-weight: var(--font-semibold);
}

.cms-page-content blockquote {
    border-left: 3px solid var(--color-primary);
    padding: var(--space-4) var(--space-5);
    margin: var(--space-6) 0;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
    color: var(--color-text-muted);
    font-style: italic;
}

.cms-page-content table {
    width: 100%;
    border-collapse: collapse;
    margin: var(--space-6) 0;
    font-size: var(--text-sm);
}

.cms-page-content table th,
.cms-page-content table td {
    padding: var(--space-3) var(--space-4);
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.cms-page-content table th {
    color: var(--color-text);
    font-weight: var(--font-semibold);
    background: rgba(255, 255, 255, 0.03);
}

.cms-page-content table tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

/* Info boxes */
.cms-page-content .info-box {
    padding: var(--space-5);
    border-radius: var(--radius-xl);
    margin: var(--space-6) 0;
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(255, 255, 255, 0.02);
}

.cms-page-content .info-box.highlight {
    border-color: rgba(139, 43, 43, 0.3);
    background: rgba(139, 43, 43, 0.05);
}

/* FAQ Accordion */
.cms-page-content .faq-item {
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-3);
    overflow: hidden;
    transition: border-color 0.2s ease;
}

.cms-page-content .faq-item:hover {
    border-color: rgba(255, 255, 255, 0.12);
}

.cms-page-content .faq-question {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: var(--space-4) var(--space-5);
    background: rgba(255, 255, 255, 0.02);
    border: none;
    color: var(--color-text);
    font-size: var(--text-base);
    font-weight: var(--font-semibold);
    text-align: left;
    cursor: pointer;
    transition: background 0.2s ease;
}

.cms-page-content .faq-question:hover {
    background: rgba(255, 255, 255, 0.04);
}

.cms-page-content .faq-question::after {
    content: '+';
    font-size: var(--text-xl);
    color: var(--color-primary);
    flex-shrink: 0;
    margin-left: var(--space-3);
    transition: transform 0.2s ease;
}

.cms-page-content .faq-item.is-open .faq-question::after {
    content: '−';
}

.cms-page-content .faq-answer {
    display: none;
    padding: 0 var(--space-5) var(--space-5);
    color: var(--color-text-secondary);
    line-height: 1.7;
}

.cms-page-content .faq-item.is-open .faq-answer {
    display: block;
}

/* Track order form */
.cms-page-content .track-form {
    display: flex;
    gap: var(--space-3);
    max-width: 500px;
    margin: var(--space-6) 0;
}

.cms-page-content .track-form input {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.04);
    color: var(--color-text);
    font-size: var(--text-sm);
}

.cms-page-content .track-form input:focus {
    outline: none;
    border-color: var(--color-primary);
}

.cms-page-content .track-form button {
    padding: var(--space-3) var(--space-5);
    border-radius: var(--radius-lg);
    border: none;
    background: var(--color-primary);
    color: #fff;
    font-weight: var(--font-semibold);
    font-size: var(--text-sm);
    cursor: pointer;
    white-space: nowrap;
    transition: opacity 0.2s ease;
}

.cms-page-content .track-form button:hover {
    opacity: 0.9;
}

/* Updated timestamp */
.cms-page-updated {
    margin-top: var(--space-10);
    padding-top: var(--space-4);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

/* Responsive */
@media (max-width: 640px) {
    .cms-page-title {
        font-size: var(--text-2xl);
    }

    .cms-page-content h2 {
        font-size: var(--text-xl);
    }

    .cms-page-content h3 {
        font-size: var(--text-lg);
    }

    .cms-page-content table {
        display: block;
        overflow-x: auto;
    }

    .cms-page-content .track-form {
        flex-direction: column;
    }
}
</style>

<script>
// FAQ Accordion
document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', () => {
        const item = btn.closest('.faq-item');
        const wasOpen = item.classList.contains('is-open');
        // Close all
        document.querySelectorAll('.faq-item.is-open').forEach(i => i.classList.remove('is-open'));
        // Toggle current
        if (!wasOpen) item.classList.add('is-open');
    });
});
</script>
