# Laravel 11 Docker Development Environment

A Laravel 11 application configured with Docker for local development.

## Stack

- **Laravel**: 11.x
- **PHP**: 8.3-fpm
- **Database**: MySQL 8.4
- **Web Server**: Nginx (Alpine)
- **Mail Testing**: MailPit

## Prerequisites

- Docker
- Docker Compose
- Make (optional, for using Makefile commands)

## Quick Start

1. **Clone the repository** (if applicable)
   ```bash
   git clone <repository-url>
   cd learnLaravel
   ```

2. **Configure environment variables**

   Copy `.env.example` to `.env` if needed (already done during setup):
   ```bash
   cp .env.example .env
   ```

   Adjust port configurations in `.env` if you have conflicts:
   ```
   APP_PORT=8000           # Web application port
   MYSQL_PORT=3306         # MySQL database port
   MAILPIT_WEB_PORT=8025   # MailPit web UI port
   MAILPIT_SMTP_PORT=1025  # MailPit SMTP port
   ```

3. **Start the Docker containers**
   ```bash
   make up
   ```

   Or without Make:
   ```bash
   docker-compose up -d
   ```

4. **Run database migrations**
   ```bash
   make migrate
   ```

   Or without Make:
   ```bash
   docker-compose exec app php artisan migrate
   ```

5. **Access the application**
   - Web Application: [http://localhost:8000](http://localhost:8000)
   - MailPit UI: [http://localhost:8025](http://localhost:8025)

## Available Make Commands

The project includes a Makefile with common development tasks:

### Container Management
```bash
make up              # Start all containers
make down            # Stop all containers
make restart         # Restart all containers
make build           # Build/rebuild containers
make ps              # Show running containers
make logs            # View all container logs
make logs-app        # View app container logs
make logs-web        # View webserver logs
make logs-db         # View database logs
```

### Development
```bash
make bash            # Open bash shell in app container
make tinker          # Open Laravel tinker
```

### Database
```bash
make migrate         # Run database migrations
make migrate-fresh   # Fresh database migrations (drops all tables)
make migrate-seed    # Run migrations with seeders
make fresh           # Fresh database with seeders
make db-bash         # Open MySQL shell
```

### Laravel Commands
```bash
make cache-clear     # Clear all Laravel caches
make key-generate    # Generate application key
make test            # Run PHPUnit tests
```

### Composer
```bash
make composer-install  # Install composer dependencies
make composer-update   # Update composer dependencies
```

### Cleanup
```bash
make clean           # Remove all containers, volumes, and images
```

### Help
```bash
make help            # Show all available commands
```

## Project Structure

```
.
├── app/                    # Laravel application code
│   ├── Contracts/          # Service interfaces
│   ├── Customers/          # Tenant-specific implementations
│   │   └── WayneEnt/       # WayneEnt tenant customizations
│   ├── Http/
│   │   ├── Controllers/    # Request handlers
│   │   └── Middleware/     # HTTP middleware (including tenant resolution)
│   ├── Services/           # Business logic services
│   └── Tenancy/            # Tenant resolution and management
├── bootstrap/              # Laravel bootstrap files
├── config/                 # Configuration files
├── database/               # Migrations, seeders, factories
├── public/                 # Public web directory
├── resources/              # Views, raw assets
│   ├── css/
│   ├── js/
│   └── views/
├── routes/                 # Route definitions
├── storage/                # Logs, cache, uploaded files
├── tests/                  # Automated tests
├── nginx/                  # Nginx configuration
│   └── default.conf
├── docker-compose.yml      # Docker services configuration
├── Dockerfile              # PHP application container
├── Makefile                # Development shortcuts
├── TENANT_ARCHITECTURE.md  # Tenant system documentation
└── .env                    # Environment configuration
```

## Multi-Tenant Architecture

This application implements a **tenant-based service customization** pattern that allows different customers to have slightly different implementations of the same functionality without cluttering the codebase with conditional logic.

### Key Features

- **Interface-based design**: Services are defined by contracts
- **Runtime service binding**: Tenant-specific implementations are swapped at request time
- **Clean separation**: Customer code isolated in `app/Customers/{TenantId}/`
- **Flexible resolution**: Tenants detected via headers, subdomains, or configuration

### Quick Example

```bash
# Default health check
curl http://localhost:8000/api/status

# WayneEnt-specific health check (custom implementation)
curl -H "X-Tenant-Id: WayneEnt" http://localhost:8000/api/status
```

### Current Tenants

- **AcMe**: Uses default implementations
- **Beta**: Uses default implementations
- **WayneEnt**: Custom health service with bat signal monitoring

### Documentation

For complete documentation on the tenant architecture, including:
- How it works
- Adding new tenants
- Creating tenant-specific services
- Testing strategies

See **[TENANT_ARCHITECTURE.md](TENANT_ARCHITECTURE.md)**

### Adding a New Tenant

1. Add tenant to valid list in `app/Tenancy/TenantResolver.php`
2. Create directory: `app/Customers/{NewTenant}/`
3. Implement custom services (optional)
4. Test with: `curl -H "X-Tenant-Id: NewTenant" http://localhost:8000/api/status`

## Frontend Development

This project is configured for **vanilla JavaScript and HTML** (no Vue.js or React).

- JavaScript files: `resources/js/`
- CSS files: `resources/css/`
- Blade templates: `resources/views/`

If you need to compile assets:
```bash
npm install
npm run dev
```

## Email Testing with MailPit

All emails sent by the application are captured by MailPit.

- **Web UI**: [http://localhost:8025](http://localhost:8025)
- **SMTP**: `mailpit:1025` (configured in `.env`)

No emails will be sent to real addresses during development.

## Troubleshooting

### Port Conflicts

If you get port conflict errors, update the ports in your `.env` file:
```env
APP_PORT=8080        # Change from 8000
MYSQL_PORT=3307      # Change from 3306
MAILPIT_WEB_PORT=8026  # Change from 8025
```

Then restart containers:
```bash
make down
make up
```

### Permission Issues

If you encounter permission errors:
```bash
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

### Clear All Caches

If you experience caching issues:
```bash
make cache-clear
```

### Fresh Start

To completely reset the environment:
```bash
make down
make clean
make build
make up
make migrate
```

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Documentation](https://docs.docker.com)
- [MailPit GitHub](https://github.com/axllent/mailpit)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# larevel-docstore
# larevel-docstore
