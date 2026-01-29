# Getting Started with Zagros CMMS

Welcome! This guide will help you get Zagros CMMS up and running on your local machine.

## Prerequisites

Before you begin, ensure you have the following installed:

- **Docker Desktop** (Windows/Mac) or **Docker + Docker Compose** (Linux)
  - Version 20.10 or higher
  - Download: https://www.docker.com/products/docker-desktop

- **Git**
  - Version 2.0 or higher
  - Download: https://git-scm.com/downloads

## Quick Start (5 minutes)

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/zagros-cmms.git
cd zagros-cmms
```

### 2. Configure Environment

```bash
# Copy the example environment file
cp .env.example .env

# Edit .env and set secure passwords
# On Windows: notepad .env
# On Mac/Linux: nano .env
```

**Important**: Change these values in `.env`:
```env
DB_PASSWORD=your_secure_postgres_password
REDIS_PASSWORD=your_secure_redis_password
JWT_SECRET=your_jwt_secret_min_32_characters
JWT_PASSPHRASE=your_jwt_passphrase
APP_SECRET=your_app_secret_here
```

### 3. Start the Application

```bash
docker-compose up -d
```

This will:
- Download all required Docker images
- Start PostgreSQL, Redis, Elasticsearch
- Start backend (Symfony) and frontend (React)
- Set up networking

### 4. Initialize the Database

```bash
# Run migrations to create tables
docker-compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Load demo data
docker-compose exec backend php bin/console app:seed-demo-data
```

### 5. Access the Application

Open your browser and navigate to:
- **Frontend**: http://localhost
- **API Documentation**: http://localhost/api/docs

### 6. Login

Use these demo credentials:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@zagros.test | admin123 |
| Manager | reza.ahmadi@zagros.test | manager123 |
| Technician | hassan.rezaei@zagros.test | tech123 |

---

## What's Next?

### Explore the Application

1. **Dashboard**: View system overview and metrics
2. **Assets**: Browse the equipment list
3. **Work Orders**: Create and manage maintenance tasks
4. **Settings**: Configure your preferences

### Learn the Features

- [User Guide](USER_GUIDE.md) - How to use the application
- [Architecture](architecture/README.md) - Understand the codebase
- [API Documentation](architecture/api-design.md) - Work with the API

### Start Developing

- [Contributing Guide](../CONTRIBUTING.md) - How to contribute
- [Development Workflow](deployment/README.md#development-workflow) - Local development tips

---

## Troubleshooting

### Docker Containers Won't Start

```bash
# Check if ports are already in use
docker-compose ps

# View logs
docker-compose logs

# Restart services
docker-compose restart
```

### Database Connection Issues

```bash
# Check PostgreSQL is running
docker-compose ps postgres

# View PostgreSQL logs
docker-compose logs postgres

# Restart PostgreSQL
docker-compose restart postgres
```

### Frontend Build Issues

```bash
# Access frontend container
docker-compose exec frontend sh

# Reinstall dependencies
npm install

# Rebuild
npm run build
```

### Permission Issues (Linux)

```bash
# Fix ownership
sudo chown -R $USER:$USER .

# Fix backend permissions
docker-compose exec backend chown -R www-data:www-data /var/www/backend/var
```

### Clear Everything and Start Fresh

```bash
# Stop and remove all containers, volumes
docker-compose down -v

# Remove all data (WARNING: This deletes everything!)
rm -rf backend/var/cache backend/var/log

# Start again
docker-compose up -d
```

---

## Common Commands

### Docker Management

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose stop

# View logs (all services)
docker-compose logs -f

# View logs (specific service)
docker-compose logs -f backend

# Access container shell
docker-compose exec backend bash
docker-compose exec frontend sh

# Restart a service
docker-compose restart backend
```

### Backend (Symfony)

```bash
# Run migrations
docker-compose exec backend php bin/console doctrine:migrations:migrate

# Clear cache
docker-compose exec backend php bin/console cache:clear

# Run tests
docker-compose exec backend php bin/phpunit

# Create new user
docker-compose exec backend php bin/console app:create-user
```

### Frontend (React)

```bash
# Install packages
docker-compose exec frontend npm install

# Run tests
docker-compose exec frontend npm test

# Build for production
docker-compose exec frontend npm run build
```

### Database

```bash
# Access PostgreSQL CLI
docker-compose exec postgres psql -U cmms -d zagros_cmms

# Backup database
docker-compose exec postgres pg_dump -U cmms zagros_cmms > backup.sql

# Restore database
docker-compose exec -T postgres psql -U cmms zagros_cmms < backup.sql
```

---

## Development Tips

### Hot Reload

Both frontend and backend support hot reload:
- **Frontend**: Changes to `.tsx` files automatically reload
- **Backend**: Changes to `.php` files take effect immediately (no rebuild needed)

### Debugging

#### Backend (PHP)
- Xdebug is available in development
- Configure your IDE to connect to port 9003

#### Frontend (React)
- React DevTools extension recommended
- TanStack Query DevTools included

### Code Quality

```bash
# Backend
docker-compose exec backend vendor/bin/phpstan analyse
docker-compose exec backend vendor/bin/php-cs-fixer fix

# Frontend
docker-compose exec frontend npm run lint
docker-compose exec frontend npm run format
```

---

## Next Steps

- **Production Deployment**: See [Deployment Guide](deployment/README.md)
- **API Integration**: See [API Design](architecture/api-design.md)
- **Contributing**: See [Contributing Guide](../CONTRIBUTING.md)

## Need Help?

- 📖 [Documentation](../README.md)
- 💬 [Discussions](https://github.com/yourusername/zagros-cmms/discussions)
- 🐛 [Report Issues](https://github.com/yourusername/zagros-cmms/issues)

---

**Happy Coding! 🚀**
