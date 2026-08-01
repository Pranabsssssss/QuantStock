<?php
/**
 * QuantStock — Dashboard Page
 * 
 * All data from MySQL. No hardcoded values.
 */

$analytics = new Analytics();
$productModel = new Product();
$saleModel = new Sale();
$forecastModel = new Forecast();

$summary = $analytics->getDashboardSummary();
$lowStockProducts = $productModel->getLowStock(8);
$recentSales = $saleModel->getRecentSales(5);
$recentActivity = $analytics->getRecentActivity(8);
$salesTrend = $saleModel->getSalesTrend(30);
$distribution = $productModel->getDistribution();
$inventoryHealth = $summary['inventory_health'];

$userName = e($user['name'] ?? 'Admin');
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-welcome">
        <h2>Welcome back, <?= $userName ?> 👋</h2>
        <p>Here's what's happening with your inventory today.</p>
    </div>
    <div class="page-header-right">
        <button class="btn btn-outline" id="generateForecastBtn">
            <i data-lucide="trending-up"></i>
            <span>Generate Forecast</span>
        </button>
        <button class="btn btn-primary" id="analyzeInventoryBtn">
            <i data-lucide="brain-circuit"></i>
            <span>Analyze Inventory</span>
        </button>
    </div>
</div>

<!-- Stat Cards Row 1 -->
<div class="stats-grid">
    <?php
    renderCard([
        'title' => 'Total Products',
        'value' => formatNumber($summary['total_products']),
        'icon' => 'package',
        'color' => 'blue',
        'subtitle' => 'Active products in catalog',
    ]);

    renderCard([
        'title' => 'Inventory Value',
        'value' => formatCompact($summary['inventory_value']),
        'icon' => 'indian-rupee',
        'color' => 'green',
        'trend' => $summary['revenue_change'],
        'subtitle' => 'from last month',
    ]);

    renderCard([
        'title' => "Today's Revenue",
        'value' => formatCurrency($summary['today_revenue']),
        'icon' => 'wallet',
        'color' => 'purple',
        'subtitle' => $summary['today_sales'] . ' transactions today',
    ]);

    renderCard([
        'title' => 'Low Stock Items',
        'value' => formatNumber($summary['low_stock_count']),
        'icon' => 'alert-triangle',
        'color' => $summary['low_stock_count'] > 0 ? 'red' : 'green',
        'subtitle' => $summary['low_stock_count'] > 0 ? 'Items need attention' : 'All stock levels healthy',
    ]);
    ?>
</div>

<!-- Stat Cards Row 2 -->
<div class="stats-grid">
    <?php
    renderCard([
        'title' => 'Monthly Revenue',
        'value' => formatCompact($summary['month_revenue']),
        'icon' => 'bar-chart-3',
        'color' => 'green',
        'trend' => $summary['revenue_change'],
        'subtitle' => 'from last month',
    ]);

    renderCard([
        'title' => 'Inventory Health',
        'value' => $inventoryHealth . '%',
        'icon' => 'heart-pulse',
        'color' => $inventoryHealth >= 80 ? 'green' : ($inventoryHealth >= 50 ? 'orange' : 'red'),
        'subtitle' => $inventoryHealth >= 80 ? 'Excellent' : ($inventoryHealth >= 50 ? 'Needs Attention' : 'Critical'),
    ]);

    renderCard([
        'title' => 'Orders This Month',
        'value' => formatNumber($summary['month_sales']),
        'icon' => 'shopping-bag',
        'color' => 'blue',
        'subtitle' => 'Completed transactions',
    ]);

    renderCard([
        'title' => 'Pending Recommendations',
        'value' => formatNumber($summary['pending_recommendations']),
        'icon' => 'bot',
        'color' => 'purple',
        'subtitle' => $summary['pending_recommendations'] > 0 ? 'View all orders' : 'No pending items',
        'link' => $summary['pending_recommendations'] > 0 ? '?page=optimization' : '',
        'link_text' => $summary['pending_recommendations'] > 0 ? 'View all' : '',
    ]);
    ?>
</div>

