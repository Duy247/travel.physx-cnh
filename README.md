# Travel PhysX CNH

A travel website project built with PHP.

## Project Structure

```
travel.physx-cnh/
├── public/          # Web root directory
│   ├── index.php    # Main entry point
│   └── .htaccess    # Apache configuration
├── src/             # Application source code
├── config/          # Configuration files
├── composer.json    # PHP dependencies
└── README.md        # This file
```

## Requirements

- PHP 7.4 or higher
- Apache/Nginx web server
- Composer (for dependency management)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Duy247/travel.physx-cnh.git
   cd travel.physx-cnh
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure your web server to point to the `public/` directory

## Deployment

### For Hostinger:

1. Upload all files to your hosting account
2. Point your domain to the `public/` directory
3. Ensure PHP version is 7.4 or higher
4. The `.htaccess` file will handle URL rewriting

## Development

To run locally:
```bash
composer start
```

This will start a development server at `http://localhost:8000`

## Features

- Modern PHP project structure
- Security headers configured
- Gzip compression enabled
- Browser caching optimized
- Clean URL routing ready

## License

MIT License
