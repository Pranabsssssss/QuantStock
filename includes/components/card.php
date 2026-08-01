<?php
/**
 * QuantStock — Stat Card Component
 * 
 * Usage:
 * renderCard([
 *     'title'     => 'Total Products',
 *     'value'     => '1,250',
 *     'icon'      => 'package',
 *     'trend'     => ['value' => 12.5, 'direction' => 'up'],
 *     'subtitle'  => 'from last month',
 *     'color'     => 'blue',  // blue, green, orange, red, purple
 * ]);
 */

function renderCard(array $config): void {
    $title     = e($config['title'] ?? '');
    $value     = $config['value'] ?? '0';
    $icon      = $config['icon'] ?? 'activity';
    $trend     = $config['trend'] ?? null;
    $subtitle  = e($config['subtitle'] ?? '');
    $color     = $config['color'] ?? 'blue';
    $link      = $config['link'] ?? '';
    $linkText  = $config['link_text'] ?? '';
    ?>
    <div class="stat-card card-<?= $color ?>">
        <div class="stat-card-header">
            <div class="stat-icon stat-icon-<?= $color ?>">
                <i data-lucide="<?= $icon ?>"></i>
            </div>
            <?php if ($trend): ?>
                <div class="stat-trend stat-trend-<?= $trend['direction'] ?? 'stable' ?>">
                    <?php if (($trend['direction'] ?? '') === 'up'): ?>
                        <i data-lucide="trending-up"></i>
                    <?php elseif (($trend['direction'] ?? '') === 'down'): ?>
                        <i data-lucide="trending-down"></i>
                    <?php else: ?>
                        <i data-lucide="minus"></i>
                    <?php endif; ?>
                    <span><?= $trend['value'] ?? 0 ?>%</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="stat-card-body">
            <p class="stat-label"><?= $title ?></p>
            <h3 class="stat-value"><?= $value ?></h3>
            <?php if ($subtitle): ?>
                <p class="stat-subtitle"><?= $subtitle ?></p>
            <?php endif; ?>
        </div>
        <?php if ($link): ?>
            <div class="stat-card-footer">
                <a href="<?= e($link) ?>" class="stat-link"><?= e($linkText) ?> <i data-lucide="arrow-right"></i></a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
