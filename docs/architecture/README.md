# Architecture Overview

## Clean Architecture Principles

Zagros CMMS follows **Clean Architecture** (also known as Hexagonal Architecture or Ports & Adapters) to ensure:

- **Independence from frameworks**: Business logic doesn't depend on Symfony or React
- **Testability**: Easy to test without UI, database, or external services
- **Independence from UI**: Can swap React with Vue or Angular
- **Independence from database**: Can swap PostgreSQL with MySQL or MongoDB
- **Independence from external services**: Business rules don't know about external APIs

## Architecture Layers

```
┌─────────────────────────────────────────────────────────┐
│                     UI Layer                            │
│  (Controllers, API Resources, React Components)         │
└─────────────────────────────────────────────────────────┘
                          │
                          ↓
┌─────────────────────────────────────────────────────────┐
│                Application Layer                        │
│     (Use Cases, Commands, Queries, DTOs)                │
└─────────────────────────────────────────────────────────┘
                          │
                          ↓
┌─────────────────────────────────────────────────────────┐
│                  Domain Layer                           │
│  (Entities, Value Objects, Domain Events, Interfaces)   │
└─────────────────────────────────────────────────────────┘
                          ↑
                          │
┌─────────────────────────────────────────────────────────┐
│              Infrastructure Layer                       │
│  (Doctrine Repositories, External APIs, Cache, Queue)   │
└─────────────────────────────────────────────────────────┘
```

### 1. Domain Layer (Core)

**Location**: `backend/src/Domain/`

The heart of the application containing:
- **Entities**: Core business objects (Asset, WorkOrder, etc.)
- **Value Objects**: Immutable objects (AssetCode, Priority, etc.)
- **Repository Interfaces**: Contracts for data access
- **Domain Events**: Events that occur in the domain
- **Domain Services**: Business logic that doesn't fit in entities

**Rules**:
- ❌ No framework dependencies (no Symfony, no Doctrine annotations)
- ❌ No database knowledge
- ✅ Pure PHP business logic
- ✅ Framework-agnostic

### 2. Application Layer (Use Cases)

**Location**: `backend/src/Application/`

Contains application-specific business rules:
- **Commands**: Write operations (CreateAsset, AssignWorkOrder)
- **Command Handlers**: Execute commands
- **Queries**: Read operations (GetAssetById, GetDashboardMetrics)
- **Query Handlers**: Execute queries
- **DTOs**: Data Transfer Objects for input/output

**Pattern**: CQRS (Command Query Responsibility Segregation)

### 3. Infrastructure Layer (Implementation)

**Location**: `backend/src/Infrastructure/`

Implements interfaces defined by Domain layer:
- **Doctrine Repositories**: Database access implementations
- **External Services**: Email, SMS, file storage
- **Cache**: Redis implementation
- **Queue**: Message queue implementation

### 4. UI Layer (Interface)

**Location**: `backend/src/UI/` and `frontend/src/`

User interface and API:
- **API Controllers**: REST endpoints
- **API Resources**: API Platform resources
- **React Components**: User interface
- **API Clients**: Frontend services

## CQRS Pattern

We separate read and write operations:

**Commands (Write)**:
```php
// CreateAssetCommand.php
class CreateAssetCommand {
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly string $categoryId
    ) {}
}

// CreateAssetHandler.php
class CreateAssetHandler {
    public function handle(CreateAssetCommand $command): Asset {
        // Business logic
    }
}
```

**Queries (Read)**:
```php
// GetAssetByIdQuery.php
class GetAssetByIdQuery {
    public function __construct(
        public readonly string $id
    ) {}
}

// GetAssetByIdHandler.php
class GetAssetByIdHandler {
    public function handle(GetAssetByIdQuery $query): AssetDTO {
        // Fetch and return data
    }
}
```

## Domain Events

Events represent things that have happened:

```php
// Domain/Asset/Event/AssetCreatedEvent.php
class AssetCreatedEvent {
    public function __construct(
        public readonly string $assetId,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}

// Infrastructure/EventListener/AssetCreatedListener.php
class AssetCreatedListener {
    public function __invoke(AssetCreatedEvent $event): void {
        // Send notification
        // Update statistics
        // etc.
    }
}
```

## Frontend Architecture

**Feature-Based Structure**:

```
src/features/
├── assets/
│   ├── components/     # UI components
│   ├── hooks/          # React hooks
│   ├── api/            # API client
│   ├── types/          # TypeScript types
│   └── utils/          # Helper functions
```

Each feature is self-contained and can be developed/tested independently.

## Data Flow

### Write Operation (Create Asset)

```
1. User submits form
   ↓
2. Frontend validates with Zod
   ↓
3. API call to POST /api/assets
   ↓
4. Controller receives request
   ↓
5. Creates CreateAssetCommand
   ↓
6. Command Bus dispatches to Handler
   ↓
7. Handler executes business logic
   ↓
8. Repository saves to database
   ↓
9. Domain event dispatched
   ↓
10. Response returned to frontend
```

### Read Operation (List Assets)

```
1. User navigates to assets page
   ↓
2. React Query fetches data
   ↓
3. API call to GET /api/assets
   ↓
4. Query Handler executes
   ↓
5. Repository fetches from database
   ↓
6. Data transformed to DTO
   ↓
7. JSON response returned
   ↓
8. React Query caches result
   ↓
9. Component renders data
```

## Security Layers

1. **Network**: Nginx with rate limiting
2. **Transport**: HTTPS in production
3. **Authentication**: JWT tokens
4. **Authorization**: RBAC with Voters
5. **Input Validation**: Symfony Validator + Zod
6. **Output Encoding**: Automatic by Symfony + React
7. **Database**: Doctrine ORM (prevents SQL injection)

## Scalability Considerations

- **Horizontal Scaling**: Stateless backend (JWT, no sessions)
- **Caching**: Redis for frequently accessed data
- **Database**: PostgreSQL with proper indexing
- **CDN**: Static assets served separately
- **Queue**: Symfony Messenger for async tasks

## Testing Strategy

```
┌─────────────────────────────────────┐
│         E2E Tests (5%)              │  ← Test complete user flows
├─────────────────────────────────────┤
│    Integration Tests (15%)          │  ← Test layer integration
├─────────────────────────────────────┤
│       Unit Tests (80%)              │  ← Test business logic
└─────────────────────────────────────┘
```

## Related Documentation

- [Database Schema](database-schema.md)
- [API Design](api-design.md)
- [ADRs](ADR/)
- [Deployment](../deployment/README.md)
