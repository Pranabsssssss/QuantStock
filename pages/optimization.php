<?php
/**
 * QuantStock — Optimization Page
 */

$forecastModel = new Forecast();
$predictions = $forecastModel->getLatestPredictions('optimization', 10);
$analytics = new Analytics();
$healthScore = $analytics->getInventoryHealthScore();
?>

<div class="page-header">
    <div class="page-header-left">
        <h2>Inventory Optimization</h2>
        <p>AI-powered recommendations for optimal stock levels</p>
    </div>
    <div class="page-header-right">
        <button class="btn btn-primary" id="runOptimizationBtn" onclick="runOptimization()">
            <i data-lucide="brain-circuit"></i>
            <span>Analyze Inventory</span>
        </button>
    </div>
</div>

<!-- Health Score -->
<div class="stats-grid" style="margin-bottom:1.5rem;">
    <div class="stat-card card-<?= $healthScore >= 80 ? 'green' : ($healthScore >= 50 ? 'orange' : 'red') ?>">
        <div class="stat-card-body" style="text-align:center;">
            <div class="health-score">
                <div class="health-ring">
                    <svg viewBox="0 0 120 120">
                        <circle class="ring-bg" cx="60" cy="60" r="52"></circle>
                        <circle class="ring-fill" cx="60" cy="60" r="52" 
                                stroke-dasharray="<?= 2 * M_PI * 52 ?>" 
                                stroke-dashoffset="<?= 2 * M_PI * 52 * (1 - $healthScore / 100) ?>"
                                style="stroke: var(--<?= $healthScore >= 80 ? 'success' : ($healthScore >= 50 ? 'warning' : 'danger') ?>)"></circle>
                    </svg>
                    <div class="health-ring-value">
                        <div class="health-ring-number"><?= $healthScore ?>%</div>
                        <div class="health-ring-label"><?= $healthScore >= 80 ? 'Excellent' : ($healthScore >= 50 ? 'Fair' : 'Poor') ?></div>
                    </div>
                </div>
            </div>
            <p class="stat-label" style="margin-top:0.75rem;">Inventory Health Score</p>
        </div>
    </div>

    <?php
    $productModel = new Product();
    renderCard(['title' => 'Total Value', 'value' => formatCompact($productModel->getTotalValue()), 'icon' => 'indian-rupee', 'color' => 'blue', 'subtitle' => 'Current inventory value']);
    renderCard(['title' => 'Cost Value', 'value' => formatCompact($productModel->getCostValue()), 'icon' => 'wallet', 'color' => 'purple', 'subtitle' => 'Total cost of goods']);
    renderCard(['title' => 'Low Stock', 'value' => formatNumber($productModel->getLowStockCount()), 'icon' => 'alert-triangle', 'color' => 'red', 'subtitle' => 'Items below minimum']);
    ?>
</div>

<?php if (empty($predictions)): ?>
    <div class="card">
        <?php renderEmptyState([
            'icon' => 'settings-2',
            'title' => 'Inventory Analysis Not Available Yet',
            'description' => 'Click "Analyze Inventory" to get AI-powered optimization recommendations for your stock levels.',
            'action' => 'Analyze Inventory',
            'action_id' => 'emptyOptBtn',
        ]); ?>
    </div>
<?php else: ?>
    <?php foreach ($predictions as $pred):
        $data = json_decode($pred['data_json'], true);
    ?>
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header">
            <div>
                <h3 class="card-title"><?= e($pred['title']) ?></h3>
                <span class="card-subtitle">Generated <?= timeAgo($pred['created_at']) ?></span>
            </div>
            <div class="prediction-actions" style="border:none; padding:0; margin:0;">
                <button class="btn btn-sm btn-secondary" onclick="dismissPrediction(<?= $pred['id'] ?>)">Dismiss</button>
            </div>
        </div>
        <p style="color:var(--text-secondary); font-size:0.875rem; margin-bottom:1rem;"><?= e($pred['summary']) ?></p>

        <?php if (!empty($data['recommendations'])): ?>
            <h4 style="font-size:0.85rem; font-weight:600; margin-bottom:0.75rem; color:var(--text-primary);">Recommendations</h4>
            <div class="prediction-cards">
                <?php foreach (array_slice($data['recommendations'], 0, 6) as $rec): ?>
                    <div class="prediction-card">
                        <div class="prediction-card-header">
                            <span class="prediction-type" style="color: var(--<?= ($rec['priority'] ?? '') === 'critical' ? 'danger' : (($rec['priority'] ?? '') === 'high' ? 'warning' : 'accent') ?>);">
                                <i data-lucide="<?= ($rec['type'] ?? '') === 'reorder' ? 'shopping-bag' : (($rec['type'] ?? '') === 'overstock' ? 'archive' : 'alert-triangle') ?>"></i>
                                <?= ucfirst($rec['type'] ?? 'recommendation') ?>
                            </span>
                            <span class="stock-badge stock-badge-<?= ($rec['priority'] ?? '') === 'critical' ? 'danger' : (($rec['priority'] ?? '') === 'high' ? 'warning' : 'success') ?>">
                                <?= ucfirst($rec['priority'] ?? 'medium') ?>
                            </span>
                        </div>
                        <h4><?= e($rec['product_name'] ?? 'N/A') ?></h4>
                        <p><?= e($rec['reason'] ?? '') ?></p>
                        <?php if (isset($rec['suggested_order_qty'])): ?>
                            <div style="margin-top:0.5rem; display:flex; gap:1rem;">
                                <div><span style="font-size:0.7rem; color:var(--text-tertiary);">Order Qty</span><br><strong><?= (int)$rec['suggested_order_qty'] ?></strong></div>
                                <div><span style="font-size:0.7rem; color:var(--text-tertiary);">Current</span><br><strong><?= (int)($rec['current_stock'] ?? 0) ?></strong></div>
                                <div><span style="font-size:0.7rem; color:var(--text-tertiary);">Optimal</span><br><strong><?= (int)($rec['optimal_stock'] ?? 0) ?></strong></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['business_risks'])): ?>
            <h4 style="font-size:0.85rem; font-weight:600; margin: 1.25rem 0 0.75rem; color:var(--text-primary);">Business Risks</h4>
            <?php foreach ($data['business_risks'] as $risk): ?>
                <div style="padding:0.75rem 1rem; background:var(--<?= ($risk['severity'] ?? '') === 'critical' ? 'danger' : (($risk['severity'] ?? '') === 'high' ? 'warning' : 'accent') ?>-light); border-radius:10px; margin-bottom:0.5rem;">
                    <div style="font-size:0.85rem; font-weight:600; color:var(--text-primary);"><?= e($risk['risk'] ?? '') ?></div>
                    <div style="font-size:0.8rem; color:var(--text-secondary); margin-top:0.25rem;"><?= e($risk['mitigation'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Loading overlay -->
<div id="optimizationLoading" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:250; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="text-align:center; background:var(--bg-secondary); padding:2rem 3rem; border-radius:16px; border:1px solid var(--border-primary);">
        <div class="loading-spinner" style="margin:0 auto 1rem; width:32px; height:32px;"></div>
        <p style="font-weight:600; color:var(--text-primary);">Optimizing your inventory...</p>
        <p style="font-size:0.8rem; color:var(--text-tertiary); margin-top:0.25rem;">AI is analyzing stock levels and trends</p>
    </div>
</div>
