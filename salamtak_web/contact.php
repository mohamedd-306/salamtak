<?php
require_once 'config.php';

// Handle language change first (before any output)
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    redirect('contact.php');
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('contact') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            flex: 1;
        }
    </style>
</head>
<body>
    <?php include 'includes/public_header.php'; ?>
    
    <div class="container" style="padding-top: 120px;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h1 style="font-size: 48px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px;"><?= t('get_in_touch') ?></h1>
            <p style="color: var(--text-secondary); font-size: 18px; max-width: 600px; margin: 0 auto;">
                <?= t('contact_desc') ?>
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 32px; max-width: 1000px; margin: 0 auto;">
            <!-- Contact Form -->
            <div style="background: var(--card-bg); border-radius: var(--radius-2xl); padding: 40px; box-shadow: var(--shadow-lg); border: 2px solid var(--border-light);">
                <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 24px;"><?= t('send_message') ?></h3>
                
                <form>
                    <div class="form-group">
                        <label><?= t('your_name') ?></label>
                        <input type="text" placeholder="<?= t('enter_full_name') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><?= t('your_email') ?></label>
                        <input type="email" placeholder="<?= t('enter_email') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><?= t('subject') ?></label>
                        <input type="text" placeholder="<?= t('subject') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><?= t('message') ?></label>
                        <textarea rows="5" placeholder="<?= t('describe_problem') ?>" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        <?= t('send_message') ?>
                    </button>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div style="background: var(--card-bg); border-radius: var(--radius-2xl); padding: 40px; box-shadow: var(--shadow-lg); border: 2px solid var(--border-light);">
                <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 24px;"><?= t('contact_info') ?></h3>
                
                <div style="display: flex; gap: 16px; margin-bottom: 32px;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin-bottom: 4px;"><?= t('email_us') ?></h4>
                        <p style="color: var(--text-secondary); margin-bottom: 4px;">support@salamtak.com</p>
                        <small style="color: var(--text-tertiary);"><?= $lang === 'ar' ? 'نرد عادة خلال 24 ساعة' : 'We typically respond within 24 hours' ?></small>
                    </div>
                </div>
                
                <div style="display: flex; gap: 16px; margin-bottom: 32px;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--success) 0%, var(--success-light) 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin-bottom: 4px;"><?= t('call_us') ?></h4>
                        <p style="color: var(--text-secondary); margin-bottom: 4px;">+20 123 456 7890</p>
                        <small style="color: var(--text-tertiary);"><?= $lang === 'ar' ? 'الأحد - الخميس، 9 صباحاً - 6 مساءً' : 'Sunday - Thursday, 9 AM - 6 PM' ?></small>
                    </div>
                </div>
                
                <div style="display: flex; gap: 16px;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--warning) 0%, var(--warning-light) 100%); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; margin-bottom: 4px;"><?= t('visit_us') ?></h4>
                        <p style="color: var(--text-secondary); margin-bottom: 4px;"><?= t('cairo_egypt') ?></p>
                        <small style="color: var(--text-tertiary);"><?= $lang === 'ar' ? 'نخدم السائقين في جميع أنحاء مصر' : 'Serving drivers across Egypt' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/public_footer.php'; ?>
    
    <!-- Quick Report Button (for logged-in users) -->
    <?php if (isLoggedIn() && !isAdmin()): ?>
        <a href="user/services.php" style="position: fixed; bottom: 30px; right: 30px; width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4); z-index: 999; transition: var(--transition); text-decoration: none;" onmouseover="this.style.transform='scale(1.1) rotate(5deg)'" onmouseout="this.style.transform='scale(1)'">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        </a>
    <?php endif; ?>
</body>
</html>
