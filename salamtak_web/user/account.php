<?php
require_once '../config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: account.php");
    exit();
}

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle profile picture upload FIRST (before getting user data)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($file_ext, $allowed_ext)) {
        $file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
            // Store clean path (no ../) in Firestore
            $profile_picture_path = 'uploads/profiles/' . $file_name;
            
            $updated = updateFirestoreDocument('users', $user_id, ['profilePicture' => $profile_picture_path]);
            
            if ($updated) {
                // Update session so header/nav reflects new picture immediately
                $_SESSION['profile_picture'] = $profile_picture_path;
                header("Location: account.php?success=picture");
                exit();
            } else {
                $error = 'File uploaded but failed to save to database. Check Firestore connection.';
            }
        } else {
            $error = 'Failed to move uploaded file. Check folder permissions.';
        }
    } else {
        $error = 'Invalid file type. Only JPG, PNG, GIF allowed.';
    }
}

// Show success message after redirect
if (isset($_GET['success']) && $_GET['success'] === 'picture') {
    $success = 'Profile picture updated successfully!';
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($name)) {
        $error = 'Name is required';
    } else {
        $update_data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ];
        
        if (updateFirestoreDocument('users', $user_id, $update_data)) {
            $_SESSION['name'] = $name;
            $success = 'Profile updated successfully!';
        } else {
            $error = 'Failed to update profile';
        }
    }
}

// Get user data from Firestore
$user = getFirestoreDocument('users', $user_id);

// Get user's orders
$all_orders = queryFirestoreCollection('orders');
$user_orders = [];
if ($all_orders) {
    foreach ($all_orders as $order) {
        if (isset($order['userId']) && $order['userId'] === $user_id) {
            $user_orders[] = $order;
        }
    }
    // Sort by date (newest first)
    usort($user_orders, function($a, $b) {
        return strtotime($b['createdAt']) - strtotime($a['createdAt']);
    });
}

