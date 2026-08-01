<?php
/**
 * QuantStock — Analytics Page
 */

$analytics = new Analytics();
$revenueTrend = $analytics->getRevenueTrend(6);
$profitTrend = $analytics->getProfitTrend(6);
$topProducts = $analytics->getTopProducts(30, 10);
$slowMoving = $analytics->getSlowMovingProducts(30, 10);
$deadStock = $analytics->getDeadStock(60);
$categoryPerformance = $analytics->getCategoryPerformance(30);
$totalRevenue = $analytics->getTotalRevenue();
$totalProfit = $analytics->getTotalProfit();
?>

<div class="page-header">
    <div class="page-header-left">
        <h2>Analytics</h2>
        <p>In-depth analysis of your business performance</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    <?php
    renderCard(['title' => 'Total Revenue', 'value' => formatCompact($totalRevenue), 'icon' => 'indian-rupee', 'color' => 'green', 'subtitle' => 'All time']);
    renderCard(['title' => 'Total Profit', 'value' => formatCompact($totalProfit), 'icon' => 'trending-up', 'color' => 'blue', 'subtitle' => 'All time']);
    renderCard(['title' => 'Profit Margin', 'value' => ($totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0) . '%', 'icon' => 'percent', 'color' => 'purple', 'subtitle' => 'Overall margin']);
    renderCard(['title' => 'Dead Stock Items', 'value' => count($deadStock), 'icon' => 'archive', 'color' => count($deadStock) > 0 ? 'red' : 'green', 'subtitle' => 'No sales in 60+ days']);
    ?>
</div>

<!-- Charts -->
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Revenue & Profit Trend</h3>
        </div>
        <div class="chart-container">
            <canvas id="revenueProfitChart"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Category Performance</h3>
        </div>
        <div class="chart-container" style="display:flex; align-items:center; justify-content:center;">
            <canvas id="categoryChart" style="max-width:260px;"></canvas>
        </div>
    </div>
</div>

<!-- Top Products -->
<div class="card" style="margin-bottom:1rem;">
    <div class="card-header">
        <h3 class="card-title">Top Selling Products (Last 30 Days)</h3>
    </div>
    <?php if (empty($topProducts)): ?>
        <?php renderEmptyState(['icon' => 'trophy', 'title' => 'No Sales Data Yet', 'description' => 'Top selling products will appear once sales are recorded.']); ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Product</th><th>SKU</th><th>Units Sold</th><th>Revenue</th><th>Profit</th><th>Stock</th></tr></thead>
                <tbody>
                    <?php foreach ($topProducts as $tp): ?>
                        <tr>
                            <td style="font-weight:600;"><?= e($tp['name']) ?></td>
                            <td style="color:var(--text-tertiary); font-size:0.8rem;"><?= e($tp['sku']) ?></td>
                            <td style="font-weight:600;"><?= formatNumber($tp['total_sold']) ?></td>
                            <td style="color:var(--success); font-weight:600;"><?= formatCurrency($tp['total_revenue']) ?></td>
                            <td style="font-weight:600;"><?= formatCurrency($tp['profit']) ?></td>
                            <td><span class="stock-badge stock-badge-<?= $tp['current_stock'] > 10 ? 'success' : ($tp['current_stock'] > 0 ? 'warning' : 'danger') ?>"><?= $tp['current_stock'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="charts-grid">
    <!-- Slow Moving Products -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Slow Moving Products</h3>
        </div>
        <?php if (empty($slowMoving)): ?>
            <?php renderEmptyState(['icon' => 'snail', 'title' => 'No Slow Movers', 'description' => 'All products are selling well.']); ?>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Stock</th><th>Value</th><th>Sold</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($slowMoving, 0, 5) as $sm): ?>
                            <tr>
                                <td style="font-weight:600;"><?= e($sm['name']) ?></td>
                                <td><?= $sm['current_stock'] ?></td>
                                <td><?= formatCurrency($sm['stock_value']) ?></td>
                                <td style="color:var(--danger);"><?= $sm['total_sold'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dead Stock -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Dead Stock</h3>
        </div>
        <?php if (empty($deadStock)): ?>
            <?php renderEmptyState(['icon' => 'check-circle', 'title' => 'No Dead Stock', 'description' => 'All products have recent sales activity.']); ?>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Stock</th><th>Locked Value</th><th>Last Sold</th></tr></thead>
                    <tbody>
                        <?php foreach (array_slice($deadStock, 0, 5) as $ds): ?>
                            <tr>
                                <td style="font-weight:600;"><?= e($ds['name']) ?></td>
                                <td><?= $ds['current_stock'] ?></td>
                                <td style="color:var(--danger);"><?= formatCurrency($ds['locked_value']) ?></td>
                                <td style="color:var(--text-tertiary); font-size:0.8rem;"><?= $ds['last_sold'] ? formatDate($ds['last_sold']) : 'Never' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
window.ANALYTICS_DATA = {
    revenueTrend: <?= json_encode($revenueTrend) ?>,
    profitTrend: <?= json_encode($profitTrend) ?>,
    categoryPerformance: <?= json_encode($categoryPerformance) ?>,
};
</script>
