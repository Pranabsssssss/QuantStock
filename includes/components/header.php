<?php
/**
 * QuantStock — Header Component
 * 
 * Top bar with search, date, theme toggle, and action buttons.
 */

$user = getCurrentUser();
$businessName = getSetting('business_name', 'QuantStock');
$today = date('l, j F Y');
?>

<header class="main-header" id="mainHeader">
    <div class="header-left">
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu">
            <i data-lucide="menu"></i>
        </button>
        <div class="header-search">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" placeholder="Search anything..." id="globalSearch" class="search-input" autocomplete="off">
            <kbd class="search-shortcut">⌘K</kbd>
        </div>
    </div>

    <div class="header-right">
        <span class="header-date"><?= $today ?></span>
        
        <button class="header-icon-btn" id="themeToggle" aria-label="Toggle theme">
            <i data-lucide="sun" class="theme-icon-light"></i>
            <i data-lucide="moon" class="theme-icon-dark"></i>
        </button>

        <button class="header-icon-btn" id="notificationBtn" aria-label="Notifications">
            <i data-lucide="bell"></i>
            <span class="notification-dot" id="notifDot" style="display:none"></span>
        </button>

        <div class="header-user" id="headerUser">
            <div class="header-avatar">
                <?php if ($user && $user['avatar']): ?>
                    <img src="<?= e($user['avatar']) ?>" alt="<?= e($user['name']) ?>">
                <?php else: ?>
                    <span><?= $user ? strtoupper(substr($user['name'], 0, 1)) : 'A' ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
