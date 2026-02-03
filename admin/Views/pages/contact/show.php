<!-- Admin Contact Message View -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Message from <?= e($message['name']) ?></h1>
        <p class="admin-page-subtitle">Received <?= date('M j, Y \a\t H:i', strtotime($message['created_at'])) ?></p>
    </div>
    <a href="<?= url('/admin/contact') ?>" class="admin-btn admin-btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Back to Messages
    </a>
</div>

<div class="admin-grid admin-grid-3-1">
    <!-- Message Content -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title"><?= e($message['subject'] ?: '(No subject)') ?></h2>
            <?php
            $badges = ['new' => 'warning', 'read' => 'neutral', 'replied' => 'active', 'archived' => 'inactive'];
            ?>
            <span class="admin-badge <?= $badges[$message['status']] ?? 'neutral' ?>"><?= ucfirst($message['status']) ?></span>
        </div>
        <div class="admin-card-body">
            <div class="admin-message-content">
                <?= nl2br(e($message['message'])) ?>
            </div>
        </div>
        <div class="admin-card-footer">
            <div class="admin-message-actions">
                <!-- Status Actions -->
                <?php if ($message['status'] !== 'archived'): ?>
                <form method="POST" action="<?= url('/admin/contact/' . $message['id'] . '/status') ?>" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="archived">
                    <button type="submit" class="admin-btn admin-btn-ghost">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <polyline points="21 8 21 21 3 21 3 8"></polyline>
                            <rect x="1" y="3" width="22" height="5"></rect>
                            <line x1="10" y1="12" x2="14" y2="12"></line>
                        </svg>
                        Archive
                    </button>
                </form>
                <?php endif; ?>

                <?php if ($message['status'] === 'archived'): ?>
                <form method="POST" action="<?= url('/admin/contact/' . $message['id'] . '/status') ?>" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="read">
                    <button type="submit" class="admin-btn admin-btn-ghost">Unarchive</button>
                </form>
                <?php endif; ?>

                <form method="POST" action="<?= url('/admin/contact/' . $message['id']) ?>" style="display: inline;" onsubmit="return confirm('Delete this message permanently?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="admin-btn admin-btn-ghost text-danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                        </svg>
                        Delete
                    </button>
                </form>
            </div>

            <!-- Reply Button -->
            <a href="mailto:<?= e($message['email']) ?>?subject=Re: <?= e($message['subject'] ?: 'Your message') ?>" class="admin-btn admin-btn-primary" onclick="markReplied()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <polyline points="9 17 4 12 9 7"></polyline>
                    <path d="M20 18v-2a4 4 0 00-4-4H4"></path>
                </svg>
                Reply via Email
            </a>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">Contact Details</h3>
            </div>
            <div class="admin-card-body">
                <div class="admin-detail-list">
                    <div class="admin-detail-item">
                        <span class="admin-detail-label">Name</span>
                        <span class="admin-detail-value"><?= e($message['name']) ?></span>
                    </div>
                    <div class="admin-detail-item">
                        <span class="admin-detail-label">Email</span>
                        <a href="mailto:<?= e($message['email']) ?>" class="admin-detail-value text-primary">
                            <?= e($message['email']) ?>
                        </a>
                    </div>
                    <?php if (!empty($message['phone'])): ?>
                    <div class="admin-detail-item">
                        <span class="admin-detail-label">Phone</span>
                        <a href="tel:<?= e($message['phone']) ?>" class="admin-detail-value">
                            <?= e($message['phone']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="admin-detail-item">
                        <span class="admin-detail-label">Received</span>
                        <span class="admin-detail-value"><?= date('M j, Y', strtotime($message['created_at'])) ?></span>
                    </div>
                    <div class="admin-detail-item">
                        <span class="admin-detail-label">Time</span>
                        <span class="admin-detail-value"><?= date('H:i:s', strtotime($message['created_at'])) ?></span>
                    </div>
                    <?php if (!empty($message['ip_address'])): ?>
                    <div class="admin-detail-item">
                        <span class="admin-detail-label">IP Address</span>
                        <span class="admin-detail-value text-muted"><?= e($message['ip_address']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($message['status'] !== 'replied'): ?>
        <div class="admin-card mt-4">
            <div class="admin-card-body">
                <form method="POST" action="<?= url('/admin/contact/' . $message['id'] . '/status') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="replied">
                    <button type="submit" class="admin-btn admin-btn-outline w-full">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Mark as Replied
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-grid-3-1 {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 24px;
}
.admin-message-content {
    font-size: 15px;
    line-height: 1.7;
    color: var(--admin-text-primary);
    white-space: pre-wrap;
}
.admin-message-actions {
    display: flex;
    gap: 8px;
}
.admin-detail-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.admin-detail-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.admin-detail-label {
    font-size: 11px;
    text-transform: uppercase;
    color: var(--admin-text-muted);
    letter-spacing: 0.5px;
}
.admin-detail-value {
    font-size: 14px;
    color: var(--admin-text-primary);
}
.w-full {
    width: 100%;
}
</style>

<script>
function markReplied() {
    // Automatically mark as replied when email is opened
    fetch('<?= url('/admin/contact/' . $message['id'] . '/status') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'status=replied&<?= csrf_field() ?>'
    });
}
</script>
