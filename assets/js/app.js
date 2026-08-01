/**
 * QuantStock — Core Application JavaScript
 * 
 * Theme management, sidebar, modals, toasts, fetch wrapper, global utilities.
 */

// ==========================================
// Theme Management
// ==========================================
function initTheme() {
    const saved = localStorage.getItem('quantstock-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcons();
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('quantstock-theme', next);
    updateThemeIcons();
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('quantstock-theme', theme);
    updateThemeIcons();
    showToast(`Theme changed to ${theme} mode`, 'success');
}

function updateThemeIcons() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    document.querySelectorAll('.theme-icon-light').forEach(el => el.style.display = isDark ? 'none' : 'block');
    document.querySelectorAll('.theme-icon-dark').forEach(el => el.style.display = isDark ? 'block' : 'none');
}

// ==========================================
// Sidebar
// ==========================================
function initSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggle) {
        toggle.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
        });
    }

    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-open');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            document.body.classList.remove('sidebar-open');
        });
    }

    // Restore sidebar state
    if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth > 768) {
        document.body.classList.add('sidebar-collapsed');
    }
}

// ==========================================
// Modals
// ==========================================
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        // Re-render Lucide icons in modal
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
        document.body.style.overflow = '';
    }
});

// Close modal on Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay[style*="display: flex"]').forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = '';
    }
});

// ==========================================
// Toast Notifications
// ==========================================
function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const icons = {
        success: 'check-circle',
        error: 'alert-circle',
        warning: 'alert-triangle',
        info: 'info',
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i data-lucide="${icons[type] || 'info'}"></i>
        <span>${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i data-lucide="x"></i>
        </button>
    `;

    container.appendChild(toast);
    if (typeof lucide !== 'undefined') lucide.createIcons();

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(40px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ==========================================
// API Fetch Wrapper
// ==========================================
async function api(url, options = {}) {
    const defaults = {
        headers: {},
    };

    // Add CSRF for POST/PUT/DELETE
    if (['POST', 'PUT', 'DELETE'].includes(options.method?.toUpperCase())) {
        if (options.body instanceof FormData) {
            options.body.append('csrf_token', window.QUANTSTOCK?.csrf || '');
        } else if (typeof options.body === 'object' && !(options.body instanceof FormData)) {
            options.body = JSON.stringify({ ...options.body, csrf_token: window.QUANTSTOCK?.csrf || '' });
            defaults.headers['Content-Type'] = 'application/json';
        }
    }

    const config = {
        ...defaults,
        ...options,
        headers: { ...defaults.headers, ...options.headers },
    };

    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        if (!response.ok && response.status === 401) {
            window.location.href = 'login.php';
            return null;
        }

        return data;
    } catch (error) {
        console.error('API Error:', error);
        showToast('Network error. Please check your connection.', 'error');
        return { success: false, message: 'Network error' };
    }
}

// ==========================================
// Pagination
// ==========================================
function goToPage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('p', page);
    window.location.href = url.toString();
}

// ==========================================
// Format Helpers
// ==========================================
function formatCurrency(amount) {
    const symbol = window.QUANTSTOCK?.currency || '₹';
    return symbol + parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatNumber(num) {
    return parseInt(num || 0).toLocaleString();
}

// ==========================================
// Chart Defaults
// ==========================================
function getChartDefaults() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94A3B8' : '#475569';
    const gridColor = isDark ? 'rgba(148, 163, 184, 0.08)' : 'rgba(0, 0, 0, 0.06)';
    
    return {
        textColor,
        gridColor,
        font: { family: "'Inter', sans-serif" },
        colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#6366F1', '#EC4899', '#14B8A6'],
    };
}

function applyChartDefaults() {
    const defaults = getChartDefaults();
    Chart.defaults.font.family = defaults.font.family;
    Chart.defaults.color = defaults.textColor;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
    Chart.defaults.plugins.legend.labels.padding = 16;
    Chart.defaults.plugins.legend.labels.font = { size: 12, family: defaults.font.family };
    Chart.defaults.elements.line.tension = 0.4;
    Chart.defaults.elements.line.borderWidth = 2;
    Chart.defaults.elements.point.radius = 3;
    Chart.defaults.elements.point.hoverRadius = 6;
    Chart.defaults.scale.grid = { color: defaults.gridColor, drawBorder: false };
}

// ==========================================
// Init
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSidebar();

    // Theme toggle button
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    // Apply chart defaults if Chart.js loaded
    if (typeof Chart !== 'undefined') {
        applyChartDefaults();
    }

    // Empty state buttons
    document.getElementById('emptyAddProduct')?.addEventListener('click', () => openModal('addProductModal'));
    document.getElementById('emptyRecordSale')?.addEventListener('click', () => openModal('recordSaleModal'));
    document.getElementById('emptyForecastBtn')?.addEventListener('click', () => runForecast?.());
    document.getElementById('emptyOptBtn')?.addEventListener('click', () => runOptimization?.());
});