<!-- Charts Row -->
<div class="charts-grid">
    <!-- Sales Trend Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Sales Trend (This Month)</h3>
            <div class="chart-filter">
                <button class="chart-filter-btn active" data-days="7">7D</button>
                <button class="chart-filter-btn" data-days="14">14D</button>
                <button class="chart-filter-btn" data-days="30">30D</button>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <!-- Inventory Distribution Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Inventory Distribution</h3>
        </div>
        <div class="chart-container" style="display:flex; align-items:center; justify-content:center;">
            <canvas id="distributionChart" style="max-width: 260px;"></canvas>
            <div id="distributionLegend" class="chart-legend" style="margin-left: 1rem;"></div>
        </div>
    </div>
</div>

<!-- Bottom Row -->
<div class="dashboard-grid dashboard-grid-3">
    <!-- Low Stock Alerts -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Low Stock Alerts</h3>
            <?php if (count($lowStockProducts) > 0): ?>
                <a href="?page=products" class="stat-link">View all <i data-lucide="arrow-right"></i></a>
            <?php endif; ?>
        </div>
        <?php if (empty($lowStockProducts)): ?>
            <?php renderEmptyState(['icon' => 'check-circle', 'title' => 'All Stock Levels Healthy', 'description' => 'No products are running low on stock.']); ?>
        <?php else: ?>
            <div class="low-stock-list">
                <?php foreach ($lowStockProducts as $prod): ?>
                    <div class="low-stock-item">
                        <div class="low-stock-info">
                            <div class="low-stock-img">
                                <?php if ($prod['image']): ?>
                                    <img src="<?= e($prod['image']) ?>" alt="<?= e($prod['name']) ?>">
                                <?php else: ?>
                                    <i data-lucide="package"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="low-stock-name"><?= e($prod['name']) ?></div>
                                <div class="low-stock-sku"><?= e($prod['sku']) ?></div>
                            </div>
                        </div>
                        <?php $status = getStockStatus($prod['current_stock'], $prod['min_stock'], $prod['max_stock']); ?>
                        <span class="low-stock-qty stock-badge stock-badge-<?= $status['class'] ?>">
                            <?= $prod['current_stock'] ?> left
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
        </div>
        <?php if (empty($recentActivity)): ?>
            <?php renderEmptyState(['icon' => 'activity', 'title' => 'No Recent Activity', 'description' => 'Inventory changes will appear here.']); ?>
        <?php else: ?>
            <div class="activity-list">
                <?php foreach ($recentActivity as $act): ?>
                    <div class="activity-item">
                        <div class="activity-icon activity-icon-<?= $act['type'] ?>">
                            <i data-lucide="<?= $act['type'] === 'in' ? 'arrow-down' : ($act['type'] === 'out' ? 'arrow-up' : 'refresh-cw') ?>"></i>
                        </div>
                        <div>
                            <div class="activity-text">
                                <strong><?= e($act['product_name']) ?></strong> — 
                                <?= $act['type'] === 'in' ? 'Stock added' : ($act['type'] === 'out' ? 'Sold' : 'Adjusted') ?>
                                (<?= $act['quantity'] ?> units)
                                <?php if ($act['reference']): ?>
                                    <br><small><?= e($act['reference']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="activity-time"><?= timeAgo($act['created_at']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Sales Table -->
<div class="card" style="margin-top: 1rem;">
    <div class="card-header">
        <h3 class="card-title">Recent Sales</h3>
        <a href="?page=sales" class="stat-link">View all <i data-lucide="arrow-right"></i></a>
    </div>
    <?php if (empty($recentSales)): ?>
        <?php renderEmptyState(['icon' => 'shopping-cart', 'title' => 'No Sales Recorded', 'description' => 'Sales transactions will appear here once recorded.']); ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Products</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSales as $sale): ?>
                        <tr>
                            <td><span style="font-weight:600; color:var(--accent);"><?= e($sale['invoice_number']) ?></span></td>
                            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= e($sale['products'] ?? 'N/A') ?></td>
                            <td style="font-weight:600;"><?= formatCurrency($sale['net_amount']) ?></td>
                            <td><span class="status-badge status-completed"><?= ucfirst(e($sale['payment_method'])) ?></span></td>
                            <td style="color:var(--text-tertiary); font-size:0.8rem;"><?= formatDateTime($sale['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Inject chart data for JS -->
<script>
window.DASHBOARD_DATA = {
    salesTrend: <?= json_encode($salesTrend) ?>,
    distribution: <?= json_encode($distribution) ?>,
    totalProducts: <?= (int)$summary['total_products'] ?>,
};
</script>
