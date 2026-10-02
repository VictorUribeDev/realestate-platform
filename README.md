# Real Estate Platform

A modern, open-source real estate property management platform built with PHP, MySQL, and Bootstrap 5. Features property listings, admin dashboard, contact forms, and responsive design.

## Features

- **Property Listings**: Browse properties with filters (type, operation)
- **Property Details**: Image galleries, video support, location maps
- **Admin Dashboard**: Manage properties and inquiries
- **Contact Forms**: Secure contact form with CSRF protection
- **Responsive Design**: Mobile-first approach with Bootstrap 5
- **Multi-language Ready**: Spanish/English support
- **SEO Optimized**: Meta tags, structured data

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache/Nginx with mod_rewrite
- Composer (for dependencies)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/real-estate-platform.git
   cd real-estate-platform
   ```

2. **Configure the database**
   ```bash
   # Import the database schema
   mysql -u root -p < database/schema.sql
   ```

3. **Configure environment**
   ```bash
   # Copy the example configuration
   cp includes/config.example.php includes/config.php
   
   # Edit config.php with your database credentials
   # Or set environment variables:
   # DB_HOST, DB_NAME, DB_USER, DB_PASS
   ```

4. **Set up web server**
   - Point document root to the project directory
   - Ensure `public/images/propiedades/` is writable
   - Enable mod_rewrite for clean URLs

5. **Create admin user**
   ```bash
   # Register first user via admin/register.php
   # Or insert directly into database:
   INSERT INTO usuarios (nombre, email, password, rol) 
   VALUES ('Admin', 'admin@example.com', '$2y$10$...', 'admin');
   ```

## Project Structure

```
├── admin/                 # Admin panel
│   ├── dashboard.php      # Main dashboard
│   ├── login.php          # Admin login
│   ├── register.php       # User registration
│   ├── agregar_publicacion.php  # Add property
│   ├── editar_publicacion.php   # Edit property
│   ├── eliminar_publicacion.php # Delete property
│   ├── mensajes.php       # Manage inquiries
│   └── logout.php         # Logout
├── includes/              # Shared components
│   ├── config.php         # Configuration (copy from config.example.php)
│   ├── config.example.php # Example configuration
│   ├── database.php       # Database connection
│   ├── auth.php           # Authentication helpers
│   ├── header.php         # Public header
│   ├── footer.php         # Public footer
│   ├── admin_header.php   # Admin header
│   └── admin_footer.php   # Admin footer
├── public/                # Public assets
│   ├── css/
│   │   └── styles.css
│   ├── js/
│   │   └── youtube-banner.js
│   └── images/
│       ├── logo.png
│       └── propiedades/   # Property images
├── index.php              # Homepage
├── propiedades.php        # Property listings
├── detalle.php            # Property detail
├── acerca.php             # About page
├── contacto.php           # Contact page
├── procesar_contacto.php  # Contact form handler
└── .htaccess              # URL rewriting
```

## Configuration

Key settings in `includes/config.php`:

| Setting | Description |
|---------|-------------|
| `DB_HOST` | Database host |
| `DB_NAME` | Database name |
| `DB_USER` | Database user |
| `DB_PASS` | Database password |
| `SITE_NAME` | Site name |
| `SITE_EMAIL` | Contact email |
| `DEFAULT_CURRENCY` | Default currency |
| `ITEMS_PER_PAGE` | Pagination limit |

## Security Features

- Prepared statements (PDO) for SQL injection prevention
- CSRF tokens on all forms
- Password hashing with bcrypt
- Session regeneration
- Input sanitization
- XSS protection with `htmlspecialchars()`

## Customization

### Branding
- Replace `public/images/logo.png` with your logo
- Update `SITE_NAME` in config.php
- Modify social media links in `includes/footer.php`

### Styling
- Edit `public/css/styles.css` for custom styles
- Bootstrap 5 variables can be overridden

### Languages
- Text strings are in PHP files
- Create language files for full i18n support

## Database Schema

Main tables:
- `publicaciones` - Properties
- `imagenes` - Property images
- `mensajes` - Contact inquiries
- `usuarios` - Admin users

See `database/schema.sql` for full schema.

## License

MIT License - see [LICENSE](LICENSE) for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## Support

For issues and feature requests, please use the [GitHub Issues](https://github.com/yourusername/real-estate-platform/issues) page.

---

**Note**: This is a template project. Remove any placeholder content (images, demo data) before deploying to production.