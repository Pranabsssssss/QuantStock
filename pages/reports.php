<?php
/**
 * QuantStock — Reports Page
 */
?>

<div class="page-header">
    <div class="page-header-left">
        <h2>Reports</h2>
        <p>Generate and export detailed business reports</p>
    </div>
</div>

<!-- Report Types -->
<div class="report-cards">
    <div class="report-card" onclick="generateReport('inventory')">
        <div class="report-card-icon" style="background: var(--accent-light); color: var(--accent);">
            <i data-lucide="package"></i>
        </div>
        <h3>Inventory Report</h3>
        <p>Complete overview of current stock levels, values, and health status for all products.</p>
        <div style="display:flex; gap:0.5rem; margin-top:1rem;">
            <span class="stock-badge stock-badge-info">CSV</span>
            <span class="stock-badge stock-badge-info">PDF</span>
        </div>
    </div>
    
    <div class="report-card" onclick="generateReport('sales')">
        <div class="report-card-icon" style="background: var(--success-light); color: var(--success);">
            <i data-lucide="shopping-cart"></i>
        </div>
        <h3>Sales Report</h3>
        <p>Detailed sales transactions with revenue, profit margins, and payment breakdowns.</p>
        <div style="display:flex; gap:0.5rem; margin-top:1rem;">
            <span class="stock-badge stock-badge-info">CSV</span>
            <span class="stock-badge stock-badge-info">PDF</span>
        </div>
    </div>
    
    <div class="report-card" onclick="generateReport('forecast')">
        <div class="report-card-icon" style="background: var(--purple-light); color: var(--purple);">
            <i data-lucide="trending-up"></i>
        </div>
        <h3>Forecast Report</h3>
        <p>AI-generated demand predictions and optimization recommendations summary.</p>
        <div style="display:flex; gap:0.5rem; margin-top:1rem;">
            <span class="stock-badge stock-badge-info">CSV</span>
            <span class="stock-badge stock-badge-info">PDF</span>
        </div>
    </div>
</div>

<!-- Date Range -->
<div class="card" style="margin-top:1.5rem;">
    <div class="card-header">
        <h3 class="card-title">Report Options</h3>
    </div>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Date From</label>
            <input type="date" class="form-control" id="reportDateFrom" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Date To</label>
            <input type="date" class="form-control" id="reportDateTo" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
</div>

<!-- Report Preview -->
<div class="card" style="margin-top:1rem;" id="reportPreview" style="display:none;">
    <div class="card-header">
        <h3 class="card-title" id="reportTitle">Report Preview</h3>
        <div style="display:flex; gap:0.5rem;">
            <button class="btn btn-sm btn-secondary" onclick="exportCSV()">
                <i data-lucide="download"></i> CSV
            </button>
            <button class="btn btn-sm btn-primary" onclick="window.print()">
                <i data-lucide="printer"></i> PDF
            </button>
        </div>
    </div>
    <div id="reportContent">
        <?php renderEmptyState(['icon' => 'file-text', 'title' => 'Select a Report Type', 'description' => 'Click on one of the report cards above to generate a report.']); ?>
    </div>
</div>
