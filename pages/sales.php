<?php
/**
 * QuantStock — Sales Page
 */

$saleModel = new Sale();
$productModel = new Product();

$currentPage = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;
$filters = [
    'search'    => $_GET['search'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to'   => $_GET['date_to'] ?? '',
    'limit'     => $perPage,
    'offset'    => ($currentPage - 1) * $perPage,
];

$result = $saleModel->getAll($filters);
$sales = $result['data'];
$totalSales = $result['total'];
$pagination = getPagination($totalSales, $currentPage, $perPage);

// Get active products for sale form
$activeProducts = $productModel->getAll(['status' => 'active', 'limit' => 500, 'offset' => 0]);
?>

<div class="page-header">
    <div class="page-header-left">
        <h2>Sales</h2>
        <p>Track your sales transactions</p>
    </div>
    <div class="page-header-right">
        <button class="btn btn-primary" onclick="openModal('recordSaleModal')">
            <i data-lucide="plus"></i>
            <span>Record Sale</span>
        </button>
    </div>
</div>

<!-- Filters -->
<div class="table-toolbar">
    <div class="table-search">
        <i data-lucide="search"></i>
        <input type="text" id="salesSearch" placeholder="Search sales..." value="<?= e($filters['search']) ?>">
    </div>
    <div class="table-filters">
        <input type="date" class="filter-select" id="dateFrom" value="<?= e($filters['date_from']) ?>" style="padding-right:0.5rem;">
        <input type="date" class="filter-select" id="dateTo" value="<?= e($filters['date_to']) ?>" style="padding-right:0.5rem;">
    </div>
</div>

<!-- Sales Table -->
<?php if (empty($sales)): ?>
    <div class="card">
        <?php renderEmptyState([
            'icon' => 'shopping-cart',
            'title' => 'No Sales Recorded',
            'description' => 'Record your first sale to start tracking revenue.',
            'action' => 'Record Sale',
            'action_id' => 'emptyRecordSale',
        ]); ?>
    </div>
<?php else: ?>
    <?php renderTableStart(['id' => 'salesTable', 'columns' => ['Invoice', 'Customer', 'Product', 'Quantity', 'Sale Date', 'Revenue', 'Status', 'Actions']]); ?>
    <?php foreach ($sales as $sale): ?>
        <tr>
            <td><span style="font-weight:600; color:var(--accent);"><?= e($sale['invoice_number']) ?></span></td>
            <td><?= e($sale['customer_name'] ?: '—') ?></td>
            <td style="max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text-secondary);">—</td>
            <td>—</td>
            <td style="font-size:0.8rem; color:var(--text-secondary);"><?= formatDateTime($sale['created_at']) ?></td>
            <td style="font-weight:700;"><?= formatCurrency($sale['net_amount']) ?></td>
            <td>
                <span class="status-badge status-<?= $sale['status'] ?>">
                    <?= ucfirst($sale['status']) ?>
                </span>
            </td>
            <td>
                <button class="btn btn-ghost btn-icon" onclick="viewSale(<?= $sale['id'] ?>)" title="View">
                    <i data-lucide="eye"></i>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php renderTableEnd($pagination); ?>
<?php endif; ?>

<!-- Record Sale Modal -->
<?php renderModal(['id' => 'recordSaleModal', 'title' => 'Record New Sale', 'size' => 'lg']); ?>
<form id="saleForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
    
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Customer Name</label>
            <input type="text" class="form-control" name="customer_name" id="saleCustomer" placeholder="Walk-in customer">
        </div>
        <div class="form-group">
            <label class="form-label">Payment Method</label>
            <select class="form-control" name="payment_method" id="salePayment">
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="upi">UPI</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
        </div>
    </div>

    <!-- Sale Items -->
    <div style="margin: 1rem 0;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
            <label class="form-label" style="margin:0;">Sale Items *</label>
            <button type="button" class="btn btn-sm btn-outline" onclick="addSaleItem()">
                <i data-lucide="plus"></i> Add Item
            </button>
        </div>
        <div id="saleItemsContainer">
            <div class="sale-item" data-index="0" style="display:grid; grid-template-columns:2fr 1fr 1fr auto; gap:0.5rem; align-items:end; margin-bottom:0.5rem;">
                <div class="form-group" style="margin:0;">
                    <select class="form-control sale-product" name="items[0][product_id]" required onchange="onProductSelect(this)">
                        <option value="">Select Product</option>
                        <?php foreach ($activeProducts['data'] as $p): ?>
                            <option value="<?= $p['id'] ?>" data-price="<?= $p['selling_price'] ?>" data-stock="<?= $p['current_stock'] ?>"><?= e($p['name']) ?> (Stock: <?= $p['current_stock'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <input type="number" class="form-control sale-qty" name="items[0][quantity]" min="1" value="1" placeholder="Qty" required onchange="updateSaleTotal()">
                </div>
                <div class="form-group" style="margin:0;">
                    <input type="number" class="form-control sale-price" name="items[0][unit_price]" step="0.01" placeholder="Price" readonly>
                </div>
                <button type="button" class="btn btn-ghost btn-icon" onclick="removeSaleItem(this)" style="color:var(--danger);">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Discount</label>
            <input type="number" class="form-control" name="discount" id="saleDiscount" step="0.01" min="0" value="0" onchange="updateSaleTotal()">
        </div>
        <div class="form-group">
            <label class="form-label">Tax</label>
            <input type="number" class="form-control" name="tax" id="saleTax" step="0.01" min="0" value="0" onchange="updateSaleTotal()">
        </div>
    </div>

    <div style="background: var(--bg-tertiary); padding: 1rem; border-radius: 10px; margin-top: 0.5rem;">
        <div style="display:flex; justify-content:space-between; margin-bottom: 0.5rem;">
            <span style="color:var(--text-secondary);">Subtotal</span>
            <span id="saleSubtotal" style="font-weight:600;">₹0.00</span>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:1.1rem;">
            <span style="font-weight:700;">Total</span>
            <span id="saleTotal" style="font-weight:800; color:var(--accent);">₹0.00</span>
        </div>
    </div>

    <div class="form-group" style="margin-top: 1rem;">
        <label class="form-label">Notes</label>
        <textarea class="form-control" name="notes" id="saleNotes" placeholder="Optional notes..." rows="2"></textarea>
    </div>

    <div class="modal-footer" style="margin: 0 -1.5rem -1.5rem; padding: 1rem 1.5rem;">
        <button type="button" class="btn btn-secondary" onclick="closeModal('recordSaleModal')">Cancel</button>
        <button type="submit" class="btn btn-success" id="saleSubmitBtn">
            <i data-lucide="check"></i>
            <span>Complete Sale</span>
        </button>
    </div>
</form>
<?php renderModalEnd(); ?>

<!-- Sale Detail Modal -->
<?php renderModal(['id' => 'saleDetailModal', 'title' => 'Sale Details', 'size' => 'md']); ?>
<div id="saleDetailContent">
    <div class="loading-spinner" style="margin: 2rem auto;"></div>
</div>
<?php renderModalEnd(); ?>
