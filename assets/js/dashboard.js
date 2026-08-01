/**
 * QuantStock — Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    if (window.QUANTSTOCK?.page !== 'dashboard') return;
    
    initDashboardCharts();
    initDashboardActions();
});

function initDashboardCharts() {
    const data = window.DASHBOARD_DATA;
    if (!data) return;

    const defaults = getChartDefaults();

    // Sales Trend Chart
    const salesCtx = document.getElementById('salesTrendChart');
    if (salesCtx && data.salesTrend) {
        const labels = data.salesTrend.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
        });
        const revenues = data.salesTrend.map(d => parseFloat(d.revenue));

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Sales',
                    data: revenues,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    pointBackgroundColor: '#3B82F6',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top', align: 'start' },
                    tooltip: {
                        callbacks: {
                            label: ctx => formatCurrency(ctx.raw),
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v) },
                    },
                },
            },
        });
    }

    // Distribution Chart
    const distCtx = document.getElementById('distributionChart');
    if (distCtx && data.distribution && data.distribution.length > 0) {
        const labels = data.distribution.map(d => d.name);
        const values = data.distribution.map(d => parseInt(d.count));
        const colors = data.distribution.map(d => d.color || '#3B82F6');

        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'right', labels: { padding: 12, font: { size: 11 } } },
                },
            },
        });

        // Center text
        const centerText = data.totalProducts || values.reduce((a, b) => a + b, 0);
        const legend = document.getElementById('distributionLegend');
        // Legend already rendered by Chart.js
    }

    // Chart filter buttons
    document.querySelectorAll('.chart-filter-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            this.parentElement.querySelectorAll('.chart-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const days = this.dataset.days;
            // Reload sales trend with new days
            const result = await api(`api/dashboard.php?action=sales_trend&days=${days}`);
            if (result?.success) {
                updateSalesTrendChart(result.data);
            }
        });
    });
}

function updateSalesTrendChart(data) {
    const canvas = document.getElementById('salesTrendChart');
    if (!canvas) return;
    const chart = Chart.getChart(canvas);
    if (chart) {
        chart.data.labels = data.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
        });
        chart.data.datasets[0].data = data.map(d => parseFloat(d.revenue));
        chart.update();
    }
}

function initDashboardActions() {
    // Generate Forecast button
    document.getElementById('generateForecastBtn')?.addEventListener('click', async () => {
        showToast('Generating AI forecast...', 'info');
        const result = await api('api/forecast.php', { method: 'POST', body: { action: 'forecast' } });
        if (result?.success) {
            showToast('Forecast generated! Redirecting...', 'success');
            setTimeout(() => window.location.href = '?page=forecast', 1500);
        } else {
            showToast(result?.message || 'Failed to generate forecast', 'error');
        }
    });

    // Analyze Inventory button
    document.getElementById('analyzeInventoryBtn')?.addEventListener('click', async () => {
        showToast('Analyzing inventory with AI...', 'info');
        const result = await api('api/optimization.php', { method: 'POST', body: { action: 'optimize' } });
        if (result?.success) {
            showToast('Analysis complete! Redirecting...', 'success');
            setTimeout(() => window.location.href = '?page=optimization', 1500);
        } else {
            showToast(result?.message || 'Failed to analyze inventory', 'error');
        }
    });
}
