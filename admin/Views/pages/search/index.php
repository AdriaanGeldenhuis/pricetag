<!-- Admin Search Analytics -->
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Search Analytics</h1>
        <p class="admin-page-subtitle">Understand what customers are searching for</p>
    </div>
</div>

<!-- Stats -->
<div class="admin-stats-grid mb-6">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Searches</div>
        <div class="admin-stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Unique Terms</div>
        <div class="admin-stat-value"><?= number_format($stats['unique'] ?? 0) ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Zero Results</div>
        <div class="admin-stat-value text-warning"><?= number_format($stats['zero_results'] ?? 0) ?></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Searches Today</div>
        <div class="admin-stat-value"><?= number_format($stats['today'] ?? 0) ?></div>
    </div>
</div>

<div class="admin-grid admin-grid-2">
    <!-- Top Search Terms -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Top Search Terms</h2>
        </div>
        <div class="admin-card-body p-0">
            <?php if (empty($topSearches)): ?>
            <div class="admin-empty py-8">
                <p class="admin-empty-text">No search data yet.</p>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Search Term</th>
                        <th style="width: 80px;">Count</th>
                        <th style="width: 80px;">Results</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topSearches as $search): ?>
                    <tr>
                        <td>
                            <span class="font-medium"><?= e($search['term']) ?></span>
                        </td>
                        <td class="text-muted"><?= number_format($search['count']) ?></td>
                        <td>
                            <?php if ($search['results'] == 0): ?>
                            <span class="text-danger">0</span>
                            <?php else: ?>
                            <span class="text-success"><?= $search['results'] ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Zero Result Searches -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Zero Result Searches</h2>
            <span class="admin-badge warning">Needs Attention</span>
        </div>
        <div class="admin-card-body p-0">
            <?php if (empty($zeroResults)): ?>
            <div class="admin-empty py-8">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="40" height="40" class="admin-empty-icon">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <p class="admin-empty-text">Great! No zero-result searches.</p>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Search Term</th>
                        <th style="width: 80px;">Count</th>
                        <th style="width: 100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($zeroResults as $search): ?>
                    <tr>
                        <td>
                            <span class="font-medium"><?= e($search['term']) ?></span>
                        </td>
                        <td class="text-muted"><?= number_format($search['count']) ?></td>
                        <td>
                            <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm" onclick="showSynonymModal('<?= e($search['term']) ?>')">
                                Add Synonym
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Search Synonyms -->
<div class="admin-card mt-6">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Search Synonyms</h2>
        <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick="showSynonymModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Synonym
        </button>
    </div>
    <div class="admin-card-body">
        <p class="text-muted mb-4">Map search terms to similar words so customers find what they're looking for.</p>

        <?php if (empty($synonyms)): ?>
        <div class="admin-empty py-6">
            <p class="admin-empty-text">No synonyms configured yet.</p>
        </div>
        <?php else: ?>
        <div class="admin-synonym-list">
            <?php foreach ($synonyms as $synonym): ?>
            <div class="admin-synonym-item">
                <div class="admin-synonym-terms">
                    <span class="admin-synonym-from"><?= e($synonym['term']) ?></span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                    <span class="admin-synonym-to"><?= e($synonym['synonyms']) ?></span>
                </div>
                <form method="POST" action="<?= url('/admin/search/synonyms/' . $synonym['id']) ?>" style="display: inline;" onsubmit="return confirm('Delete this synonym?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="admin-btn admin-btn-ghost admin-btn-sm admin-btn-icon text-danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Synonym Modal -->
<div id="synonymModal" class="admin-modal" style="display: none;">
    <div class="admin-modal-backdrop" onclick="closeSynonymModal()"></div>
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add Search Synonym</h3>
            <button type="button" class="admin-modal-close" onclick="closeSynonymModal()">&times;</button>
        </div>
        <form method="POST" action="<?= url('/admin/search/synonyms') ?>">
            <?= csrf_field() ?>
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label class="admin-label">Search Term</label>
                    <input type="text" name="term" id="synonymTerm" class="admin-input" placeholder="e.g., sofa" required>
                    <p class="admin-help">The term customers search for</p>
                </div>
                <div class="admin-form-group">
                    <label class="admin-label">Synonyms</label>
                    <input type="text" name="synonyms" class="admin-input" placeholder="e.g., couch, settee, loveseat" required>
                    <p class="admin-help">Comma-separated list of words to also match</p>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-ghost" onclick="closeSynonymModal()">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary">Save Synonym</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-synonym-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.admin-synonym-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--admin-bg-secondary);
    border-radius: 8px;
}
.admin-synonym-terms {
    display: flex;
    align-items: center;
    gap: 12px;
}
.admin-synonym-from {
    font-weight: 600;
    color: var(--admin-text-primary);
}
.admin-synonym-to {
    color: var(--admin-text-muted);
}
</style>

<script>
function showSynonymModal(term = '') {
    document.getElementById('synonymTerm').value = term;
    document.getElementById('synonymModal').style.display = 'flex';
}

function closeSynonymModal() {
    document.getElementById('synonymModal').style.display = 'none';
}
</script>
