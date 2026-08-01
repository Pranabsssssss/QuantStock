<?php
/**
 * QuantStock — Products Page
 * 
 * Full CRUD with search, filter, sort, pagination.
 */

$productModel = new Product();
$categoryModel = new Category();
$supplierModel = new Supplier();

$categories = $categoryModel->getAll(true);
$suppliers = $supplierModel->getAll(true);

// Pagination
$currentPage = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;
$filters = [
    'search'      => $_GET['search'] ?? '',
    'category_id' => $_GET['category'] ?? '',
    'status'      => $_GET['status'] ?? '',
    'limit'       => $perPage,
    'offset'      => ($currentPage - 1) * $perPage,
    'sort'        => 'p.created_at',
    'order'       => 'DESC',
];

$result = $productModel->getAll($filters);
$products = $result['data'];
$totalProducts = $result['total'];
$pagination = getPagination($totalProducts, $currentPage, $perPage);
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h2>Products</h2>
        <p>Manage your inventory products</p>
    </div>
    <div class="page-header-right">
        <button class="btn btn-primary" onclick="openModal('addProductModal')">
            <i data-lucide="plus"></i>
            <span>Add Product</span>
        </button>
    </div>
</div>

<!-- Toolbar -->
<div class="table-toolbar">
    <div class="table-search">
        <i data-lucide="search"></i>
        <input type="text" id="productSearch" placeholder="Search products..." value="<?= e($filters['search']) ?>">
    </div>
    <div class="table-filters">
        <select class="filter-select" id="categoryFilter">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="filter-select" id="statusFilter">
            <option value="">All Status</option>
            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            <option value="discontinued" <?= $filters['status'] === 'discontinued' ? 'selected' : '' ?>>Discontinued</option>
        </select>
    </div>
</div>

<!-- Products Table -->
<?php if (empty($products)): ?>
    <div class="card">
        <?php renderEmptyState([
            'icon' => 'package',
            'title' => 'No Products Found',
            'description' => $filters['search'] ? 'No products match your search criteria.' : 'Start by adding your first product to the inventory.',
            'action' => 'Add Product',
            'action_id' => 'emptyAddProduct',
        ]); ?>
    </div>
<?php else: ?>
    <?php
    renderTableStart([
        'id' => 'productsTable',
        'columns' => ['Product', 'SKU', 'Category', 'Supplier', 'Buy Price', 'Sell Price', 'Stock', 'Min Stock', 'Status', 'Actions'],
    ]);
    ?>
    <?php foreach ($products as $prod):
        $status = getStockStatus($prod['current_stock'], $prod['min_stock'], $prod['max_stock']);
    ?>
        <tr>
            <td>
                <div class="cell-product">
                    <div class="cell-product-img">
                        <?php if ($prod['image']): ?>
                            <img src="<?= e($prod['image']) ?>" alt="<?= e($prod['name']) ?>">
                        <?php else: ?>
                            <i data-lucide="package"></i>
                        <?php endif; ?>
                    </div>
                    <div class="cell-product-info">
                        <div class="cell-product-name"><?= e($prod['name']) ?></div>
                    </div>
                </div>
            </td>
            <td style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-secondary);"><?= e($prod['sku']) ?></td>
            <td>
                <?php if ($prod['category_name']): ?>
                    <span class="stock-badge" style="background: <?= e($prod['category_color']) ?>20; color: <?= e($prod['category_color']) ?>;">
                        <?= e($prod['category_name']) ?>
                    </span>
                <?php else: ?>
                    <span style="color: var(--text-tertiary);">—</span>
                <?php endif; ?>
            </td>
            <td style="color: var(--text-secondary);"><?= e($prod['supplier_name'] ?? '—') ?></td>
            <td><?= formatCurrency($prod['cost_price']) ?></td>
            <td style="font-weight: 600;"><?= formatCurrency($prod['selling_price']) ?></td>
            <td>
                <span class="stock-badge stock-badge-<?= $status['class'] ?>">
                    <?= $prod['current_stock'] ?>
                </span>
            </td>
            <td style="color: var(--text-tertiary);"><?= $prod['min_stock'] ?></td>
            <td>
                <span class="status-badge status-<?= $prod['status'] === 'active' ? 'completed' : ($prod['status'] === 'inactive' ? 'pending' : 'cancelled') ?>">
                    <?= ucfirst($prod['status']) ?>
                </span>
            </td>
            <td>
                <div style="display: flex; gap: 0.25rem;">
                    <button class="btn btn-ghost btn-icon" onclick="editProduct(<?= $prod['id'] ?>)" title="Edit">
                        <i data-lucide="pencil"></i>
                    </button>
                    <button class="btn btn-ghost btn-icon" onclick="deleteProduct(<?= $prod['id'] ?>, '<?= e($prod['name']) ?>')" title="Delete" style="color: var(--danger);">
                        <i data-lucide="trash-2"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php renderTableEnd($pagination); ?>