// If no Firestore data, use session data (for hardcoded/test users)
if (!$user || empty($user['nationalId'])) {
    $user = [
        'name' => $_SESSION['name'] ?? 'User',
        'nationalId' => $_SESSION['national_id'] ?? 'N/A',
        'email' => 'Not set',
        'phone' => 'Not set',
        'address' => 'Not set',
        'profilePicture' => ''
    ];
} else {
    // Ensure all fields have default values if not set
    $user['name'] = $user['name'] ?? $_SESSION['name'] ?? 'User';
    $user['nationalId'] = $user['nationalId'] ?? $_SESSION['national_id'] ?? 'N/A';
    $user['email'] = $user['email'] ?? 'Not set';
    $user['phone'] = $user['phone'] ?? 'Not set';
    $user['address'] = $user['address'] ?? 'Not set';
    $user['profilePicture'] = $user['profilePicture'] ?? '';
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';
$page_title = t('account');
$page_subtitle = t('settings_preferences');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('account') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Navbar Styles */
        .landing-nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(15, 29, 63, 0.95);
            backdrop-filter: blur(20px);
            padding: 16px 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .landing-nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .landing-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: 700;
        }
        
        .landing-brand-logo {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .landing-nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }
        
        .landing-nav-link {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .landing-nav-link:hover {
            color: #FBBF24;
        }

        .profile-picture-section {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--surface);
            border-radius: var(--radius);
            border: 2px solid var(--border);
        }
        .profile-picture-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 16px;
        }
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            background: var(--background);
        }
        .profile-picture-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: 700;
            border: 4px solid var(--primary);
        }
        .upload-picture-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: var(--transition);
        }
        .upload-picture-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }
        .upload-picture-btn input {
            display: none;
        }
        .profile-info-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid var(--border);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 14px;
        }
        .info-value {
            font-weight: 500;
            color: var(--text-primary);
            font-size: 14px;
        }
        .edit-mode {
            display: none;
        }
        .edit-mode.active {
            display: block;
        }
        .view-mode.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container" style="padding-top: 120px; padding-bottom: 100px;">
        <?php 
        // Debug mode - show what's in database
        if (isset($_GET['debug'])) {
            echo "<div style='background: yellow; padding: 15px; margin-bottom: 20px; border: 3px solid red;'>";
            echo "<h3 style='color: red;'>DEBUG INFO:</h3>";
            echo "<strong>User ID:</strong> " . htmlspecialchars($user_id) . "<br>";
            echo "<strong>Profile Picture Path in DB:</strong> " . htmlspecialchars($user['profilePicture'] ?? 'NOT SET') . "<br>";
            $testPath = '../' . ($user['profilePicture'] ?? '');
            echo "<strong>Full Path:</strong> " . htmlspecialchars($testPath) . "<br>";
            echo "<strong>File Exists:</strong> " . (file_exists($testPath) ? 'YES ✓' : 'NO ✗') . "<br>";
            echo "<strong>Upload Dir:</strong> " . realpath('../uploads/profiles/') . "<br>";
            echo "</div>";
        }
        ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <!-- Profile Picture Section -->
        <div class="profile-picture-section">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="profile-picture-container">
                    <?php 
                    // Check if profile picture exists and file is accessible
                    $profilePictureSrc = '';
                    if (!empty($user['profilePicture'])) {
                        // The path in DB is already relative like 'uploads/profiles/filename.jpg'
                        // We need to add ../ to go up from user/ folder
                        $picPath = '../' . $user['profilePicture'];
                        if (file_exists($picPath)) {
                            $profilePictureSrc = '../' . $user['profilePicture'] . '?v=' . time();
                        }
                    }
                    ?>
                    
                    <?php if ($profilePictureSrc): ?>
                        <img src="<?= htmlspecialchars($profilePictureSrc) ?>" alt="Profile" class="profile-picture" id="profileImg">
                    <?php else: ?>
                        <div class="profile-picture-placeholder" id="placeholder">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <label class="upload-picture-btn" for="fileInput" title="Upload Profile Picture">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        <input type="file" name="profile_picture" id="fileInput" accept="image/*">
                    </label>
                </div>
                <h2 style="margin: 0 0 4px 0; font-size: 24px;"><?= htmlspecialchars($user['name']) ?></h2>
                <p style="color: var(--text-secondary); margin: 0; font-size: 14px;"><?= t('salamtak_user') ?></p>
            </form>
        </div>
        
        <!-- Profile Information -->
        <div class="profile-info-card view-mode" id="viewMode">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Profile Information</h3>
                <button onclick="toggleEditMode()" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    <?= t('edit') ?>
                </button>
            </div>
            
            <div class="info-row">
                <span class="info-label"><?= t('full_name') ?></span>
                <span class="info-value"><?= htmlspecialchars($user['name']) ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label"><?= t('national_id') ?></span>
                <span class="info-value"><?= htmlspecialchars($user['nationalId']) ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label"><?= t('email') ?></span>
                <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label"><?= t('phone_number') ?></span>
                <span class="info-value"><?= htmlspecialchars($user['phone']) ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label"><?= t('address') ?></span>
                <span class="info-value"><?= htmlspecialchars($user['address']) ?></span>
            </div>
        </div>
        
        <!-- Edit Profile Form -->
        <div class="profile-info-card edit-mode" id="editMode">
            <h3 style="margin: 0 0 20px 0; font-size: 18px; font-weight: 700;">Edit Profile</h3>
            
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-group">
                    <label><?= t('full_name') ?></label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($user['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('national_id') ?></label>
                    <input type="text" value="<?= htmlspecialchars($user['nationalId']) ?>" disabled style="background: var(--background); cursor: not-allowed;">
                    <p class="form-hint">National ID cannot be changed</p>
                </div>
                
                <div class="form-group">
                    <label><?= t('email') ?></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('phone_number') ?></label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('address') ?></label>
                    <textarea name="address" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        <?= t('save') ?>
                    </button>
                    <button type="button" onclick="toggleEditMode()" class="btn" style="flex: 1; background: var(--background); color: var(--text-primary);">
                        <?= t('cancel') ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Order History Section -->
        <div class="profile-info-card">
            <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                Order History
            </h3>
            
            <?php if (empty($user_orders)): ?>
                <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 16px; opacity: 0.5;">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <p style="margin: 0; font-size: 16px;">No orders yet</p>
                    <p style="margin: 8px 0 0 0; font-size: 14px;">Start shopping to see your orders here</p>
                    <a href="products.php" class="btn btn-primary" style="margin-top: 16px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        Browse Products
                    </a>
                </div>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach (array_slice($user_orders, 0, 5) as $order): ?>
                        <div style="padding: 16px; border: 2px solid var(--border); border-radius: var(--radius); margin-bottom: 12px; transition: var(--transition); cursor: pointer;" onclick="window.location.href='invoice.php?order_id=<?= $order['id'] ?>'">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                <div>
                                    <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">
                                        Order #<?= strtoupper(substr($order['id'], 0, 8)) ?>
                                    </div>
                                    <div style="font-size: 13px; color: var(--text-secondary);">
                                        <?= date('M d, Y - H:i', strtotime($order['createdAt'])) ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 18px; font-weight: 700; color: var(--primary);">
                                        EGP <?= number_format($order['totalAmount'], 2) ?>
                                    </div>
                                    <div style="font-size: 12px; padding: 4px 12px; background: <?= $order['status'] === 'pending' ? '#fef3c7' : ($order['status'] === 'completed' ? '#d1fae5' : '#fee2e2') ?>; color: <?= $order['status'] === 'pending' ? '#92400e' : ($order['status'] === 'completed' ? '#065f46' : '#991b1b') ?>; border-radius: 12px; display: inline-block; margin-top: 4px; font-weight: 600;">
                                        <?= ucfirst($order['status']) ?>
                                    </div>
                                </div>
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary);">
                                <?php 
                                $itemCount = is_array($order['items']) ? count($order['items']) : 0;
                                echo $itemCount . ' item' . ($itemCount !== 1 ? 's' : '');
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($user_orders) > 5): ?>
                    <div style="text-align: center; margin-top: 16px;">
                        <a href="history.php" class="btn" style="background: var(--background); color: var(--text-primary); text-decoration: none;">
                            View All Orders (<?= count($user_orders) ?>)
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Language Section -->
        <div class="profile-info-card">
            <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700;"><?= t('language') ?></h3>
            <div style="display: flex; gap: 12px;">
                <a href="?lang=en" class="btn <?= $lang === 'en' ? 'btn-primary' : '' ?>" style="flex: 1; text-align: center; <?= $lang !== 'en' ? 'background: var(--background); color: var(--text-primary);' : '' ?>">
                    <?= t('english') ?>
                </a>
                <a href="?lang=ar" class="btn <?= $lang === 'ar' ? 'btn-primary' : '' ?>" style="flex: 1; text-align: center; <?= $lang !== 'ar' ? 'background: var(--background); color: var(--text-primary);' : '' ?>">
                    <?= t('arabic') ?>
                </a>
            </div>
        </div>
        
        <!-- Sign Out Button -->
        <button onclick="confirmSignOut()" class="btn btn-danger btn-block">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <?= t('sign_out') ?>
        </button>
    </div>
    
    <?php include 'includes/nav.php'; ?>
    
    <script>
        // Handle file upload
        document.getElementById('fileInput').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    return;
                }
                
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 5MB');
                    return;
                }
                
                // Submit form immediately - the page will reload with the new image
                document.getElementById('uploadForm').submit();
            }
        });
        
        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            
            if (editMode.classList.contains('active')) {
                editMode.classList.remove('active');
                viewMode.classList.remove('hidden');
            } else {
                editMode.classList.add('active');
                viewMode.classList.add('hidden');
            }
        }
        
        function confirmSignOut() {
            if (confirm('<?= t('sign_out_confirm') ?>')) {
                window.location.href = '../logout.php';
            }
        }
    </script>
</body>
</html>
