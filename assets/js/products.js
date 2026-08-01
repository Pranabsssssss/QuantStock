/**
 * QuantStock — Products JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    if (window.QUANTSTOCK?.page !== 'products') return;
    initProducts();
});

function initProducts() {
    // Product form submission
    const form = document.getElementById('productForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('csrf_token', window.QUANTSTOCK.csrf);

            const btn = document.getElementById('productSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="loading-spinner" style="width:16px;height:16px;"></div> Saving...';

            try {
                const response = await fetch('api/products.php', { method: 'POST', body: formData });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal('addProductModal');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (err) {
                showToast('Failed to save product', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="plus"></i><span>Add Product</span>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    }

    // Image preview
    const imageInput = document.getElementById('productImage');
    if (imageInput) {
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    document.getElementById('imagePreview').src = ev.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                    document.getElementById('imagePlaceholder').style.display = 'none';
                    document.getElementById('imageUploadZone').classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyProductFilters();
            }, 500);
        });
    }

    // Category & Status filters
    document.getElementById('categoryFilter')?.addEventListener('change', applyProductFilters);
    document.getElementById('statusFilter')?.addEventListener('change', applyProductFilters);
}

function applyProductFilters() {
    const search = document.getElementById('productSearch')?.value || '';
    const category = document.getElementById('categoryFilter')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    
    const url = new URL(window.location.href);
    url.searchParams.set('page', 'products');
    if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
    if (category) url.searchParams.set('category', category); else url.searchParams.delete('category');
    if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');
    url.searchParams.delete('p');
    
    window.location.href = url.toString();
}

async function editProduct(id) {
    const result = await api(`api/products.php?id=${id}`);
    if (!result?.success) {
        showToast('Failed to load product', 'error');
        return;
    }

    const p = result.data;
    document.getElementById('productId').value = p.id;
    document.getElementById('productName').value = p.name;
    document.getElementById('productSku').value = p.sku;
    document.getElementById('productCategory').value = p.category_id || '';
    document.getElementById('productSupplier').value = p.supplier_id || '';
    document.getElementById('productCostPrice').value = p.cost_price;
    document.getElementById('productSellingPrice').value = p.selling_price;
    document.getElementById('productStock').value = p.current_stock;
    document.getElementById('productMinStock').value = p.min_stock;
    document.getElementById('productMaxStock').value = p.max_stock;
    document.getElementById('productBarcode').value = p.barcode || '';
    document.getElementById('productDescription').value = p.description || '';

    if (p.image) {
        document.getElementById('imagePreview').src = p.image;
        document.getElementById('imagePreview').style.display = 'block';
        document.getElementById('imagePlaceholder').style.display = 'none';
    }

    // Update modal title and button
    document.querySelector('#addProductModal .modal-title').textContent = 'Edit Product';
    document.getElementById('productSubmitBtn').innerHTML = '<i data-lucide="save"></i><span>Update Product</span>';
    
    openModal('addProductModal');
}

async function deleteProduct(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

    const result = await api(`api/products.php?id=${id}`, { method: 'DELETE' });
    if (result?.success) {
        showToast(result.message, 'success');
        setTimeout(() => window.location.reload(), 1000);
    } else {
        showToast(result?.message || 'Failed to delete', 'error');
    }
}
