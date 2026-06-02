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
        
        .signup-card {
            width: 100%;
            max-width: 700px;
            background: linear-gradient(145deg, #e8e5da 0%, #d9d6cb 100%);
            border-radius: 32px;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4);
            padding: 45px 40px;
            position: relative;
        }
        
        .card-title {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .card-title h1 {
            font-size: 32px;
            font-weight: 900;
            color: #0f1d3f;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .card-title p {
            font-size: 15px;
            color: #6b7280;
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
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
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
        
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
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
            margin-top: 24px;
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
            .signup-card {
                max-width: 100%;
                padding: 35px 28px;
            }
            
            .card-title h1 {
                font-size: 26px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
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
        <div class="signup-card">
            <div class="card-title">
                <h1><?= t('create_account') ?></h1>
                <p>Join thousands making roads safer</p>
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
                <div class="form-grid">
                    <div class="form-group">
                        <label><?= t('national_id') ?></label>
                        <input type="text" name="national_id" class="form-control" 
                               required maxlength="14" pattern="\d{14}"
                               placeholder="14-digit National ID"
                               value="<?= htmlspecialchars($_POST['national_id'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><?= t('full_name') ?></label>
                        <input type="text" name="name" class="form-control" 
                               required minlength="3"
                               placeholder="Your full name"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><?= t('email') ?></label>
                        <input type="email" name="email" class="form-control" 
                               required
                               placeholder="your@email.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><?= t('phone_number') ?></label>
                        <input type="tel" name="phone" class="form-control" 
                               required pattern="\d{10,15}"
                               placeholder="Phone number"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><?= t('password') ?></label>
                        <input type="password" name="password" class="form-control" 
                               required minlength="6"
                               placeholder="Create password">
                    </div>
                    
                    <div class="form-group">
                        <label><?= t('confirm_password') ?></label>
                        <input type="password" name="confirm_password" class="form-control" 
                               required minlength="6"
                               placeholder="Confirm password">
                    </div>
                    
                    <div class="form-group full-width">
                        <label><?= t('address') ?></label>
                        <textarea name="address" class="form-control" 
                                  required minlength="5" rows="3" 
                                  placeholder="Your full address"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    <?= t('create_account') ?>
                </button>
                
                <div class="form-footer">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
