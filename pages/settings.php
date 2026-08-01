<?php
/**
 * QuantStock — Settings Page
 */

$settingsModel = new Settings();
$allSettings = $settingsModel->getAll();
$currentUser = getCurrentUser();
?>

<div class="page-header">
    <div class="page-header-left">
        <h2>Settings</h2>
        <p>Configure your application preferences</p>
    </div>
</div>

<div class="settings-grid">
    <!-- Settings Navigation -->
    <nav class="settings-nav">
        <button class="settings-nav-item active" onclick="showSettingsTab('general', this)">
            <i data-lucide="settings"></i> General
        </button>
        <button class="settings-nav-item" onclick="showSettingsTab('ai', this)">
            <i data-lucide="bot"></i> AI Configuration
        </button>
        <button class="settings-nav-item" onclick="showSettingsTab('profile', this)">
            <i data-lucide="user"></i> Profile
        </button>
        <button class="settings-nav-item" onclick="showSettingsTab('appearance', this)">
            <i data-lucide="palette"></i> Appearance
        </button>
    </nav>

    <!-- Settings Panels -->
    <div>
        <!-- General Settings -->
        <div class="settings-panel settings-tab" id="tab-general">
            <form id="generalSettingsForm">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                
                <div class="settings-section">
                    <h3 class="settings-section-title">Business Information</h3>
                    <p class="settings-section-desc">Configure your business details.</p>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Business Name</label>
                            <input type="text" class="form-control" name="business_name" value="<?= e($allSettings['business_name'] ?? 'QuantStock') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" class="form-control" name="currency" value="<?= e($allSettings['currency'] ?? '₹') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Currency Code</label>
                            <input type="text" class="form-control" name="currency_code" value="<?= e($allSettings['currency_code'] ?? 'INR') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Timezone</label>
                            <input type="text" class="form-control" name="timezone" value="<?= e($allSettings['timezone'] ?? 'Asia/Kolkata') ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3 class="settings-section-title">Low Stock Threshold</h3>
                    <p class="settings-section-desc">Default minimum stock level for new products.</p>
                    <div class="form-group" style="max-width:200px;">
                        <input type="number" class="form-control" name="low_stock_threshold" value="<?= e($allSettings['low_stock_threshold'] ?? '10') ?>" min="1">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i> Save Changes
                </button>
            </form>
        </div>

        <!-- AI Settings -->
        <div class="settings-panel settings-tab" id="tab-ai" style="display:none;">
            <form id="aiSettingsForm">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                
                <div class="settings-section">
                    <h3 class="settings-section-title">AI Provider</h3>
                    <p class="settings-section-desc">Configure your AI service provider and API key.</p>
                    
                    <div class="form-group">
                        <label class="form-label">AI Provider</label>
                        <select class="form-control" name="ai_provider">
                            <option value="groq" <?= ($allSettings['ai_provider'] ?? '') === 'groq' ? 'selected' : '' ?>>Groq</option>
                            <option value="openai" <?= ($allSettings['ai_provider'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">API Key</label>
                        <div class="form-input-wrapper" style="position:relative;">
                            <input type="password" class="form-control" name="ai_api_key" id="aiApiKey" value="<?= e($allSettings['ai_api_key'] ?? '') ?>" placeholder="Enter your API key">
                            <button type="button" style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-tertiary);" onclick="toggleApiKeyVisibility()">
                                <i data-lucide="eye" id="apiKeyEyeIcon"></i>
                            </button>
                        </div>
                        <p class="form-hint">Your API key is stored securely in the database.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">AI Model</label>
                        <input type="text" class="form-control" name="ai_model" value="<?= e($allSettings['ai_model'] ?? 'llama-3.3-70b-versatile') ?>">
                    </div>
                </div>

                <div class="settings-section">
                    <h3 class="settings-section-title">Prediction Settings</h3>
                    <p class="settings-section-desc">Configure how often AI predictions are generated.</p>
                    
                    <div class="form-group">
                        <label class="form-label">Prediction Frequency</label>
                        <select class="form-control" name="prediction_frequency">
                            <option value="manual" <?= ($allSettings['prediction_frequency'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual Only</option>
                            <option value="daily" <?= ($allSettings['prediction_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= ($allSettings['prediction_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i> Save AI Settings
                </button>
            </form>
        </div>

        <!-- Profile Settings -->
        <div class="settings-panel settings-tab" id="tab-profile" style="display:none;">
            <form id="profileSettingsForm">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                
                <div class="settings-section">
                    <h3 class="settings-section-title">Profile Information</h3>
                    <p class="settings-section-desc">Update your personal information.</p>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?= e($currentUser['name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= e($currentUser['email'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3 class="settings-section-title">Change Password</h3>
                    <p class="settings-section-desc">Leave blank to keep current password.</p>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Enter new password">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm password">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i> Update Profile
                </button>
            </form>
        </div>

        <!-- Appearance Settings -->
        <div class="settings-panel settings-tab" id="tab-appearance" style="display:none;">
            <div class="settings-section">
                <h3 class="settings-section-title">Theme</h3>
                <p class="settings-section-desc">Choose your preferred theme.</p>
                
                <div style="display:flex; gap:1rem;">
                    <div class="report-card" style="flex:1; cursor:pointer; padding:1.25rem;" onclick="setTheme('dark')" id="themeDarkCard">
                        <div style="width:100%; height:60px; background:#0A0A0F; border-radius:8px; border:2px solid var(--border-primary); margin-bottom:0.75rem;"></div>
                        <h3 style="font-size:0.9rem;">Dark Mode</h3>
                        <p style="font-size:0.8rem;">Easier on the eyes</p>
                    </div>
                    <div class="report-card" style="flex:1; cursor:pointer; padding:1.25rem;" onclick="setTheme('light')" id="themeLightCard">
                        <div style="width:100%; height:60px; background:#F8FAFC; border-radius:8px; border:2px solid #E2E8F0; margin-bottom:0.75rem;"></div>
                        <h3 style="font-size:0.9rem;">Light Mode</h3>
                        <p style="font-size:0.8rem;">Classic appearance</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
