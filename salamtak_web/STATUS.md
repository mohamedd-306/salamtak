# 🎉 Salamtak Website - READY AND RUNNING!

## ✅ Current Status: LIVE

The Salamtak web application is now **fully operational** and running on your local machine!

### 🌐 Access Information

**Website URL:** http://localhost:8000/

**Server Status:** ✅ Running (PHP Development Server on port 8000)

### 🔐 Login Credentials

#### Admin Account
- **National ID:** `12345678901234`
- **Password:** `admin123456`
- **Access:** Full admin dashboard with report management

#### Test User Account
- **National ID:** `11111111111111`
- **Password:** `user123456`
- **Access:** User dashboard with report submission

### 📁 Data Storage

The website uses a **file-based database system** that works exactly like Firestore:

- **Location:** `salamtak_web/data/`
- **Users:** Stored in `data/users.json`
- **Reports:** Stored in `data/reports.json`
- **Images:** Stored in `uploads/` folder

This means:
- ✅ No MySQL setup required
- ✅ No database configuration needed
- ✅ Works exactly like your Flutter app
- ✅ Data persists between sessions
- ✅ Easy to backup (just copy the data folder)

### 🎨 Features Available

#### For Users:
- ✅ Login/Signup with National ID validation
- ✅ Dashboard with real-time statistics
- ✅ Report problems (Pothole, Broken Pipe, Other)
- ✅ Upload photos for reports
- ✅ Interactive map with location selection
- ✅ View report history with status tracking
- ✅ Account management
- ✅ Language switcher (English/Arabic with RTL)

#### For Admins:
- ✅ Admin control panel
- ✅ View all reports from all users
- ✅ Filter by status (All, Pending, In Progress, Resolved)
- ✅ Update report status with one click
- ✅ Statistics overview
- ✅ Language support

### 🌍 Language Support

- **English** (Default)
- **Arabic** with full RTL (Right-to-Left) layout
- Switch language from Account page
- Persistent language preference

### 📱 Responsive Design

- ✅ Mobile-first design
- ✅ Works on all screen sizes
- ✅ Touch-friendly interface
- ✅ Bottom navigation on mobile
- ✅ Desktop-optimized layout

### 🔧 Technical Details

**Technology Stack:**
- PHP 8.2.12 (via XAMPP)
- File-based JSON storage (Firestore-like)
- Leaflet.js for maps
- OpenStreetMap tiles
- Vanilla JavaScript
- CSS3 with CSS Variables
- SVG icons

**Security Features:**
- Password hashing (bcrypt)
- SQL injection prevention (not applicable - no SQL)
- XSS protection (htmlspecialchars)
- Session-based authentication
- File upload validation

### 📂 Project Structure

```
salamtak_web/
├── 🌐 index.php              # Entry point
├── 🔐 login.php              # Login page
├── 📝 signup.php             # Registration
├── 🚪 logout.php             # Logout handler
├── ⚙️ config.php             # Configuration & DB functions
├── 🌍 translations.php       # English/Arabic translations
├── 📁 data/                  # JSON database (auto-created)
│   ├── users.json           # User accounts
│   └── reports.json         # All reports
├── 📸 uploads/               # Uploaded images
├── 🎨 assets/css/
│   └── style.css            # All styles
├── 👤 user/
│   ├── dashboard.php        # User home
│   ├── services.php         # Problem types
│   ├── report.php           # Submit report
│   ├── history.php          # Report history
│   ├── account.php          # Settings
│   └── includes/
│       ├── header.php       # Header component
│       └── nav.php          # Navigation
└── 👨‍💼 admin/
    └── dashboard.php        # Admin panel
```

### 🚀 How to Use

1. **Open your browser** and go to: http://localhost:8000/

2. **Login** with one of the default accounts:
   - Admin: `12345678901234` / `admin123456`
   - User: `11111111111111` / `user123456`

3. **Explore the features:**
   - Submit reports with photos and locations
   - View statistics
   - Track report status
   - Switch languages
   - (Admin) Manage all reports

### 🔄 Server Management

**To stop the server:**
- Close the terminal/PowerShell window
- Or press `Ctrl+C` in the terminal

**To restart the server:**
```powershell
cd "C:\New folder\htdocs\swebsite\salamtak\salamtak_web"
C:\xampp\php\php.exe -S localhost:8000
```

**To change the port:**
```powershell
C:\xampp\php\php.exe -S localhost:9000
```
(Then access at http://localhost:9000/)

### 📊 Data Management

**View stored data:**
- Open `salamtak_web/data/users.json` to see all users
- Open `salamtak_web/data/reports.json` to see all reports

**Backup data:**
```powershell
Copy-Item -Recurse data data_backup
```

**Reset to defaults:**
```powershell
Remove-Item -Recurse data
# Refresh website - will recreate with admin/test user
```

### 🐛 Troubleshooting

**Website not loading?**
- Check if server is running (look for "PHP Development Server started" message)
- Try accessing: http://127.0.0.1:8000/
- Check if port 8000 is already in use

**Can't upload images?**
- Check that `uploads/` folder exists
- Check folder permissions

**Data not saving?**
- Check that `data/` folder can be created
- Check write permissions on the folder

**Map not loading?**
- Check internet connection (maps require external tiles)
- Try refreshing the page

### 🎯 Next Steps

1. ✅ **Test the website** - Try all features
2. ✅ **Create a new user** - Test signup functionality
3. ✅ **Submit a report** - Test the full workflow
4. ✅ **Switch to Arabic** - Test RTL layout
5. ✅ **Login as admin** - Test report management

### 📝 Notes

- The website shares the same data structure as your Flutter app
- All features from the mobile app are available
- The file-based database is perfect for development/testing
- For production, you can easily migrate to MySQL or keep using files
- Images are stored locally in the `uploads/` folder

### 🎉 Success!

Your Salamtak website is now fully functional and ready to use!

**Quick Access:** http://localhost:8000/

Enjoy! 🚀
