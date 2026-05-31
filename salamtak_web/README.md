# Salamtak Web Application

A complete PHP web application that replicates all functionality from the Flutter Salamtak mobile app.

## Features

### User Features
- ✅ Login/Signup with National ID
- ✅ Dashboard with statistics (Total, Pending, In Progress, Resolved)
- ✅ Report problems (Pothole, Broken Pipe, Other)
- ✅ Upload photos for reports
- ✅ Select location on interactive map
- ✅ View report history
- ✅ Multi-language support (English/Arabic with RTL)
- ✅ Account management

### Admin Features
- ✅ Admin dashboard with all reports
- ✅ Filter reports by status
- ✅ Update report status (Pending, In Progress, Resolved)
- ✅ View statistics
- ✅ Multi-language support

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### Setup Steps

1. **Copy files to web server**
   ```bash
   cp -r salamtak_web /path/to/htdocs/
   ```

2. **Create database**
   - Open phpMyAdmin or MySQL command line
   - Import the `database.sql` file:
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Configure database connection**
   - Edit `config.php`
   - Update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'salamtak_db');
   ```

4. **Set permissions**
   ```bash
   chmod 755 salamtak_web
   chmod 777 salamtak_web/uploads
   ```

5. **Access the application**
   - Open browser: `http://localhost/salamtak_web/`

## Default Credentials

### Admin Account
- National ID: `12345678901234`
- Password: `admin123456`

### Test User Account
- National ID: `11111111111111`
- Password: `user123456`

## File Structure

```
salamtak_web/
├── config.php              # Database & session configuration
├── database.sql            # Database schema & default data
├── translations.php        # English & Arabic translations
├── index.php              # Entry point (redirects based on role)
├── login.php              # Login page
├── signup.php             # User registration
├── logout.php             # Logout handler
├── assets/
│   └── css/
│       └── style.css      # All styles (responsive, RTL support)
├── user/
│   ├── dashboard.php      # User home with statistics
│   ├── services.php       # Problem type selection
│   ├── report.php         # Report submission form
│   ├── history.php        # User's report history
│   ├── account.php        # Account & language settings
│   └── includes/
│       ├── header.php     # Common header
│       └── nav.php        # Bottom navigation
├── admin/
│   └── dashboard.php      # Admin control panel
└── uploads/               # Uploaded images storage
```

## Features Breakdown

### Authentication
- National ID-based login (14 digits)
- Password hashing with bcrypt
- Session management
- Hardcoded admin/test user support

### User Dashboard
- Real-time statistics
- Quick report button
- Status legend
- Responsive design

### Report Submission
- Photo upload with preview
- Interactive map (OpenStreetMap/Leaflet)
- Location selection with reverse geocoding
- Severity levels (Low, Medium, High, Critical)
- Form validation

### Report History
- List all user reports
- Status badges with colors
- Timestamps and locations
- Empty state handling

### Admin Dashboard
- View all reports
- Filter by status (All, Pending, In Progress, Resolved)
- Update report status
- Statistics overview
- Responsive tabs

### Multi-language Support
- English and Arabic
- RTL layout for Arabic
- Language switcher in account page
- Persistent language preference

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL with PDO
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Maps**: Leaflet.js with OpenStreetMap
- **Icons**: SVG icons (inline)
- **Responsive**: Mobile-first design

## Security Features

- Password hashing (bcrypt)
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- Session-based authentication
- CSRF protection ready
- File upload validation

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Customization

### Change Colors
Edit `assets/css/style.css` CSS variables:
```css
:root {
    --primary: #3B82F6;
    --success: #10B981;
    --warning: #F59E0B;
    --danger: #EF4444;
}
```

### Add New Problem Types
Edit `user/services.php`:
```php
$problem_types = [
    ['type' => 'New Type', 'icon' => 'icon-name', 'color' => 'primary']
];
```

### Modify Translations
Edit `translations.php` to add/modify translations.

## Troubleshooting

### Database Connection Error
- Check database credentials in `config.php`
- Ensure MySQL service is running
- Verify database exists

### Upload Directory Error
- Create `uploads/` folder
- Set permissions: `chmod 777 uploads`

### Map Not Loading
- Check internet connection (requires external map tiles)
- Verify Leaflet.js CDN is accessible

### Session Issues
- Ensure `session_start()` is called
- Check PHP session configuration
- Clear browser cookies

## Production Deployment

1. **Security**
   - Change default admin password
   - Use HTTPS
   - Set proper file permissions (755 for folders, 644 for files)
   - Enable CSRF protection
   - Add rate limiting

2. **Performance**
   - Enable PHP OPcache
   - Use CDN for static assets
   - Optimize images
   - Enable gzip compression

3. **Database**
   - Regular backups
   - Use connection pooling
   - Add indexes for performance

## License

This project is created for educational purposes.

## Support

For issues or questions, please refer to the documentation or contact the development team.