<?php endif; ?>

<!-- Add/Edit Product Modal -->
<?php renderModal(['id' => 'addProductModal', 'title' => 'Add Product', 'size' => 'lg']); ?>
<form id="productForm" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
    <input type="hidden" name="id" id="productId" value="">
    
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label">Product Name *</label>
            <input type="text" class="form-control" name="name" id="productName" placeholder="Enter product name" required>
        </div>
        <div class="form-group">
            <label class="form-label">SKU</label>
            <input type="text" class="form-control" name="sku" id="productSku" placeholder="Auto-generated">
        </div>
        <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-control" name="category_id" id="productCategory">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Supplier</label>
            <select class="form-control" name="supplier_id" id="productSupplier">
                <option value="">Select Supplier</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?= $sup['id'] ?>"><?= e($sup['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Cost Price *</label>
            <input type="number" class="form-control" name="cost_price" id="productCostPrice" step="0.01" min="0" placeholder="0.00" required>
        </div>
        <div class="form-group">
            <label class="form-label">Selling Price *</label>
            <input type="number" class="form-control" name="selling_price" id="productSellingPrice" step="0.01" min="0" placeholder="0.00" required>
        </div>
        <div class="form-group">
            <label class="form-label">Current Stock</label>
            <input type="number" class="form-control" name="current_stock" id="productStock" min="0" value="0">
        </div>
        <div class="form-group">
            <label class="form-label">Min Stock Level</label>
            <input type="number" class="form-control" name="min_stock" id="productMinStock" min="0" value="5">
        </div>
        <div class="form-group">
            <label class="form-label">Max Stock Level</label>
            <input type="number" class="form-control" name="max_stock" id="productMaxStock" min="0" value="1000">
        </div>
        <div class="form-group">
            <label class="form-label">Barcode</label>
            <input type="text" class="form-control" name="barcode" id="productBarcode" placeholder="Optional barcode">
        </div>
        <div class="form-group form-grid-full">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="productDescription" placeholder="Product description..." rows="2"></textarea>
        </div>
        <div class="form-group form-grid-full">
            <label class="form-label">Product Image</label>
            <div class="image-upload" id="imageUploadZone">
                <div class="image-upload-placeholder" id="imagePlaceholder">
                    <i data-lucide="upload-cloud"></i>
                    <p>Click or drag to upload (JPG, PNG, WebP — max 2MB)</p>
                </div>
                <img class="image-preview" id="imagePreview" style="display:none;">
                <input type="file" name="image" id="productImage" accept="image/jpeg,image/png,image/webp">
            </div>
        </div>
    </div>
    <div class="modal-footer" style="margin: 0 -1.5rem -1.5rem; padding: 1rem 1.5rem;">
        <button type="button" class="btn btn-secondary" onclick="closeModal('addProductModal')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="productSubmitBtn">
            <i data-lucide="plus"></i>
            <span>Add Product</span>
        </button>
    </div>
</form>
<?php renderModalEnd(); ?>

<!-- Inject categories/suppliers for JS -->
<script>
window.PRODUCT_DATA = {
    categories: <?= json_encode($categories) ?>,
    suppliers: <?= json_encode($suppliers) ?>,
};
</script>
