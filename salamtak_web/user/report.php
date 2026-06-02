<?php
require_once '../config.php';

/**
 * Compress image data for base64 storage
 * Similar to Flutter app's compression logic
 * Requires PHP GD extension to be enabled
 */
function compressImage($image_data, $mime_type) {
    // Create image resource from string
    $image = imagecreatefromstring($image_data);
    
    if ($image === false) {
        error_log('Failed to create image resource, returning original data');
        return $image_data;
    }
    
    // Get original dimensions
    $width = imagesx($image);
    $height = imagesy($image);
    
    error_log("Original image dimensions: {$width}x{$height}");
    
    // Resize if image is too large (max 1200px)
    $max_dimension = 1200;
    if ($width > $max_dimension || $height > $max_dimension) {
        $ratio = min($max_dimension / $width, $max_dimension / $height);
        $new_width = (int)($width * $ratio);
        $new_height = (int)($height * $ratio);
        
        error_log("Resizing image to: {$new_width}x{$new_height}");
        
        $resized = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG
        if (strpos($mime_type, 'png') !== false) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }
    
    // Compress and output to buffer
    ob_start();
    
    if (strpos($mime_type, 'png') !== false) {
        // PNG compression (0-9, where 9 is max compression)
        imagepng($image, null, 6);
    } else {
        // JPEG compression (0-100, where 100 is best quality)
        imagejpeg($image, null, 85);
    }
    
    $compressed_data = ob_get_clean();
    imagedestroy($image);
    
    $original_size = strlen($image_data);
    $compressed_size = strlen($compressed_data);
    $ratio = round((1 - $compressed_size / $original_size) * 100, 1);
    
    error_log("Image compression: {$original_size} bytes -> {$compressed_size} bytes ({$ratio}% reduction)");
    
    return $compressed_data;
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    $type = $_GET['type'] ?? 'Other';
    header("Location: report.php?type=" . urlencode($type));
    exit();
}

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$problem_type = $_GET['type'] ?? 'Other';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $severity = $_POST['severity'] ?? 'Medium';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $location_address = trim($_POST['location_address'] ?? '');
    
    // Handle image upload - Convert to base64 for Flutter app compatibility
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp_file = $_FILES['image']['tmp_name'];
        $file_type = $_FILES['image']['type'];
        
        // Read the uploaded file
        $image_data = file_get_contents($tmp_file);
        
        if ($image_data !== false) {
            // Compress image (requires GD library)
            $compressed_image = compressImage($image_data, $file_type);
            
            // Convert to base64
            $base64_image = base64_encode($compressed_image);
            
            // Determine MIME type
            $mime_type = $file_type;
            if (empty($mime_type) || $mime_type === 'application/octet-stream') {
                // Fallback to JPEG if type is unknown
                $mime_type = 'image/jpeg';
            }
            
            // Create data URI
            $image_path = 'data:' . $mime_type . ';base64,' . $base64_image;
            
            error_log('Image converted to base64: ' . strlen($image_path) . ' characters');
        } else {
            error_log('Failed to read uploaded image file');
        }
    }
    
    if (empty($description) || strlen($description) < 10) {
        $error = "Description must be at least 10 characters";
    } elseif (empty($latitude) || empty($longitude)) {
        $error = "Please select a location on the map";
    } else {
        $user_id = $_SESSION['user_id'];
        $national_id = $_SESSION['national_id'];
        $name = $_SESSION['name'];
        
        $report_data = [
            'uid' => $user_id,
            'nationalId' => $national_id,
            'name' => $name,
            'type' => $problem_type,
            'description' => $description,
            'imagePath' => $image_path,
            'status' => 'pending',
            'severity' => $severity,
            'latitude' => (float)$latitude,
            'longitude' => (float)$longitude,
            'location' => $location_address,
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ];
        
        $report_id = addFirestoreDocument('reports', $report_data);
        
        if ($report_id) {
            $success = t('report_submitted');
            // Redirect will be handled by JavaScript popup
        } else {
            $error = "Failed to submit report";
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
    <title><?= t('app_name') ?> - <?= t('report') ?> <?= htmlspecialchars($problem_type) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        /* Navbar Styles - Ensure they load */
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
            overflow: hidden;
        }
        
        .landing-brand-logo img {
            width: 150%;
            height: 150%;
            object-fit: contain;
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
        
        /* Profile Dropdown Styles */
        .profile-dropdown {
            position: relative;
        }
        
        .profile-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px 6px 6px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }
        
        .profile-button:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        
        .profile-avatar {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        
        .profile-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .profile-name {
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            margin-bottom: 2px;
            color: white;
        }
        
        .dropdown-arrow {
            margin-left: 4px;
            transition: transform 0.3s;
        }
        
        .profile-dropdown.active .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .profile-dropdown.active .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #2d3748;
            text-decoration: none;
            transition: all 0.3s;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .dropdown-item:first-child {
            border-radius: 12px 12px 0 0;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
            border-radius: 0 0 12px 12px;
        }
        
        .dropdown-item:hover {
            background: #f7fafc;
        }
        
        .dropdown-item svg {
            width: 18px;
            height: 18px;
            color: #4a5568;
        }
        
        .dropdown-item.logout {
            color: #ef4444;
        }
        
        .dropdown-item.logout svg {
            color: #ef4444;
        }
        
        .dropdown-item.logout:hover {
            background: #fee;
        }
        
        .language-switcher {
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .lang-btn {
            flex: 1;
            padding: 6px 12px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .lang-btn:hover {
            border-color: #0f1d3f;
        }
        
        .lang-btn.active {
            background: #0f1d3f;
            color: white;
            border-color: #0f1d3f;
        }
        
        /* Severity dropdown fix */
        .form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: white !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%234a5568' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
            cursor: pointer;
        }
        
        .form-select:hover {
            border-color: var(--primary) !important;
            background-color: white !important;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(15, 29, 63, 0.1) !important;
            background-color: white !important;
        }
        
        /* Fix dropdown options background */
        .form-select option {
            background-color: white !important;
            color: var(--text-primary) !important;
            padding: 12px 16px;
        }
        
        .form-select option:hover,
        .form-select option:checked {
            background-color: #f0f4ff !important;
        }

        /* Voice Button Styles */
        .voice-btn {
            position: absolute;
            right: 12px;
            top: 12px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            z-index: 10;
        }
        
        .voice-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        
        .voice-btn:active {
            transform: scale(0.95);
        }
        
        .voice-btn svg {
            color: white;
        }
        
        .voice-btn.recording {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            }
            50% {
                box-shadow: 0 4px 24px rgba(239, 68, 68, 0.6);
            }
        }
        
        .voice-btn.processing {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .form-hint {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 8px;
        }
        
        .form-hint.recording {
            color: #ef4444;
            font-weight: 600;
        }
        
        /* Notification Styles */
        .notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
            min-width: 320px;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .notification-icon {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .notification-message {
            font-size: 14px;
            opacity: 0.95;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Page Header with Back Button -->
    <div class="container" style="margin-top: 20px;">
        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
            <a href="services.php" style="width: 44px; height: 44px; border-radius: var(--radius); background: var(--surface); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-primary); text-decoration: none; transition: var(--transition);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 style="font-size: 28px; font-weight: 800; margin: 0; letter-spacing: -0.5px;"><?= t('report') ?> <?= htmlspecialchars($problem_type) ?></h1>
                <p style="font-size: 14px; color: var(--text-secondary); margin: 4px 0 0 0; font-weight: 500;">Fill in the details below</p>
            </div>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="report-form">
            <div class="form-group">
                <label class="form-label required"><?= t('photo') ?></label>
                <div class="image-upload" id="imageUpload">
                    <input type="file" name="image" id="imageInput" accept="image/*" style="display:none">
                    <div class="upload-placeholder" id="uploadPlaceholder">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <p>Tap to upload a photo</p>
                        <span>JPG, PNG supported</span>
                    </div>
                    <img id="imagePreview" style="display:none; width:100%; height:100%; object-fit:cover;">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label required"><?= t('location') ?></label>
                <div id="map" style="height: 300px; border-radius: 16px; margin-bottom: 10px; border: 2px solid var(--border);"></div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="text" name="location_address" id="locationAddress" placeholder="<?= t('location') ?> address" readonly>
                <p class="form-hint">Click on the map to set location</p>
            </div>
            
            <div class="form-group">
                <label class="form-label required"><?= t('description') ?></label>
                <div style="position: relative;">
                    <textarea name="description" id="descriptionField" rows="5" required minlength="10" 
                              placeholder="<?= $lang === 'ar' ? 'صف المشكلة - الخطورة، الموقع الدقيق، إلخ.' : 'Describe the problem — severity, exact spot, etc.' ?>" 
                              style="padding-right: 60px;"></textarea>
                    <button type="button" id="voiceBtn" class="voice-btn" title="<?= $lang === 'ar' ? 'إدخال صوتي' : 'Voice input' ?>">
                        <svg id="micIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                            <line x1="12" y1="19" x2="12" y2="23"/>
                            <line x1="8" y1="23" x2="16" y2="23"/>
                        </svg>
                    </button>
                </div>
                <p class="form-hint" id="voiceHint"><?= $lang === 'ar' ? 'انقر على الميكروفون لاستخدام الإدخال الصوتي' : 'Click the microphone to use voice input' ?></p>
            </div>
            
            <div class="form-group">
                <label class="form-label required"><?= t('severity') ?></label>
                <select name="severity" class="form-select">
                    <option value="Low"><?= t('low') ?></option>
                    <option value="Medium" selected><?= t('medium') ?></option>
                    <option value="High"><?= t('high') ?></option>
                    <option value="Critical"><?= t('critical') ?></option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                <?= t('submit') ?>
            </button>
        </form>
    </div>
    
    <script>
        // Voice Recognition Setup
        let recognition;
        let isRecording = false;
        const currentLang = '<?= $lang ?>';
        const isArabic = currentLang === 'ar';
        
        // Language-specific messages
        const messages = {
            recording: isArabic ? '🎤 جاري التسجيل... انقر مرة أخرى للإيقاف' : '🎤 Recording... Click again to stop',
            clickToUse: isArabic ? 'انقر على الميكروفون لاستخدام الإدخال الصوتي' : 'Click the microphone to use voice input',
            notSupported: isArabic ? 'الإدخال الصوتي غير مدعوم في هذا المتصفح' : 'Voice input not supported in this browser',
            error: isArabic ? 'خطأ' : 'Error',
            tryAgain: isArabic ? 'انقر للمحاولة مرة أخرى' : 'Click to try again'
        };
        
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = true;
            recognition.interimResults = true;
            recognition.maxAlternatives = 1;
            recognition.lang = isArabic ? 'ar-EG' : 'en-US';
            
            const voiceBtn = document.getElementById('voiceBtn');
            const descriptionField = document.getElementById('descriptionField');
            const voiceHint = document.getElementById('voiceHint');
            let finalTranscript = '';
            let restartTimeout;
            
            voiceBtn.addEventListener('click', function() {
                if (!isRecording) {
                    // Start recording
                    finalTranscript = descriptionField.value;
                    try {
                        recognition.start();
                        isRecording = true;
                        voiceBtn.classList.add('recording');
                        voiceHint.textContent = messages.recording;
                        voiceHint.classList.add('recording');
                    } catch (e) {
                        console.error('Error starting recognition:', e);
                    }
                } else {
                    // Stop recording
                    recognition.stop();
                    isRecording = false;
                    voiceBtn.classList.remove('recording');
                    voiceHint.textContent = messages.clickToUse;
                    voiceHint.classList.remove('recording');
                    if (restartTimeout) {
                        clearTimeout(restartTimeout);
                    }
                }
            });
            
            recognition.onresult = function(event) {
                let interimTranscript = '';
                
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalTranscript += (finalTranscript ? ' ' : '') + transcript;
                    } else {
                        interimTranscript += transcript;
                    }
                }
                
                descriptionField.value = finalTranscript + (interimTranscript ? ' ' + interimTranscript : '');
                
                // Auto-scroll textarea to bottom
                descriptionField.scrollTop = descriptionField.scrollHeight;
            };
            
            recognition.onerror = function(event) {
                console.error('Speech recognition error:', event.error);
                
                // Don't stop on "no-speech" error, just continue
                if (event.error === 'no-speech') {
                    if (isRecording) {
                        // Restart after a short delay
                        restartTimeout = setTimeout(() => {
                            if (isRecording) {
                                try {
                                    recognition.start();
                                } catch (e) {
                                    console.log('Already started');
                                }
                            }
                        }, 100);
                    }
                    return;
                }
                
                // For other errors, stop recording
                if (event.error !== 'aborted') {
                    isRecording = false;
                    voiceBtn.classList.remove('recording');
                    voiceHint.textContent = messages.error + ': ' + event.error + '. ' + messages.tryAgain;
                    voiceHint.classList.remove('recording');
                }
            };
            
            recognition.onend = function() {
                if (isRecording) {
                    // Automatically restart for continuous recording
                    restartTimeout = setTimeout(() => {
                        if (isRecording) {
                            try {
                                recognition.start();
                            } catch (e) {
                                console.log('Recognition restart failed:', e);
                                // Try again after a longer delay
                                setTimeout(() => {
                                    if (isRecording) {
                                        try {
                                            recognition.start();
                                        } catch (err) {
                                            console.error('Failed to restart:', err);
                                        }
                                    }
                                }, 500);
                            }
                        }
                    }, 100);
                }
            };
        } else {
            // Hide voice button if not supported
            const voiceBtn = document.getElementById('voiceBtn');
            if (voiceBtn) {
                voiceBtn.style.display = 'none';
            }
            document.getElementById('voiceHint').textContent = messages.notSupported;
        }
        
        // Show notification and redirect if report was submitted successfully
        <?php if ($success): ?>
        window.addEventListener('DOMContentLoaded', function() {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = `
                <div class="notification-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="notification-content">
                    <div class="notification-title">Success!</div>
                    <div class="notification-message">Your report has been submitted successfully.</div>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Redirect after 2 seconds
            setTimeout(function() {
                window.location.href = 'dashboard.php';
            }, 2000);
        });
        <?php endif; ?>
        
        // Image upload preview and classification
        const imageInput = document.getElementById('imageInput');
        const imageUpload = document.getElementById('imageUpload');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const imagePreview = document.getElementById('imagePreview');
        
        imageUpload.addEventListener('click', () => imageInput.click());
        
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('Image selected:', file.name);
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    uploadPlaceholder.style.display = 'none';
                    
                    console.log('Starting image classification...');
                    // Classify the image
                    classifyImage(file);
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Helper function to show notifications
        function showNotification(message, type = 'info', duration = 3000) {
            console.log('Showing notification:', message, type);
            const colors = {
                'info': 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
                'success': 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                'error': 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'
            };
            
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${colors[type]};
                color: white;
                padding: 16px 20px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                z-index: 10001;
                font-size: 14px;
                font-weight: 600;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            if (duration > 0) {
                setTimeout(() => {
                    notification.remove();
                }, duration);
            }
            
            return notification;
        }
        
        // Image classification function
        function classifyImage(file) {
            console.log('classifyImage called with file:', file.name);
            const formData = new FormData();
            formData.append('image', file);
            
            // Show loading indicator
            const notification = showNotification('<?= $lang === "ar" ? "جاري تحليل الصورة..." : "Analyzing image..." ?>', 'info', 0);
            
            fetch('../classify_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Classification result:', data);
                // Remove loading notification
                if (notification) notification.remove();
                
                if (data.success) {
                    const detectedType = data.type;
                    const confidence = (data.confidence * 100).toFixed(0);
                    
                    // Show classification result
                    const message = '<?= $lang === "ar" ? "تم اكتشاف" : "Detected" ?>: ' + detectedType + ' (' + confidence + '% <?= $lang === "ar" ? "دقة" : "confidence" ?>)';
                    showNotification(message, 'success', 5000);
                    
                    // Update page title if different from current type
                    const currentType = '<?= $problem_type ?>';
                    console.log('Current type:', currentType, 'Detected type:', detectedType);
                    
                    if (detectedType !== currentType && detectedType !== 'Other') {
                        setTimeout(() => {
                            const changeMessage = '<?= $lang === "ar" ? "هل تريد تغيير نوع المشكلة إلى" : "Would you like to change problem type to" ?> ' + detectedType + '?';
                            if (confirm(changeMessage)) {
                                window.location.href = 'report.php?type=' + encodeURIComponent(detectedType) + '&lang=<?= $lang ?>';
                            }
                        }, 1000);
                    }
                } else {
                    console.error('Classification failed:', data.error);
                    showNotification('<?= $lang === "ar" ? "فشل تحليل الصورة" : "Image analysis failed" ?>', 'error', 3000);
                }
            })
            .catch(error => {
                console.error('Classification error:', error);
                if (notification) notification.remove();
                showNotification('<?= $lang === "ar" ? "خطأ في تحليل الصورة" : "Error analyzing image" ?>', 'error', 3000);
            });
        }
        
        // Map initialization
        const map = L.map('map').setView([30.0444, 31.2357], 13); // Cairo default
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        let marker;
        
        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            if (marker) {
                map.removeLayer(marker);
            }
            
            marker = L.marker([lat, lng]).addTo(map);
            
            // Reverse geocoding
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('locationAddress').value = data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                });
        });
        
        // Get user location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                map.setView([lat, lng], 15);
            });
        }
    </script>
</body>
</html>
