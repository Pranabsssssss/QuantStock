<?php
/**
 * QuantStock — Empty State Component
 * 
 * Usage:
 * renderEmptyState([
 *     'icon'        => 'package',
 *     'title'       => 'No Products Found',
 *     'description' => 'Start by adding your first product.',
 *     'action'      => 'Add Product',
 *     'action_id'   => 'addProductBtn',
 * ]);
 */

function renderEmptyState(array $config): void {
    $icon        = $config['icon'] ?? 'inbox';
    $title       = e($config['title'] ?? 'No Data Available');
    $description = e($config['description'] ?? 'There is nothing to display yet.');
    $action      = $config['action'] ?? '';
    $actionId    = $config['action_id'] ?? '';
    $actionUrl   = $config['action_url'] ?? '#';
    ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i data-lucide="<?= $icon ?>"></i>
        </div>
        <h3 class="empty-state-title"><?= $title ?></h3>
        <p class="empty-state-desc"><?= $description ?></p>
        <?php if ($action): ?>
            <button class="btn btn-primary" id="<?= e($actionId) ?>" <?php if ($actionUrl !== '#'): ?>onclick="window.location='<?= e($actionUrl) ?>'"<?php endif; ?>>
                <i data-lucide="plus"></i>
                <span><?= e($action) ?></span>
            </button>
        <?php endif; ?>
    </div>
    <?php
}
