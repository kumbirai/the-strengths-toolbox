# The Strengths Toolbox

A modern, production-ready Laravel application featuring a complete CMS, blog system, contact forms, and booking integration.

## Features

- **Content Management System** - Full CMS for managing website content
- **Blog System** - Complete blog with categories, tags, and search
- **Contact Forms** - Integrated contact forms with Calendly integration
- **Booking Calendar** - Appointment booking system
- **Responsive Design** - Mobile-first, fully responsive interface
- **Security** - Rate limiting, CSRF protection, input validation, and XSS prevention
- **Performance** - Caching, lazy loading, database optimization, and image optimization
- **Testing** - Comprehensive test suite with feature tests, unit tests, and CI/CD pipeline

## Quick Start

### Prerequisites

- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher
- **npm**: 9.x or higher
- **MySQL**: 8.0 or higher
- **Git**: Latest version

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/kumbirai/the-strengths-toolbox.git
   cd the-strengths-toolbox
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set up database**
   
   Edit `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=strengthstoolbox
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```
   
   Create the database:
   ```bash
   mysql -u root -p
   CREATE DATABASE strengthstoolbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   exit
   ```

5. **Run migrations and build assets**
   ```bash
   php artisan migrate
   php artisan storage:link
   npm run build
   ```

6. **Seed database and content** (optional)
   ```bash
   php artisan db:seed
   ```
   To populate local images after seeding (Sales Courses and TSA blog featured images):
   ```bash
   php artisan content:download-sales-courses-images
   php artisan blog:download-tsa-images
   ```

## Running the Application

### Development Server

Start the development server:
```bash
php artisan serve
```

The application will be available at `https://localhost:8000`

To make it accessible from other devices on your network:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Full Development Environment

For development with hot reload, queue worker, and log viewer:
```bash
composer dev
```

This runs:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server with hot module replacement

### Stopping the Server

Press `Ctrl+C` in the terminal, or:
```bash
pkill -f "php artisan serve"
```

## Production Deployment

### Setup

1. **Configure production environment**
   ```bash
   php artisan setup:production
   # Edit .env with your production values
   ```

2. **Seed content** (optional)
   ```bash
   php artisan db:seed --class=ProductionContentSeeder
   ```

3. **Optimize assets**
   ```bash
   npm run build
   php artisan images:optimize --format=webp --quality=85
   ```

4. **Run tests**
   ```bash
   php artisan test
   php artisan test:forms --url=https://localhost:8000
   php artisan test:routes
   php artisan security:audit
   php artisan benchmark:performance
   ```

5. **Deploy**
   ```bash
   ./deploy.sh
   ```

For detailed deployment instructions, see `documentation/03-development/phase-04/DEPLOYMENT_GUIDE.md`

## Available Commands

### Setup
- `composer setup` - Complete setup (install dependencies, generate key, migrate, build assets)
- `php artisan key:generate` - Generate application encryption key
- `php artisan migrate` - Run database migrations
- `php artisan storage:link` - Create symbolic link for storage
- `php artisan setup:production` - Production setup

### Development
- `php artisan serve` - Start development server
- `composer dev` - Start full development environment
- `npm run dev` - Start Vite dev server with hot reload
- `npm run build` - Build production assets

### Testing
- `php artisan test` - Run all tests
- `php artisan test:forms` - Test form functionality
- `php artisan test:routes` - Test route accessibility
- `php artisan benchmark:performance` - Performance benchmarking
- `php artisan security:audit` - Security audit

### Maintenance
- `php artisan optimize:clear` - Clear all caches
- `php artisan config:clear` - Clear configuration cache
- `php artisan cache:clear` - Clear application cache
- `php artisan view:clear` - Clear compiled views
- `php artisan route:clear` - Clear route cache
- `php artisan backup:database` - Database backup
- `php artisan maintenance:manage` - Maintenance mode
- `php artisan images:optimize` - Optimize images

## Health Checks

- `GET /health` - Basic health check
- `GET /health/detailed` - Comprehensive system health

## Project Structure

```
the-strengths-toolbox/
├── app/                    # Application code
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Models/            # Eloquent models
│   ├── Repositories/      # Repository layer
│   ├── Services/          # Business logic
│   └── ...
├── database/              # Migrations, seeders, factories
├── public/                # Public assets and entry point
├── resources/             # Views, CSS, JS source files
├── routes/                # Route definitions
├── storage/               # Logs, cache, uploaded files
└── tests/                 # Test files
```

## Environment Configuration

Key environment variables for local development:

```env
APP_NAME="The Strengths Toolbox"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=strengthstoolbox
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=database
```

## Security & Performance

### Security Features
- Rate limiting on forms
- CSRF protection
- Input validation & sanitization
- XSS prevention
- Security audit command

### Performance Features
- Caching configured
- Lazy loading images
- Database optimization
- Image optimization
- Performance benchmarking

## Testing

The application includes comprehensive testing:
- Feature tests
- Unit tests
- Test factories
- CI/CD pipeline configured

Run tests with:
```bash
php artisan test
```

## Documentation

Comprehensive documentation is available in the `documentation/` directory:

- **Deployment Guide**: `documentation/03-development/phase-04/DEPLOYMENT_GUIDE.md`
- **Testing Guide**: `documentation/03-development/phase-04/TESTING_GUIDE.md`
- **Maintenance Procedures**: `documentation/03-development/phase-04/MAINTENANCE_PROCEDURES.md`
- **Production Readiness**: `documentation/03-development/phase-04/PRODUCTION_READINESS.md`
- **Architecture Documentation**: `documentation/01-architecture/`
- **Development Guides**: `documentation/03-development/`

## Troubleshooting

### Server won't start
- Check if port 8000 is already in use: `lsof -i :8000`
- Clear caches: `php artisan optimize:clear`
- Check PHP version: `php -v` (must be 8.2+)

### Database connection errors
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Assets not loading
- Rebuild assets: `npm run build`
- Clear view cache: `php artisan view:clear`
- Verify storage link: `php artisan storage:link`

### 500 errors
- Check logs: `tail -f storage/logs/laravel.log`
- Clear all caches: `php artisan optimize:clear`
- Verify permissions: `chmod -R 775 storage bootstrap/cache`

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Contributing

Please follow the project's coding standards and architecture guidelines as defined in the documentation.

---

For more information, see the complete documentation in the `documentation/` directory.
