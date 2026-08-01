/**
 * QuantStock — Sales JavaScript
 */

let saleItemIndex = 1;

document.addEventListener('DOMContentLoaded', () => {
    if (window.QUANTSTOCK?.page !== 'sales') return;
    initSales();
});

function initSales() {
    const form = document.getElementById('saleForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const items = [];
            document.querySelectorAll('.sale-item').forEach(row => {
                const productId = row.querySelector('.sale-product')?.value;
                const qty = row.querySelector('.sale-qty')?.value;
                const price = row.querySelector('.sale-price')?.value;
                if (productId && qty) {
                    items.push({ product_id: productId, quantity: parseInt(qty), unit_price: parseFloat(price) || 0 });
                }
            });

            if (items.length === 0) {
                showToast('Please add at least one item', 'warning');
                return;
            }

            const btn = document.getElementById('saleSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="loading-spinner" style="width:16px;height:16px;"></div> Processing...';

            const result = await api('api/sales.php', {
                method: 'POST',
                body: {
                    customer_name: document.getElementById('saleCustomer')?.value || '',
                    payment_method: document.getElementById('salePayment')?.value || 'cash',
                    discount: parseFloat(document.getElementById('saleDiscount')?.value) || 0,
                    tax: parseFloat(document.getElementById('saleTax')?.value) || 0,
                    notes: document.getElementById('saleNotes')?.value || '',
                    items: items,
                },
            });

            if (result?.success) {
                showToast('Sale recorded successfully!', 'success');
                closeModal('recordSaleModal');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast(result?.message || 'Failed to record sale', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="check"></i><span>Complete Sale</span>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    }

    // Search
    let timeout;
    document.getElementById('salesSearch')?.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(applySalesFilters, 500);
    });
    document.getElementById('dateFrom')?.addEventListener('change', applySalesFilters);
    document.getElementById('dateTo')?.addEventListener('change', applySalesFilters);
}

function applySalesFilters() {
    const url = new URL(window.location.href);
    url.searchParams.set('page', 'sales');
    const search = document.getElementById('salesSearch')?.value;
    const from = document.getElementById('dateFrom')?.value;
    const to = document.getElementById('dateTo')?.value;
    if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
    if (from) url.searchParams.set('date_from', from); else url.searchParams.delete('date_from');
    if (to) url.searchParams.set('date_to', to); else url.searchParams.delete('date_to');
    url.searchParams.delete('p');
    window.location.href = url.toString();
}

function onProductSelect(select) {
    const option = select.options[select.selectedIndex];
    const row = select.closest('.sale-item');
    const priceInput = row.querySelector('.sale-price');
    if (option.dataset.price) {
        priceInput.value = parseFloat(option.dataset.price).toFixed(2);
    }
    updateSaleTotal();
}

function addSaleItem() {
    const container = document.getElementById('saleItemsContainer');
    const options = document.querySelector('.sale-product').innerHTML;
    
    const html = `
        <div class="sale-item" data-index="${saleItemIndex}" style="display:grid; grid-template-columns:2fr 1fr 1fr auto; gap:0.5rem; align-items:end; margin-bottom:0.5rem;">
            <div class="form-group" style="margin:0;">
                <select class="form-control sale-product" name="items[${saleItemIndex}][product_id]" required onchange="onProductSelect(this)">
                    ${options}
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <input type="number" class="form-control sale-qty" name="items[${saleItemIndex}][quantity]" min="1" value="1" placeholder="Qty" required onchange="updateSaleTotal()">
            </div>
            <div class="form-group" style="margin:0;">
                <input type="number" class="form-control sale-price" name="items[${saleItemIndex}][unit_price]" step="0.01" placeholder="Price" readonly>
            </div>
            <button type="button" class="btn btn-ghost btn-icon" onclick="removeSaleItem(this)" style="color:var(--danger);">
                <i data-lucide="x"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    saleItemIndex++;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function removeSaleItem(btn) {
    const items = document.querySelectorAll('.sale-item');
    if (items.length <= 1) {
        showToast('At least one item is required', 'warning');
        return;
    }
    btn.closest('.sale-item').remove();
    updateSaleTotal();
}

function updateSaleTotal() {
    let subtotal = 0;
    document.querySelectorAll('.sale-item').forEach(row => {
        const qty = parseInt(row.querySelector('.sale-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.sale-price')?.value) || 0;
        subtotal += qty * price;
    });

    const discount = parseFloat(document.getElementById('saleDiscount')?.value) || 0;
    const tax = parseFloat(document.getElementById('saleTax')?.value) || 0;
    const total = subtotal - discount + tax;

    document.getElementById('saleSubtotal').textContent = formatCurrency(subtotal);
    document.getElementById('saleTotal').textContent = formatCurrency(total);
}

async function viewSale(id) {
    openModal('saleDetailModal');
    const content = document.getElementById('saleDetailContent');
    content.innerHTML = '<div class="loading-spinner" style="margin:2rem auto;"></div>';

    const result = await api(`api/sales.php?id=${id}`);
    if (!result?.success) {
        content.innerHTML = '<p style="text-align:center;color:var(--danger);">Failed to load sale details</p>';
        return;
    }

    const s = result.data;
    let html = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div><span style="font-size:0.75rem; color:var(--text-tertiary); text-transform:uppercase;">Invoice</span><br><strong style="color:var(--accent);">${s.invoice_number}</strong></div>
            <div><span style="font-size:0.75rem; color:var(--text-tertiary); text-transform:uppercase;">Date</span><br><strong>${new Date(s.created_at).toLocaleDateString('en-IN', {day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})}</strong></div>
            <div><span style="font-size:0.75rem; color:var(--text-tertiary); text-transform:uppercase;">Customer</span><br><strong>${s.customer_name || 'Walk-in'}</strong></div>
            <div><span style="font-size:0.75rem; color:var(--text-tertiary); text-transform:uppercase;">Payment</span><br><strong>${s.payment_method?.toUpperCase()}</strong></div>
        </div>
        <table class="data-table" style="margin-bottom:1rem;">
            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
    `;
    (s.items || []).forEach(item => {
        html += `<tr><td style="font-weight:600;">${item.product_name}</td><td>${item.quantity}</td><td>${formatCurrency(item.unit_price)}</td><td style="font-weight:600;">${formatCurrency(item.total_price)}</td></tr>`;
    });
    html += `</tbody></table>
        <div style="background:var(--bg-tertiary); padding:1rem; border-radius:10px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;"><span>Subtotal</span><span>${formatCurrency(s.total_amount)}</span></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:0.25rem;"><span>Discount</span><span>-${formatCurrency(s.discount)}</span></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;"><span>Tax</span><span>+${formatCurrency(s.tax)}</span></div>
            <div style="display:flex; justify-content:space-between; font-size:1.1rem; font-weight:700; padding-top:0.5rem; border-top:1px solid var(--border-primary);"><span>Total</span><span style="color:var(--accent);">${formatCurrency(s.net_amount)}</span></div>
        </div>
    `;
    content.innerHTML = html;
}
