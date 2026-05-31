<?php
require_once 'config.php';

// Redirect to home page for public, or dashboard if logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
} else {
    redirect('home.php');
}
?>
