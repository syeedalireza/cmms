# Changelog

All notable changes to Zagros CMMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial project structure with Docker Compose
- Clean Architecture implementation with Domain, Application, Infrastructure, and UI layers
- User authentication and authorization with JWT
- Role-Based Access Control (RBAC) with Security Voters
- Asset Management module (backend)
  - Asset entity with code, status, criticality level
  - Create and retrieve assets via API
  - Asset repository pattern
- PostgreSQL database schema with migrations
- Redis caching infrastructure
- Elasticsearch integration (ready for future search features)
- Demo data fixtures for quick start
- API Platform integration for auto-generated REST API
- React frontend with TypeScript and Tailwind CSS
- Login functionality
- Comprehensive documentation (README, ADRs, Architecture docs)

### Security
- JWT token authentication
- Password hashing with bcrypt
- No database ports exposed in Docker
- Redis password protected
- Security headers configured in Nginx
- Input validation on all endpoints

## [1.0.0] - TBD

### Planned Features
- Asset Management UI
- Work Order Management (backend + frontend)
- Preventive Maintenance Scheduling
- Inventory & Parts Management
- Analytics Dashboard with MTBF/MTTR metrics
- Reporting & Export (PDF, Excel)
- Notification system (Email, In-app)
- Mobile PWA
- IoT sensor integration
- AI-powered predictive maintenance

---

For detailed information about architectural decisions, see [docs/architecture/ADR/](docs/architecture/ADR/)
