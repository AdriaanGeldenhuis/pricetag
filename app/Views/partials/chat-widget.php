<?php
/**
 * AI Chat Widget Partial
 * Include this in your main layout to enable the shopping assistant
 */

// Only show if AI is enabled
if (!config('ai.enabled', true)) {
    return;
}
?>

<!-- AI Shopping Assistant -->
<div data-chat-widget
     data-color="<?= e(config('ai.widget_color', '#4f46e5')) ?>"
     data-title="<?= e(config('ai.widget_title', 'Shopping Assistant')) ?>"
     data-position="<?= e(config('ai.widget_position', 'right')) ?>">
</div>
<script src="<?= asset('assets/js/chat-widget.js') ?>" defer></script>
