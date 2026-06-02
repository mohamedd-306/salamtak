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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 50%, #0f1d3f 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.02) 0%, transparent 50%);
            pointer-events: none;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
        }
        
        .back-arrow {
            position: fixed;
            top: 24px;
            left: 24px;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            z-index: 100;
        }
        
        .back-arrow:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(-4px);
        }
        
        .login-card {
            width: 100%;
            max-width: 480px;
            background: linear-gradient(145deg, #e8e5da 0%, #d9d6cb 100%);
            border-radius: 32px;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4);
            padding: 50px 45px;
            position: relative;
        }
        
        .card-title {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .card-title h1 {
            font-size: 36px;
            font-weight: 900;
            color: #0f1d3f;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .card-title p {
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 20px;
        }
        
        .card-title h2 {
            font-size: 22px;
            font-weight: 700;
            color: #4a5568;
            margin-top: 12px;
        }
        
        .alert {
            padding: 14px 18px;
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 12px;
            color: #c00;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #4a5568;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .radio-group {
            display: flex;
            gap: 24px;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        
        .radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            color: #4a5568;
        }
        
        .radio-label input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #0f1d3f;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 18px;
            font-size: 15px;
            border: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-family: inherit;
            background: rgba(255, 255, 255, 0.4);
            color: #0f1d3f;
            font-weight: 500;
        }
        
        .form-control:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.6);
            box-shadow: 0 0 0 3px rgba(15, 29, 63, 0.15);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        .btn {
            width: 100%;
            padding: 16px 24px;
            font-size: 17px;
            font-weight: 700;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 12px;
            background: #0f1d3f;
            color: white;
        }
        
        .btn:hover {
            background: #1a2d5a;
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -8px rgba(15, 29, 63, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .form-footer {
            margin-top: 28px;
            text-align: center;
            font-size: 15px;
            color: #6b7280;
        }
        
        .form-footer a {
            color: #0f1d3f;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .login-card {
                max-width: 100%;
                padding: 40px 30px;
            }
            
            .card-title h1 {
                font-size: 28px;
            }
            
            .back-arrow {
                top: 16px;
                left: 16px;
                width: 44px;
                height: 44px;
            }
        }
    </style>
</head>
<body>
    <a href="home.php" class="back-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
    </a>
    
    <div class="page-wrapper">
        <div class="login-card">
            <div class="card-title">
                <h1><?= t('app_name') ?></h1>
                <p><?= t('tagline') ?></p>
                <h2><?= t('welcome_back') ?></h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label><?= t('login_as') ?></label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="user_type" value="user" checked onchange="updateLoginForm()">
                            <span><?= t('user') ?></span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="user_type" value="admin" onchange="updateLoginForm()">
                            <span><?= t('admin') ?></span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label id="idLabel"><?= t('national_id') ?></label>
                    <input type="text" name="identifier" id="identifierInput" class="form-control" 
                           required maxlength="14" pattern="\d{14}" 
                           placeholder="<?= t('enter_national_id') ?>" 
                           value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('password') ?></label>
                    <input type="password" name="password" class="form-control" 
                           required minlength="6" 
                           placeholder="<?= t('enter_password') ?>">
                </div>
                
                <button type="submit" class="btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <?= t('sign_in') ?>
                </button>
                
                <div class="form-footer">
                    Don't have an account? <a href="signup.php">Sign Up</a>
                </div>
            </form>
        </div>
    </div>
    
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
</body>
</html>
