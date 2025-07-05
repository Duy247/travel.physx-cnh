# Admin Backend System

This document describes the administration backend system for the travel application.

## Overview

The admin backend provides secure access to edit JSON data files in the `public/data/` directory. It implements the following security measures:

1. Password protection using date-based password or custom password
2. Session-based authentication
3. File path validation to prevent directory traversal attacks
4. JSON validation to ensure data integrity
5. Automatic backups before saving files
6. Protection against direct access to sensitive files

## Access

To access the admin panel, navigate to `/admin.php` in your browser. You will be prompted for a password.

The default password is the current date in the format `dd/MM/yyyy` (e.g., `05/07/2025`).

You can set a custom password in the admin panel's Password Settings section.

## Features

- Browse and edit all JSON files in the `public/data/` directory
- Format JSON data for better readability
- Validate JSON data before saving
- Create automatic backups before saving
- Set custom admin password

## Security Notes

- The admin.php file should be protected by server configuration (Apache .htaccess or Nginx location directives)
- Always access the admin panel over HTTPS if available
- Logout when finished to invalidate your session
- Consider setting up IP restrictions for additional security

## File Structure

- `admin.php` - The main admin interface
- `includes/admin_security.php` - Security middleware for admin functions
- `includes/password_manager.php` - Password management utilities
- `includes/api_security.php` - Security checks for API files
- `public/data/.htaccess` - Protection for data files
- `.htaccess` - Server-level protection

## Password Management

You can change from the default date-based password to a custom password in the Password Settings section.
This will create a `password.json` file in the `public/data/` directory.

To reset to the default password, delete the `password.json` file or set an empty password.

## Troubleshooting

If you're locked out of the admin panel:

1. Delete the `public/data/password.json` file to revert to date-based password
2. Use the current date in `dd/MM/yyyy` format as the password
