<?php
/**
 * Script to create admin account in Firebase
 * Run this once to create the admin account
 * 
 * Admin Credentials:
 * Work ID: 221007689
 * Password: 631663
 */

require_once 'config.php';

echo "<h2>Creating Admin Account...</h2>";

$work_id = '221007689';
$password = '631663';
$name = 'System Administrator';

// Create Firebase Auth account with admin email format
$admin_email = 'admin-' . $work_id . '@salamtak.com';

$url = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . FIREBASE_API_KEY;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => $admin_email,
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
    
    echo "<p style='color: green;'>✓ Firebase Auth account created successfully!</p>";
    echo "<p>UID: " . htmlspecialchars($uid) . "</p>";
    
    // Create Firestore user document
    $admin_data = [
        'workId' => $work_id,
        'name' => $name,
        'email' => $admin_email,
        'userType' => 'admin',
        'createdAt' => date('Y-m-d H:i:s')
    ];
    
    // Use the UID as document ID
    $firestore_url = FIRESTORE_URL . '/users/' . $uid;
    $ch = curl_init($firestore_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    
    $fields = [];
    foreach ($admin_data as $key => $value) {
        $fields[$key] = convertToFirestoreValue($value);
    }
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['fields' => $fields]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $firestore_response = curl_exec($ch);
    $firestore_httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($firestore_httpCode >= 200 && $firestore_httpCode < 300) {
        echo "<p style='color: green;'>✓ Firestore admin document created successfully!</p>";
        echo "<h3 style='color: blue;'>Admin Account Created Successfully!</h3>";
        echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h4>Login Credentials:</h4>";
        echo "<p><strong>Work ID:</strong> " . htmlspecialchars($work_id) . "</p>";
        echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
        echo "<p><strong>User Type:</strong> Admin</p>";
        echo "</div>";
        echo "<p><a href='login.php' style='color: blue; text-decoration: underline;'>Go to Login Page</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create Firestore document</p>";
        echo "<p>Response: " . htmlspecialchars($firestore_response) . "</p>";
    }
} else {
    $error_data = json_decode($response, true);
    if (isset($error_data['error']['message']) && $error_data['error']['message'] === 'EMAIL_EXISTS') {
        echo "<p style='color: orange;'>⚠ Admin account already exists!</p>";
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
        echo "<h4>Existing Admin Credentials:</h4>";
        echo "<p><strong>Work ID:</strong> " . htmlspecialchars($work_id) . "</p>";
        echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
        echo "<p><strong>User Type:</strong> Admin</p>";
        echo "</div>";
        echo "<p><a href='login.php' style='color: blue; text-decoration: underline;'>Go to Login Page</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create admin account</p>";
        echo "<p>Error: " . htmlspecialchars($response) . "</p>";
    }
}
?>
