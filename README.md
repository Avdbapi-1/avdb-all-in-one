# AVDB All In One

A comprehensive WordPress plugin for managing and installing AVDB packages (plugins and themes) with automatic updates and smart detection.

## 🚀 Features

- **One-Click Installation**: Install plugins and themes directly from GitHub repositories
- **Automatic Updates**: Check for updates and install them with a single click
- **Smart Detection**: Automatically detects installed packages regardless of naming variations
- **Bulk Operations**: Install multiple packages at once
- **Self-Update**: The plugin can update itself through WordPress native update system
- **Visual Interface**: Modern card-based UI with hover effects and animations
- **Package Categories**: Separate official AVDB packages from third-party packages
- **Cache Management**: Built-in cache system for better performance

## 📦 Package Management

### Official AVDB Packages
- **AVDB Sample Plugin**: Demo plugin for testing
- **Adult API Crawler for WP-Script**: Advanced crawler for WP-Script
- **Crawl AVDBAPI Vidmov**: Specialized crawler for Vidmov theme
- **Adult API Crawler for Eroz Theme**: Crawler optimized for Eroz theme

### Third-Party Packages
- **Eroz Theme**: Modern adult content theme
- **Vidmov Theme**: Video-focused theme

## 🛠️ Installation

1. **Download** the plugin ZIP file
2. **Upload** to your WordPress site via Plugins > Add New > Upload Plugin
3. **Activate** the plugin
4. **Access** via the new "AVDB All In One" menu in your WordPress admin

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- File system write permissions
- Internet connection for package downloads

## 🎯 Usage

### Installing Packages

1. Navigate to **AVDB All In One** in your WordPress admin
2. Browse available packages in the card interface
3. Click **Install** on any package you want to install
4. The plugin will automatically download, install, and activate the package
5. Page will reload to show updated status

### Checking for Updates

1. Click **Check for Updates** button at the top of the page
2. The plugin will fetch the latest version information from the manifest
3. Packages with updates will show "Update available" status
4. Click **Update** button to install the latest version

### Bulk Installation

1. Select multiple packages using the checkboxes
2. Click **Install Selected** to install all selected packages at once
3. Progress and results will be displayed in real-time

## 🔧 Configuration

### Manifest URL
The plugin uses a central manifest file to manage package information:
```
https://help.avdbapi.com/manifest.json
```

### Package Structure
Each package in the manifest should have:
```json
{
  "slug": "package-slug",
  "version": "1.0.0",
  "download_url": "https://github.com/user/repo/releases/download/v1.0.0/package.zip",
  "name": "Package Name",
  "description": "Package description",
  "changelog": "Update changelog",
  "tested": "6.3.2"
}
```

## 🔍 Smart Detection

The plugin uses intelligent detection algorithms to identify installed packages:

- **Exact Name Matching**: Matches exact package names
- **Partial Name Matching**: Handles naming variations and abbreviations
- **Folder Name Detection**: Identifies packages by their installation folder
- **Case-Insensitive**: Works regardless of case differences

## 🔄 Update System

### WordPress Native Updates
- Plugin integrates with WordPress native update system
- Updates appear in Plugins page automatically
- One-click updates through WordPress admin

### Manual Updates
- Use the built-in update checker in the plugin interface
- Clear cache functionality for troubleshooting
- Real-time update status display

## 🎨 User Interface

### Modern Design
- Card-based layout with hover effects
- Responsive design for all screen sizes
- Loading animations and status indicators
- Image zoom functionality for package previews

### Status Indicators
- **Green**: Installed and up to date
- **Yellow**: Update available
- **Red**: Not installed
- **Blue**: Installation in progress

## 🛡️ Security

- **Permission Checks**: Only administrators can install/update packages
- **Nonce Verification**: CSRF protection for all AJAX operations
- **File Validation**: Validates downloaded packages before installation
- **Error Handling**: Comprehensive error handling and user feedback

## 🔧 Troubleshooting

### Common Issues

**Installation Fails**
- Check file system permissions
- Verify internet connection
- Clear plugin cache using "Clear Cache" button

**Update Not Detected**
- Click "Clear Cache" button
- Check manifest URL accessibility
- Verify package version in manifest

**Package Not Recognized**
- Check package naming in manifest
- Verify package structure in ZIP file
- Use "Clear Cache" to refresh detection

### Debug Mode
Enable debug logging by adding to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📝 Changelog

### Version 1.3.0
- Renamed to "AVDB All In One"
- Added self-update capability
- English interface
- WordPress native update integration
- Enhanced detection algorithms
- Improved error handling

### Version 1.2.0
- Added bulk installation
- Enhanced UI with card design
- Added package categories
- Improved update system

### Version 1.1.0
- Initial release
- Basic installation functionality
- Simple update checking

## 🤝 Support

For support and feature requests:
- **GitHub**: [https://github.com/Avdbapi-1/avdb-all-in-one](https://github.com/Avdbapi-1/avdb-all-in-one)
- **Documentation**: [https://help.avdbapi.com](https://help.avdbapi.com)

## 📄 License

This plugin is proprietary software developed by AVDB Team.

## 👥 Credits

- **Developed by**: AVDB Team
- **UI Design**: Inspired by modern card-based interfaces
- **Icons**: Custom designed for AVDB ecosystem

---

**AVDB All In One** - Simplifying WordPress package management since 2024. 