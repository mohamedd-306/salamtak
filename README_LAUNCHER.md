# 🚀 Salamtak Web App - Quick Launch Guide

## 📁 Files Created

I've created several launcher files to make it easy to run your app:

### 1. **OPEN_APP.bat** ⭐ (Easiest)
- **Double-click** this file to open the home page in Chrome
- Works on any Windows system
- No technical knowledge needed

### 2. **OPEN_ADMIN.bat** 🔐
- **Double-click** this file to open the admin dashboard directly
- Quick access to admin panel

### 3. **run-app.ps1** 🎯 (Advanced)
- PowerShell script with interactive menu
- Checks if XAMPP services are running
- Multiple page options

**To run:**
```powershell
.\run-app.ps1
```

### 4. **RUN_APP_COMMANDS.md** 📖
- Complete documentation with all commands
- Troubleshooting guide
- Project structure
- Development tips

---

## 🎬 Quick Start (3 Steps)

### Step 1: Make Sure XAMPP is Running
1. Open XAMPP Control Panel
2. Start **Apache** (if not running)
3. Start **MySQL** (if not running)

### Step 2: Launch the App
**Option A - Easiest:**
- Double-click `OPEN_APP.bat`

**Option B - Command Line:**
```cmd
start chrome "http://localhost/salamtak_web/home.php"
```

**Option C - PowerShell:**
```powershell
Start-Process "chrome.exe" "http://localhost/salamtak_web/home.php"
```

### Step 3: Test the Logo Fix
1. Login as admin
2. Go to admin dashboard
3. Click the logo in the navbar
4. It should reload the dashboard (not go to home page)

---

## 🔗 Quick Access URLs

Copy and paste these into your browser:

### Public Pages:
```
http://localhost/salamtak_web/home.php
http://localhost/salamtak_web/login.php
http://localhost/salamtak_web/signup.php
http://localhost/salamtak_web/user/products.php
```

### Admin Pages:
```
http://localhost/salamtak_web/admin/dashboard.php
http://localhost/salamtak_web/admin/inventory.php
http://localhost/salamtak_web/admin/add_product.php
```

---

## 💡 Pro Tips

### Tip 1: Create Desktop Shortcuts
Right-click `OPEN_APP.bat` → Send to → Desktop (create shortcut)

### Tip 2: Pin to Taskbar
1. Create a shortcut to `OPEN_APP.bat`
2. Right-click the shortcut → Pin to taskbar

### Tip 3: Keyboard Shortcut
Create a shortcut and assign a keyboard shortcut:
1. Right-click `OPEN_APP.bat` → Create shortcut
2. Right-click the shortcut → Properties
3. In "Shortcut key" field, press `Ctrl + Alt + S` (or any key)
4. Click OK

### Tip 4: Always Clear Cache After Changes
When you make CSS/JS changes:
- Press `Ctrl + F5` (hard refresh)
- Or `Ctrl + Shift + Delete` (clear cache)

---

## 🐛 Troubleshooting

### Problem: "This site can't be reached"
**Solution:**
1. Check if XAMPP Apache is running
2. Verify the URL is correct
3. Try: `http://localhost/` to test Apache

### Problem: Batch file doesn't work
**Solution:**
1. Right-click the `.bat` file
2. Select "Run as administrator"

### Problem: PowerShell script won't run
**Solution:**
Run this command first (one time only):
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Problem: Chrome doesn't open
**Solution:**
Use the full path:
```cmd
"C:\Program Files\Google\Chrome\Application\chrome.exe" "http://localhost/salamtak_web/home.php"
```

---

## 📝 Commands Cheat Sheet

### Open in Chrome:
```cmd
start chrome "http://localhost/salamtak_web/home.php"
```

### Check XAMPP Status:
```powershell
Get-Process | Where-Object {$_.ProcessName -like "*httpd*" -or $_.ProcessName -like "*mysql*"}
```

### Run PHP Script:
```cmd
C:\xampp\php\php.exe salamtak_web/script_name.php
```

### Clear Browser Cache:
- `Ctrl + Shift + Delete` - Open clear data dialog
- `Ctrl + F5` - Hard refresh (bypass cache)

---

## 🎯 Next Steps

1. ✅ Test the logo fix in admin dashboard
2. ✅ Verify all pages load correctly
3. ✅ Test image uploads
4. ✅ Test product filtering
5. ✅ Test bilingual support (EN/AR)

---

## 📞 Need Help?

Refer to `RUN_APP_COMMANDS.md` for:
- Complete command reference
- Detailed troubleshooting
- Project structure
- Development workflow

---

**Created**: May 16, 2026  
**Project**: Salamtak Safety Equipment Platform  
**Status**: ✅ Logo fix applied, ready to test!
