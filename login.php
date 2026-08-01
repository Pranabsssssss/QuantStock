<?php
/**
 * QuantStock — Login Page
 * 
 * Beautiful split-layout login with dark/light mode support.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Initialize database
initApp();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$email = '';

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrfToken)) {
        $error = 'Security token invalid. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $result = attemptLogin($email, $password);
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — QuantStock</title>
    <meta name="description" content="Sign in to QuantStock — Quantum AI-Powered Inventory Forecasting & Optimization">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .login-page { display: flex; min-height: 100vh; }
        
        .login-brand {
            flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 3rem; background: var(--bg-gradient);
            position: relative; overflow: hidden;
        }
        .login-brand::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 30% 50%, rgba(59, 130, 246, 0.12) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(139, 92, 246, 0.08) 0%, transparent 40%);
        }
        .login-brand-content { position: relative; z-index: 1; text-align: center; max-width: 420px; }
        .login-brand .logo-icon {
            width: 72px; height: 72px; border-radius: 20px;
            background: var(--accent-gradient); display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem; box-shadow: 0 8px 32px rgba(59, 130, 246, 0.3);
        }
        .login-brand .logo-icon svg { width: 36px; height: 36px; color: white; }
        .login-brand h1 { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem; letter-spacing: -0.03em; }
        .login-brand p { font-size: 1rem; color: var(--text-secondary); line-height: 1.6; }
        .brand-features { margin-top: 3rem; text-align: left; }
        .brand-feature { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem; }
        .brand-feature-icon { width: 36px; height: 36px; border-radius: 10px; background: var(--bg-tertiary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .brand-feature-icon svg { width: 18px; height: 18px; color: var(--accent); }

        .login-form-section {
            flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 3rem; background: var(--bg-primary); max-width: 520px;
        }
        .login-form-wrapper { width: 100%; max-width: 380px; }
        .login-form-header { margin-bottom: 2rem; }
        .login-form-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; }
        .login-form-header p { font-size: 0.9rem; color: var(--text-secondary); }

        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .form-input-wrapper { position: relative; }
        .form-input {
            width: 100%; padding: 0.75rem 1rem; padding-left: 2.75rem;
            background: var(--bg-secondary); border: 1.5px solid var(--border-primary);
            border-radius: 12px; color: var(--text-primary); font-size: 0.95rem;
            font-family: 'Inter', sans-serif; transition: all 0.2s ease;
        }
        .form-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .form-input::placeholder { color: var(--text-tertiary); }
        .form-input-icon {
            position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%);
            color: var(--text-tertiary); pointer-events: none;
        }
        .form-input-icon svg { width: 18px; height: 18px; }
        .password-toggle {
            position: absolute; right: 0.875rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: var(--text-tertiary);
            padding: 4px; transition: color 0.2s;
        }
        .password-toggle:hover { color: var(--text-primary); }
        .password-toggle svg { width: 18px; height: 18px; }

        .form-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .remember-me { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
        .remember-me input[type="checkbox"] {
            width: 16px; height: 16px; accent-color: var(--accent);
            border-radius: 4px; cursor: pointer;
        }
        .remember-me span { font-size: 0.85rem; color: var(--text-secondary); }
        .forgot-link { font-size: 0.85rem; color: var(--accent); text-decoration: none; font-weight: 500; }
        .forgot-link:hover { text-decoration: underline; }

        .login-btn {
            width: 100%; padding: 0.875rem; background: var(--accent-gradient);
            color: white; border: none; border-radius: 12px; font-size: 1rem;
            font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        .login-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(59, 130, 246, 0.35); }
        .login-btn:active { transform: translateY(0); }

        .login-footer { margin-top: 2rem; text-align: center; font-size: 0.8rem; color: var(--text-tertiary); }
        
        .alert-error {
            padding: 0.75rem 1rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 10px; color: #ef4444; font-size: 0.875rem; margin-bottom: 1.25rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .alert-error svg { width: 16px; height: 16px; flex-shrink: 0; }

        .login-theme-toggle {
            position: fixed; top: 1.5rem; right: 1.5rem;
            background: var(--bg-secondary); border: 1px solid var(--border-primary);
            border-radius: 10px; padding: 0.5rem; cursor: pointer; color: var(--text-secondary);
            transition: all 0.2s; z-index: 100;
        }
        .login-theme-toggle:hover { color: var(--text-primary); background: var(--bg-tertiary); }
        .login-theme-toggle svg { width: 18px; height: 18px; }

        @media (max-width: 768px) {
            .login-brand { display: none; }
            .login-form-section { max-width: 100%; }
        }
    </style>
</head>
<body>
    <button class="login-theme-toggle" id="loginThemeToggle" aria-label="Toggle theme">
        <i data-lucide="sun" class="theme-icon-light"></i>
        <i data-lucide="moon" class="theme-icon-dark"></i>
    </button>

    <div class="login-page">
        <!-- Brand Section -->
        <div class="login-brand">
            <div class="login-brand-content">
                <div class="logo-icon">
                    <i data-lucide="brain-circuit"></i>
                </div>
                <h1>QuantStock</h1>
                <p>Quantum AI-Powered Inventory Intelligence</p>

                <div class="brand-features">
                    <div class="brand-feature">
                        <div class="brand-feature-icon"><i data-lucide="trending-up"></i></div>
                        <span>ML-Powered Demand Forecasting</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon"><i data-lucide="bar-chart-3"></i></div>
                        <span>Real-time Analytics Dashboard</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon"><i data-lucide="bot"></i></div>
                        <span>Quantum AI Advisor</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon"><i data-lucide="shield-check"></i></div>
                        <span>Inventory Risk Assessment</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="login-form-section">
            <div class="login-form-wrapper">
                <div class="login-form-header">
                    <h2>Welcome back 👋</h2>
                    <p>Sign in to manage your inventory with AI</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-error">
                        <i data-lucide="alert-circle"></i>
                        <span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email address</label>
                        <div class="form-input-wrapper">
                            <span class="form-input-icon"><i data-lucide="mail"></i></span>
                            <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email" value="<?= e($email) ?>" required autocomplete="email">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="form-input-wrapper">
                            <span class="form-input-icon"><i data-lucide="lock"></i></span>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" checked>
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-btn">Sign In</button>
                </form>

                <div class="login-footer">
                    <p>&copy; <?= date('Y') ?> QuantStock. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();

        // Theme toggle
        const savedTheme = localStorage.getItem('quantstock-theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcons();

        document.getElementById('loginThemeToggle').addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('quantstock-theme', next);
            updateThemeIcons();
        });

        function updateThemeIcons() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            document.querySelectorAll('.theme-icon-light').forEach(el => el.style.display = isDark ? 'none' : 'block');
            document.querySelectorAll('.theme-icon-dark').forEach(el => el.style.display = isDark ? 'block' : 'none');
        }

        // Password toggle
        document.getElementById('passwordToggle').addEventListener('click', () => {
            const input = document.getElementById('password');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
        });
    </script>
</body>
</html>
