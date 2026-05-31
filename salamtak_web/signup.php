<?php
require_once 'config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: signup.php");
    exit();
}

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $national_id = trim($_POST['national_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($national_id) || empty($name) || empty($email) || empty($phone) || empty($address) || empty($password)) {
        $error = t('required');
    } elseif (strlen($national_id) !== 14 || !ctype_digit($national_id)) {
        $error = t('must_be_14_digits');
    } elseif ($password !== $confirm_password) {
        $error = t('passwords_do_not_match');
    } else {
        // Create Firebase Auth account
        $fake_email = $national_id . '@salamtak.com';
        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . FIREBASE_API_KEY;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $fake_email,
            'password' => $password,
            'returnSecureToken' => true
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $uid = $data['localId'];
            
            // Create Firestore user document
            $user_data = [
                'nationalId' => $national_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'userType' => 'user'
            ];
            
            // Use the UID as document ID
            $url = FIRESTORE_URL . '/users/' . $uid;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            
            $fields = [];
            foreach ($user_data as $key => $value) {
                $fields[$key] = convertToFirestoreValue($value);
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['fields' => $fields]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            curl_exec($ch);
            curl_close($ch);
            
            // Log them in
            $_SESSION['user_id'] = $uid;
            $_SESSION['user_type'] = 'user';
            $_SESSION['national_id'] = $national_id;
            $_SESSION['name'] = $name;
            redirect('home.php');
        } else {
            $error_data = json_decode($response, true);
            if (isset($error_data['error']['message']) && $error_data['error']['message'] === 'EMAIL_EXISTS') {
                $error = "National ID already registered";
            } else {
                $error = "Registration failed. Please try again.";
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
    <title><?= t('app_name') ?> - <?= t('sign_up') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #0f1d3f !important;
            background-color: #0f1d3f !important;
        }
        body.signup-page {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #0f1d3f !important;
        }
        body.signup-page::before,
        body.signup-page::after {
            display: none !important;
        }
        .signup-container {
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
        .signup-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 20px 60px;
        }
        .signup-card {
            width: 100%;
            max-width: 520px;
            padding: 32px 28px !important;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .signup-card h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            text-align: center;
        }
        .signup-card .subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 24px;
        }
        .signup-form .form-group {
            margin-bottom: 14px !important;
        }
        .signup-form .form-group label {
            font-size: 12px !important;
            margin-bottom: 4px !important;
        }
        .signup-form input,
        .signup-form textarea {
            padding: 8px 12px !important;
            font-size: 13px !important;
        }
        .signup-form button {
            padding: 10px 20px !important;
            font-size: 14px !important;
            margin-top: 4px !important;
            border-radius: 12px !important;
        }
        .form-footer {
            text-align: center;
            margin-top: 20px !important;
            padding-top: 20px !important;
            border-top: 2px solid var(--border-light);
        }
        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .form-footer .login-link {
            padding: 2px 0;
            border-bottom: 2px solid transparent;
        }
        .form-footer .login-link:hover {
            text-decoration: underline;
            color: var(--primary);
        }
        .alert {
            padding: 10px 12px !important;
            font-size: 13px !important;
            margin-bottom: 12px !important;
        }
    </style>
</head>
<body class="signup-page">
    <!-- Back Arrow -->
    <a href="home.php" class="back-arrow">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
    </a>
    
    <div class="signup-container">
        <div class="signup-card">
            <!-- Title and Tagline -->
            <div style="text-align: center; margin-bottom: 24px;">
                <h1 style="font-size: 28px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;"><?= t('create_account') ?></h1>
                <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 0;">Join thousands making roads safer</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="signup-form">
                <div class="form-group">
                    <label><?= t('national_id') ?></label>
                    <input type="text" name="national_id" required maxlength="14" pattern="\d{14}"
                           placeholder="Enter your 14-digit National ID"
                           value="<?= htmlspecialchars($_POST['national_id'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('full_name') ?></label>
                    <input type="text" name="name" required minlength="3"
                           placeholder="Enter your full name"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('email') ?></label>
                    <input type="email" name="email" required
                           placeholder="Enter your email address"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('phone_number') ?></label>
                    <input type="tel" name="phone" required pattern="\d{10,15}"
                           placeholder="Enter your phone number"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('address') ?></label>
                    <textarea name="address" required minlength="5" rows="3" 
                              placeholder="Enter your full address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><?= t('password') ?></label>
                    <input type="password" name="password" required minlength="6"
                           placeholder="Create a strong password">
                </div>
                
                <div class="form-group">
                    <label><?= t('confirm_password') ?></label>
                    <input type="password" name="confirm_password" required minlength="6"
                           placeholder="Re-enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    <?= t('create_account') ?>
                </button>
            </form>
                
                <div class="form-footer">
                    <span style="color: var(--text-secondary);">Already have an account? </span>
                    <a href="login.php" class="login-link" style="color: var(--primary); text-decoration: none; font-weight: 700;">Sign In</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
