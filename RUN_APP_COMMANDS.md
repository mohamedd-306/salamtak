# Salamtak Web Application - Run Commands

## 🚀 Quick Start Commands

### 1. Open the Web App in Chrome

**PowerShell Command:**
```powershell
Start-Process "chrome.exe" "http://localhost/salamtak_web/home.php"
```

**Alternative (if above doesn't work):**
```powershell
& "C:\Program Files\Google\Chrome\Application\chrome.exe" "http://localhost/salamtak_web/home.php"
```

**CMD Command:**
```cmd
start chrome "http://localhost/salamtak_web/home.php"
```

---

## 📍 Important URLs

### Public Pages:
- **Home Page**: `http://localhost/salamtak_web/home.php`
- **Login**: `http://localhost/salamtak_web/login.php`
- **Signup**: `http://localhost/salamtak_web/signup.php`
- **User Products**: `http://localhost/salamtak_web/user/products.php`
- **User Dashboard**: `http://localhost/salamtak_web/user/dashboard.php`

### Admin Pages:
- **Admin Dashboard**: `http://localhost/salamtak_web/admin/dashboard.php`
- **Admin Inventory**: `http://localhost/salamtak_web/admin/inventory.php`
- **Add Product**: `http://localhost/salamtak_web/admin/add_product.php`
- **Orders**: `http://localhost/salamtak_web/admin/products.php`

---

## 🔧 XAMPP Control Commands

### Check if Apache and MySQL are Running:
```powershell
Get-Process | Where-Object {$_.ProcessName -like "*httpd*" -or $_.ProcessName -like "*mysql*"}
```

### Start XAMPP Services (if not running):
```cmd
cd C:\xampp
xampp-control.exe
```

Or start services directly:
```cmd
C:\xampp\apache_start.bat
C:\xampp\mysql_start.bat
```

### Stop XAMPP Services:
```cmd
C:\xampp\apache_stop.bat
C:\xampp\mysql_stop.bat
```

---

## 🗄️ Database Commands

### Access MySQL via Command Line:
```cmd
C:\xampp\mysql\bin\mysql.exe -u root -p
```

### Run PHP Scripts (for migrations/fixes):
```cmd
C:\xampp\php\php.exe salamtak_web/script_name.php
```

**Example:**
```cmd
C:\xampp\php\php.exe salamtak_web/fix_all_images_cli.php
```

---

## 🧹 Clear Browser Cache

When you make CSS or JavaScript changes, clear cache:

**Chrome Shortcut:**
- `Ctrl + Shift + Delete` - Opens Clear Browsing Data
- `Ctrl + F5` - Hard refresh (bypass cache)
- `Shift + F5` - Hard refresh alternative

---

## 🔐 Test Accounts

### Admin Account:
- **Email**: admin@salamtak.com
- **Password**: (your admin password)

### User Account:
- **Email**: user@salamtak.com
- **Password**: (your user password)

---

## 📂 Project Structure

```
salamtak_web/
├── home.php              # Public home page
├── login.php             # Login page
├── signup.php            # Signup page
├── config.php            # Configuration & Firebase setup
├── translations.php      # Bilingual translations (EN/AR)
├── admin/                # Admin pages
│   ├── dashboard.php     # Admin dashboard
│   ├── inventory.php     # Product inventory
│   ├── add_product.php   # Add new products
│   └── includes/
│       └── admin_navbar.php  # Reusable admin navbar
├── user/                 # User pages
│   ├── dashboard.php     # User dashboard
│   ├── products.php      # Browse products
│   └── product_details.php  # Product details
└── assets/
    ├── products/         # Product images (local storage)
    ├── css/
    └── logof.png         # Logo
```

---

## 🐛 Troubleshooting

### Issue: "Cannot connect to database"
**Solution:**
1. Make sure MySQL is running in XAMPP
2. Check Firebase credentials in `config.php`

### Issue: "Page not found (404)"
**Solution:**
1. Verify XAMPP Apache is running
2. Check the URL path is correct
3. Ensure files are in `C:\xampp\htdocs\salamtak_web\`

### Issue: "Images not showing"
**Solution:**
1. Clear browser cache (`Ctrl + Shift + Delete`)
2. Hard refresh (`Ctrl + F5`)
3. Check images exist in `salamtak_web/assets/products/`

### Issue: "Logo not clickable"
**Solution:**
1. Clear browser cache
2. Hard refresh the page
3. Check browser console for JavaScript errors (F12)

---

## 💡 Development Tips

### Watch for File Changes:
When editing PHP files, just refresh the browser - no restart needed!

### CSS Changes Not Showing:
Add cache-busting parameter:
```html
<link rel="stylesheet" href="assets/css/style.css?v=2.0">
```

### Check PHP Errors:
Enable error reporting in `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 🌐 Language Switching

The app supports English and Arabic:
- Add `?lang=en` for English
- Add `?lang=ar` for Arabic

**Examples:**
- `http://localhost/salamtak_web/home.php?lang=ar`
- `http://localhost/salamtak_web/admin/dashboard.php?lang=en`

---

## 📝 Notes

- **PHP Version**: Check with `C:\xampp\php\php.exe -v`
- **Document Root**: `C:\xampp\htdocs\`
- **Project Path**: `C:\New folder\htdocs\swebsite\salamtak - Copy\salamtak_web\`
- **Firestore**: Used for database (users, products, reports, orders, reviews)
- **Local Storage**: Product images saved in `assets/products/`

---

## 🎯 Quick Test Workflow

1. **Start XAMPP** (if not running)
2. **Open Chrome**: `start chrome "http://localhost/salamtak_web/home.php"`
3. **Test Login**: Go to login page and use test credentials
4. **Test Admin**: Navigate to admin dashboard
5. **Test Products**: Browse user products page
6. **Clear Cache**: If changes don't show, use `Ctrl + F5`

---

**Last Updated**: May 16, 2026
**Project**: Salamtak Safety Equipment Platform
