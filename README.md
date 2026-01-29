# Zagros CMMS

A modern maintenance management system built with Symfony 7 and React 18. This project started as a way to learn clean architecture principles while building something actually useful for industrial maintenance teams.

## What is this?

CMMS stands for Computerized Maintenance Management System. Basically, it helps facilities and factories keep track of their equipment, schedule maintenance, manage work orders, and avoid costly breakdowns. 

I've been working on this using a proper clean architecture approach - separating business logic from infrastructure, using CQRS for commands and queries, and keeping everything testable. It's been a great learning experience so far.

## Current Status

Right now the project is in early alpha (v0.1). The foundation is solid:
- Backend API is running on Symfony 7 with proper DDD structure
- Frontend uses React 18 with TypeScript
- Everything runs in Docker containers (works great on Windows too)
- JWT authentication is working
- Basic asset management module is implemented

Still working on the UI for assets, work orders system, and the dashboard. But the architecture is there and ready to scale.

## Tech Stack

**Backend:**
- Symfony 7 (PHP 8.3)
- PostgreSQL 16 for data
- Redis for caching
- API Platform for REST endpoints
- Doctrine ORM with migrations
- JWT tokens for auth

**Frontend:**
- React 18 with TypeScript
- Vite for building (much faster than webpack)
- TailwindCSS + Shadcn components
- Zustand for state management
- TanStack Query for server data

**Infrastructure:**
- Docker Compose for local dev
- Nginx as reverse proxy
- Multi-stage Docker builds
- GitHub Actions for CI/CD

## Architecture

I'm following Clean Architecture and Domain-Driven Design patterns here. The backend is organized in layers:

```
backend/src/
├── Domain/          # Core business entities and rules
├── Application/     # Use cases (commands and queries)
├── Infrastructure/  # Database repos and external services  
└── UI/              # API controllers and resources
```

Frontend is feature-based, so each module (auth, assets, work orders) has its own folder with components, API calls, and state management.

## Getting Started

You'll need Docker Desktop installed. That's it.

1. Clone the repo
```bash
git clone https://github.com/syeedalireza/cmms.git
cd cmms
```

2. Copy the example env file and update it
```bash
cp .env.example .env
# Edit .env with your settings
```

3. Start everything
```bash
docker-compose up -d
```

4. Run database migrations
```bash
docker-compose exec backend php bin/console doctrine:migrations:migrate
```

5. Create an admin user
```bash
docker-compose exec backend php bin/console app:create-admin
```

The app should be running at http://localhost

API docs are available at http://localhost/api/docs

## Development

**Backend commands:**
```bash
# Install dependencies
docker-compose exec backend composer install

# Run migrations
docker-compose exec backend php bin/console doctrine:migrations:migrate

# Code quality checks
docker-compose exec backend vendor/bin/phpstan analyse
docker-compose exec backend vendor/bin/php-cs-fixer fix
```

**Frontend:**
```bash
cd frontend
npm install
npm run dev
```

## What's Next

The roadmap is pretty straightforward:

**Phase 1 (Done):**
- ✅ Docker setup with security best practices
- ✅ Clean architecture implementation
- ✅ Authentication and user management
- ✅ Basic asset module

**Phase 2 (In Progress):**
- Asset management UI
- Work order system
- Dashboard with metrics
- Preventive maintenance scheduling

**Phase 3 (Planned):**
- Inventory management
- Reporting tools
- Mobile optimization
- Email notifications

## Code Quality

I'm using PHPStan at level 8 for static analysis, PHP-CS-Fixer for code style (PSR-12), and TypeScript in strict mode. The code should be pretty clean and maintainable.

## Security

Some important security measures in place:
- Database ports are not exposed publicly
- Redis requires password authentication
- JWT tokens for API access
- Role-based access control (RBAC)
- All containers run as non-root users
- Input validation on all endpoints

## Documentation

There's more detailed documentation in the `docs/` folder:
- Architecture decisions and reasoning
- Database schema design
- API design patterns
- Deployment guides

## Contributing

If you want to contribute, feel free to open an issue or submit a pull request. Please follow the existing code style and architecture patterns.

## License

MIT License - feel free to use this for your own projects.

---

Built with Symfony, React, and a lot of coffee ☕
