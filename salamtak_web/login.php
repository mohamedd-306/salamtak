<?php
require_once 'config.php';

// Handle language change first (before any output)
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    redirect('login.php');
}

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // Can be National ID or Work ID
    $password = trim($_POST['password'] ?? '');
    $user_type = $_POST['user_type'] ?? 'user'; // Get selected user type
    
    if (empty($identifier) || empty($password)) {
        $error = t('required');
    } else {
        // Admin login with Work ID (9 digits)
        if ($user_type === 'admin') {
            // Check hardcoded admin credentials (synchronized with mobile app)
            $adminData = verifyAdminCredentials($identifier, $password);
            
            if ($adminData) {
                // Set session variables with admin role
                $_SESSION['user_id'] = 'admin-' . $identifier;
                $_SESSION['user_type'] = 'admin';
                $_SESSION['admin_role'] = $adminData['role']; // 'moderator' or 'product_manager'
                $_SESSION['work_id'] = $identifier;
                $_SESSION['name'] = $adminData['name'];
                
                // Redirect based on role
                if ($adminData['role'] === 'moderator') {
                    redirect('admin/dashboard.php'); // Reports
                } else {
                    redirect('admin/products.php'); // Products/Orders
                }
            }
            // Check Firebase for admin with Work ID
            else {
                $email = 'admin-' . $identifier . '@salamtak.com';
                $uid = verifyFirebasePassword($email, $password);
                
                if ($uid) {
                    $user = getFirestoreDocument('users', $uid);
                    
                    if ($user && ($user['userType'] ?? '') === 'admin') {
                        $_SESSION['user_id'] = $uid;
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['admin_role'] = $user['adminRole'] ?? 'product_manager';
                        $_SESSION['work_id'] = $user['workId'] ?? $identifier;
                        $_SESSION['name'] = $user['name'] ?? 'Administrator';
                        
                        // Redirect based on role
                        if (($_SESSION['admin_role'] ?? '') === 'moderator') {
                            redirect('admin/dashboard.php'); // Reports
                        } else {
                            redirect('admin/products.php'); // Products/Orders
                        }
                    } else {
                        $error = t('invalid_credentials');
                    }
                } else {
                    $error = t('invalid_credentials');
                }
            }
        }
        // User login with National ID (14 digits)
        else {
            // Check for hardcoded test user
            if ($identifier === '11111111111111' && $password === 'user123456') {
                $_SESSION['user_id'] = 'user-hardcoded';
                $_SESSION['user_type'] = 'user';
                $_SESSION['national_id'] = $identifier;
                $_SESSION['name'] = 'Test User';
                redirect('home.php');
            }
            // Check Firebase Auth for regular user
            else {
                $email = $identifier . '@salamtak.com';
                $uid = verifyFirebasePassword($email, $password);
                
                if ($uid) {
                    $user = getFirestoreDocument('users', $uid);
                    
                    if ($user && ($user['userType'] ?? 'user') === 'user') {
                        $_SESSION['user_id'] = $uid;
                        $_SESSION['user_type'] = 'user';
                        $_SESSION['national_id'] = $user['nationalId'] ?? $identifier;
                        $_SESSION['name'] = $user['name'] ?? 'User';
                        redirect('home.php');
                    } else {
                        $error = t('invalid_credentials');
                    }
                } else {
                    $error = t('invalid_credentials');
                }
            }
        }
    }
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('sign_in') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #0f1d3f !important;
            background-color: #0f1d3f !important;
        }
        body.login-page {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #0f1d3f !important;
        }
        body.login-page::before,
        body.login-page::after {
            display: none !important;
        }
        .login-container {
            background: transparent !important;
        }
        .back-arrow {
            position: fixed;
            top: 24px;
            left: 24px;
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1000;
            text-decoration: none;
            color: white;
        }
        .back-arrow:hover {
            transform: translateX(-4px);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        }
        .login-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 20px 60px;
        }
        .login-header {
            width: 100%;
            max-width: 380px;
            margin-bottom: 24px;
            text-align: center;
        }
        .login-header .logo-container {
            margin-bottom: 16px !important;
        }
        .login-header .logo-icon {
            width: 80px !important;
            height: 80px !important;
            margin: 0 auto;
        }
        .login-header .app-title {
            font-size: 32px !important;
            margin-bottom: 8px !important;
            color: white;
            font-weight: 800;
        }
        .login-header .app-tagline {
            font-size: 14px !important;
            color: rgba(255, 255, 255, 0.9);
        }
        .login-card {
            width: 100%;
            max-width: 800px;
            padding: 40px 48px !important;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .login-card h2 {
            font-size: 32px !important;
            margin-bottom: 12px !important;
            color: var(--text-primary);
        }
        .login-card .subtitle {
            font-size: 16px !important;
            margin-bottom: 24px !important;
        }
        .login-form .form-group {
            margin-bottom: 20px !important;
        }
        .login-form .form-group label {
            font-size: 14px !important;
            margin-bottom: 8px !important;
        }
        .login-form input {
            padding: 14px 16px !important;
            font-size: 15px !important;
        }
        .login-form button {
            padding: 16px 24px !important;
            font-size: 16px !important;
            margin-top: 8px !important;
            border-radius: 12px !important;
        }
        .form-footer {
            margin-top: 20px !important;
            padding-top: 20px !important;
        }
        .form-footer {
            text-align: center;
            margin-top: 28px !important;
            padding-top: 24px !important;
            border-top: 2px solid var(--border-light);
        }
        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .form-footer .signup-link {
            padding: 2px 0;
            border-bottom: 2px solid transparent;
        }
        .form-footer .signup-link:hover {
            text-decoration: underline;
            color: var(--primary);
        }
        .alert {
            padding: 14px 16px !important;
            font-size: 15px !important;
            margin-bottom: 20px !important;
        }
    </style>
</head>
<body class="login-page">
    <!-- Back Arrow -->
    <a href="home.php" class="back-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
    </a>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Title and Tagline -->
            <div style="text-align: center; margin-bottom: 32px;">
                <h1 style="font-size: 36px; font-weight: 800; color: var(--text-primary); margin-bottom: 12px;"><?= t('app_name') ?></h1>
                <p style="font-size: 16px; color: var(--text-secondary); margin-bottom: 0;"><?= t('tagline') ?></p>
            </div>
            
            <h2><?= t('welcome_back') ?></h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label><?= t('login_as') ?></label>
                    <div style="display: flex; gap: 24px; margin-bottom: 16px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="radio" name="user_type" value="user" checked onchange="updateLoginForm()" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary);">
                            <span><?= t('user') ?></span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500;">
                            <input type="radio" name="user_type" value="admin" onchange="updateLoginForm()" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary);">
                            <span><?= t('admin') ?></span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label id="idLabel"><?= t('national_id') ?></label>
                    <input type="text" name="identifier" id="identifierInput" required maxlength="14" pattern="\d{14}" 
                           placeholder="<?= t('enter_national_id') ?>" value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('password') ?></label>
                    <input type="password" name="password" required minlength="6" placeholder="<?= t('enter_password') ?>">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <?= t('sign_in') ?>
                </button>
            </form>
            
            <script>
                const translations = {
                    en: {
                        national_id: '<?= addslashes(t('national_id')) ?>',
                        work_id: '<?= addslashes(t('work_id')) ?>',
                        enter_national_id: '<?= addslashes(t('enter_national_id')) ?>',
                        enter_work_id: '<?= addslashes(t('enter_work_id')) ?>'
                    },
                    ar: {
                        national_id: 'رقم الهوية الوطنية',
                        work_id: 'رقم العمل',
                        enter_national_id: 'أدخل رقم الهوية الوطنية المكون من 14 رقماً',
                        enter_work_id: 'أدخل رقم العمل المكون من 9 أرقام'
                    }
                };
                
                const currentLang = '<?= $lang ?>';
                
                function updateLoginForm() {
                    const userType = document.querySelector('input[name="user_type"]:checked').value;
                    const idLabel = document.getElementById('idLabel');
                    const identifierInput = document.getElementById('identifierInput');
                    
                    if (userType === 'admin') {
                        idLabel.textContent = translations[currentLang].work_id;
                        identifierInput.placeholder = translations[currentLang].enter_work_id;
                        identifierInput.maxLength = 9;
                        identifierInput.pattern = '\\d{9}';
                    } else {
                        idLabel.textContent = translations[currentLang].national_id;
                        identifierInput.placeholder = translations[currentLang].enter_national_id;
                        identifierInput.maxLength = 14;
                        identifierInput.pattern = '\\d{14}';
                    }
                    identifierInput.value = '';
                }
            </script>
            
            <div class="form-footer">
                <span style="color: var(--text-secondary);">Don't have an account? </span>
                <a href="signup.php" class="signup-link">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>
