<?php
/**
 * QuantStock — Forecast Page
 */

$forecastModel = new Forecast();
$predictions = $forecastModel->getLatestPredictions('forecast', 10);
$productForecasts = $forecastModel->getProductForecasts(0, 20);
?>

<div class="page-header">
    <div class="page-header-left">
        <h2>Demand Forecast</h2>
        <p>AI-powered demand prediction for your inventory</p>
    </div>
    <div class="page-header-right">
        <button class="btn btn-primary" id="runForecastBtn" onclick="runForecast()">
            <i data-lucide="brain-circuit"></i>
            <span>Generate Forecast</span>
        </button>
    </div>
</div>

<?php if (empty($predictions) && empty($productForecasts)): ?>
    <div class="card" style="margin-bottom:1.5rem;">
        <?php renderEmptyState([
            'icon' => 'trending-up',
            'title' => 'No Forecasts Generated Yet',
            'description' => 'Click "Generate Forecast" to analyze your sales data and predict future demand using AI.',
            'action' => 'Generate Forecast',
            'action_id' => 'emptyForecastBtn',
        ]); ?>
    </div>
<?php else: ?>
    <!-- Forecast Summary -->
    <?php foreach ($predictions as $pred): 
        $data = json_decode($pred['data_json'], true);
    ?>
    <div class="card" style="margin-bottom:1rem;">
        <div class="card-header">
            <div>
                <h3 class="card-title"><?= e($pred['title']) ?></h3>
                <span class="card-subtitle">Generated <?= timeAgo($pred['created_at']) ?></span>
            </div>
            <span class="stock-badge stock-badge-<?= $pred['priority'] === 'high' ? 'danger' : ($pred['priority'] === 'medium' ? 'warning' : 'success') ?>">
                <?= ucfirst($pred['priority']) ?> Priority
            </span>
        </div>
        <p style="color:var(--text-secondary); font-size:0.875rem; line-height:1.6; margin-bottom:1rem;"><?= e($pred['summary']) ?></p>
        
        <?php if (!empty($data['products'])): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current Stock</th>
                            <th>7-Day Demand</th>
                            <th>30-Day Demand</th>
                            <th>90-Day Demand</th>
                            <th>Trend</th>
                            <th>Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($data['products'] ?? [], 0, 10) as $fp): ?>
                            <tr>
                                <td style="font-weight:600;"><?= e($fp['product_name'] ?? 'N/A') ?></td>
                                <td><?= (int)($fp['current_stock'] ?? 0) ?></td>
                                <td><?= (int)($fp['predicted_demand_7d'] ?? 0) ?></td>
                                <td><?= (int)($fp['predicted_demand_30d'] ?? 0) ?></td>
                                <td><?= (int)($fp['predicted_demand_90d'] ?? 0) ?></td>
                                <td>
                                    <span class="stock-badge stock-badge-<?= ($fp['trend'] ?? '') === 'increasing' ? 'success' : (($fp['trend'] ?? '') === 'decreasing' ? 'danger' : 'info') ?>">
                                        <?= ucfirst($fp['trend'] ?? 'stable') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="stock-badge stock-badge-<?= ($fp['risk_level'] ?? '') === 'high' ? 'danger' : (($fp['risk_level'] ?? '') === 'medium' ? 'warning' : 'success') ?>">
                                        <?= ucfirst($fp['risk_level'] ?? 'low') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['insights'])): ?>
            <div style="margin-top:1rem; padding:1rem; background:var(--bg-tertiary); border-radius:10px;">
                <h4 style="font-size:0.85rem; font-weight:600; color:var(--text-primary); margin-bottom:0.5rem;">Key Insights</h4>
                <ul style="list-style:none; padding:0;">
                    <?php foreach ($data['insights'] as $insight): ?>
                        <li style="font-size:0.825rem; color:var(--text-secondary); padding:0.25rem 0; display:flex; align-items:flex-start; gap:0.5rem;">
                            <i data-lucide="lightbulb" style="width:14px; height:14px; flex-shrink:0; margin-top:2px; color:var(--warning);"></i>
                            <?= e($insight) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Forecast Chart Container -->
<div class="chart-card" style="margin-top:1rem;" id="forecastChartContainer" style="display:none;">
    <div class="chart-header">
        <h3 class="chart-title">Forecast Visualization</h3>
    </div>
    <div class="chart-container">
        <canvas id="forecastChart"></canvas>
    </div>
</div>

<!-- Loading overlay for AI -->
<div id="forecastLoading" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:250; display:none; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="text-align:center; background:var(--bg-secondary); padding:2rem 3rem; border-radius:16px; border:1px solid var(--border-primary);">
        <div class="loading-spinner" style="margin:0 auto 1rem; width:32px; height:32px;"></div>
        <p style="font-weight:600; color:var(--text-primary);">Analyzing your inventory...</p>
        <p style="font-size:0.8rem; color:var(--text-tertiary); margin-top:0.25rem;">AI is processing your sales data</p>
    </div>
</div>
