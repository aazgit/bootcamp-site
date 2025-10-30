# Kala-Klub Figma EDU Bootcamp Website

A professional, modern website for Kala-Klub's Figma EDU Bootcamp designed to support educational applications. Built with clean HTML, CSS, JavaScript, and PHP for easy deployment on shared hosting platforms.

## 🎯 Project Overview

This website serves as a comprehensive platform for the Kala-Klub Figma EDU Bootcamp, featuring:

- **Professional Design**: Modern glassmorphism effects with soft, neutral color palette
- **Responsive Layout**: Mobile-first design that works on all devices
- **Secure Forms**: PHP-based application and contact forms with validation
- **SEO Optimized**: Proper meta tags, structured data, and sitemap
- **Accessibility**: WCAG compliant with keyboard navigation and screen reader support

## 🏗️ Technical Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 7.4+ (compatible with shared hosting)
- **Styling**: Custom CSS with glassmorphism effects
- **Forms**: Server-side PHP processing with validation
- **SEO**: JSON-LD structured data, sitemap.xml, robots.txt

## 📁 Project Structure

```
/
├── index.html              # Home page
├── about.html             # Program information
├── curriculum.html        # Course curriculum
├── instructors.html       # Instructor profiles
├── schedule.html          # Cohort schedules
├── apply.php              # Application form
├── apply-success.html     # Application success page
├── contact.php            # Contact form
├── robots.txt             # Search engine directives
├── sitemap.xml            # Site structure for SEO
├── css/
│   └── main.css           # Main stylesheet
├── js/
│   └── main.js            # JavaScript functionality
├── assets/
│   └── images/            # Image assets (placeholder)
├── downloadables/
│   └── download.php       # Secure file download handler
└── applications/          # Application data storage (created automatically)
```

## 🚀 Deployment Instructions

### cPanel/Shared Hosting Deployment

1. **Upload Files**
   ```
   - Connect to your hosting via FTP or cPanel File Manager
   - Upload all files to your public_html directory
   - Maintain the exact folder structure shown above
   ```

2. **Set File Permissions**
   ```
   Files (.html, .php, .css, .js): 644
   Directories: 755
   Specifically for applications/ directory: 755 (will be created automatically)
   ```

3. **PHP Version Requirements**
   - Minimum: PHP 7.4
   - Recommended: PHP 8.0 or newer
   - Ensure the following PHP extensions are enabled:
     - mail() function
     - file_put_contents() 
     - fopen/fwrite functions

4. **Email Configuration**
   - Update email addresses in `apply.php` and `contact.php`
   - Configure SMTP settings if using advanced email features
   - Test mail functionality after deployment

5. **Domain Configuration**
   - Update all instances of `https://kala-klub.com` with your actual domain
   - Update canonical URLs in all HTML files
   - Update sitemap.xml with your domain

### Environment-Specific Setup

#### Local Development
```bash
# Using PHP built-in server for testing
php -S localhost:8000

# Or using XAMPP/WAMP/MAMP
# Place files in htdocs/www directory
```

#### Production Checklist
- [ ] Update all email addresses to real addresses
- [ ] Replace placeholder content with actual information  
- [ ] Test all forms (application and contact)
- [ ] Verify download functionality
- [ ] Test on mobile devices
- [ ] Run SEO audit
- [ ] Set up analytics (optional)

## 📧 Form Configuration

### Application Form (`apply.php`)
Update these variables in the configuration section:
```php
'admin_email' => 'your-admissions@domain.com',
'from_email' => 'noreply@yourdomain.com',
```

### Contact Form (`contact.php`)
Update these variables:
```php
'to_email' => 'your-info@domain.com',
'from_email' => 'noreply@yourdomain.com',
```

## 🔒 Security Features

- **CSRF Protection**: Honeypot fields in all forms
- **Rate Limiting**: Prevents form spam and abuse
- **Input Sanitization**: All user inputs are cleaned and validated
- **XSS Protection**: Output escaping for all user data
- **File Access Control**: Secure download system with logging

## 🎨 Customization Guide

### Colors and Branding
The main color scheme is defined in `css/main.css`:
```css
/* Primary Colors */
--primary-blue: #3498db;
--primary-green: #27ae60;
--primary-purple: #9b59b6;

/* Neutral Colors */
--text-dark: #2c3e50;
--text-medium: #34495e;
--text-light: #666;
```

### Adding New Pages
1. Create new HTML file following existing structure
2. Update navigation in all files
3. Add to sitemap.xml
4. Test responsive design

### Modifying Forms
1. Update HTML form fields
2. Adjust PHP validation rules
3. Modify email templates
4. Test thoroughly

## 🔍 SEO Features

### Structured Data
- Educational Organization schema
- Course schema with detailed information
- Local Business schema for location

### Meta Tags
- Proper title tags and meta descriptions
- Open Graph tags for social sharing
- Canonical URLs for duplicate content prevention

### Performance
- Optimized CSS with mobile-first approach
- Minimal JavaScript footprint
- Image optimization guidelines included

## 📱 Browser Support

- **Modern Browsers**: Full feature support including glassmorphism effects
- **Older Browsers**: Graceful degradation with fallback styles
- **Mobile Browsers**: Full responsive support

### Tested On:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile Safari (iOS 14+)
- Chrome Mobile (Android 10+)

## 🧪 Testing Checklist

### Functionality Tests
- [ ] All navigation links work correctly
- [ ] Forms submit and validate properly
- [ ] Download system functions correctly
- [ ] Email delivery works (test in production)
- [ ] Mobile navigation operates smoothly

### Cross-Browser Tests
- [ ] Layout renders correctly in all supported browsers
- [ ] Glassmorphism effects display properly (with fallbacks)
- [ ] Forms work in all browsers
- [ ] JavaScript functions properly

### Mobile Tests
- [ ] Responsive design on various screen sizes
- [ ] Touch targets are appropriately sized
- [ ] Text is readable without zooming
- [ ] Forms are easy to fill on mobile

### SEO Tests
- [ ] All pages have unique, descriptive titles
- [ ] Meta descriptions are under 160 characters
- [ ] Structured data validates correctly
- [ ] Sitemap.xml is accessible and valid
- [ ] robots.txt functions properly

## 🐛 Troubleshooting

### Common Issues

1. **Forms not sending emails**
   - Check PHP mail configuration
   - Verify email addresses are correct
   - Test with a simple PHP mail script
   - Check spam folders

2. **Glassmorphism effects not showing**
   - Verify browser support for `backdrop-filter`
   - Check CSS fallbacks are in place
   - Ensure proper CSS loading

3. **File downloads not working**
   - Check file permissions on downloadables/ directory
   - Verify PHP has write permissions
   - Check error logs for specific issues

4. **Mobile layout issues**
   - Verify viewport meta tag is present
   - Check CSS media queries
   - Test on actual devices, not just browser tools

### Log Files
- Application submissions: `applications/applications.csv`
- Download activity: `downloadables/downloads.log`
- Server errors: Check cPanel error logs

## 📞 Support

For technical issues or questions about deployment:

- **Email**: technical-support@domain.com
- **Documentation**: This README file
- **Testing**: Use provided checklist above

## 📄 License & Credits

- **Copyright**: © 2025 TheGaurav.in
- **Design**: Custom glassmorphism design
- **Code**: Clean, standards-compliant HTML/CSS/PHP
- **Fonts**: System fonts for optimal loading speed

---

## 🚀 Quick Start

1. Upload files to `public_html/` directory
2. Update email addresses in PHP files
3. Update domain in all files
4. Test forms and functionality
5. Submit to search engines

Your professional bootcamp website is ready to launch! 🎉