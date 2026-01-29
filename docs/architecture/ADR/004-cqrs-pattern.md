# ADR 004: CQRS Pattern Implementation

**Date**: 2026-01-28  
**Status**: Accepted  
**Decision Makers**: Architecture Team

## Context

We need a pattern to handle business operations that:
- Separates read and write concerns
- Makes intent explicit
- Enables different optimization strategies
- Supports audit logging and event sourcing
- Improves testability

## Decision

We will implement **CQRS (Command Query Responsibility Segregation)** pattern using Symfony Messenger.

## Pattern Overview

### Commands (Write Operations)
- Represent user intentions to change state
- **Examples**: CreateAsset, AssignWorkOrder, UpdateMaintenanceSchedule
- Return void or simple acknowledgment
- Can fail (validation, business rules)

### Queries (Read Operations)
- Request data without side effects
- **Examples**: GetAssetById, ListWorkOrders, GetDashboardMetrics
- Always succeed (return empty if not found)
- Can be cached aggressively

## Implementation

### Command Structure

```php
// Application/Asset/Command/CreateAssetCommand.php
final class CreateAssetCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly string $categoryId,
        public readonly string $locationId,
        public readonly ?string $serialNumber = null,
    ) {}
}
```

### Command Handler

```php
// Application/Asset/Command/CreateAssetHandler.php
final class CreateAssetHandler
{
    public function __construct(
        private readonly AssetRepositoryInterface $repository,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}
    
    public function __invoke(CreateAssetCommand $command): string
    {
        // Validate
        $this->validateAssetCode($command->code);
        
        // Create domain entity
        $asset = Asset::create(
            name: $command->name,
            code: new AssetCode($command->code),
            categoryId: $command->categoryId,
            locationId: $command->locationId
        );
        
        // Persist
        $this->repository->save($asset);
        
        // Dispatch domain event
        $this->eventDispatcher->dispatch(
            new AssetCreatedEvent($asset->getId())
        );
        
        return $asset->getId();
    }
}
```

### Query Structure

```php
// Application/Asset/Query/GetAssetByIdQuery.php
final class GetAssetByIdQuery
{
    public function __construct(
        public readonly string $id
    ) {}
}
```

### Query Handler

```php
// Application/Asset/Query/GetAssetByIdHandler.php
final class GetAssetByIdHandler
{
    public function __construct(
        private readonly AssetRepositoryInterface $repository,
    ) {}
    
    public function __invoke(GetAssetByIdQuery $query): ?AssetDTO
    {
        $asset = $this->repository->findById($query->id);
        
        if (!$asset) {
            return null;
        }
        
        return AssetDTO::fromEntity($asset);
    }
}
```

### Controller Integration

```php
// UI/API/Controller/AssetController.php
#[Route('/api/assets', methods: ['POST'])]
public function create(Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    
    $command = new CreateAssetCommand(
        name: $data['name'],
        code: $data['code'],
        categoryId: $data['category_id'],
        locationId: $data['location_id']
    );
    
    $assetId = $this->commandBus->dispatch($command);
    
    return new JsonResponse(['id' => $assetId], 201);
}

#[Route('/api/assets/{id}', methods: ['GET'])]
public function get(string $id): JsonResponse
{
    $query = new GetAssetByIdQuery($id);
    $asset = $this->queryBus->dispatch($query);
    
    return new JsonResponse($asset);
}
```

## Symfony Messenger Configuration

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        buses:
            command.bus:
                middleware:
                    - validation
                    - doctrine_transaction
            query.bus:
                middleware:
                    - validation
        
        routing:
            'App\Application\*\Command\*Command': command.bus
            'App\Application\*\Query\*Query': query.bus
```

## Consequences

### Positive
✅ **Clear Intent**: Commands/Queries express what users want  
✅ **Single Responsibility**: Each handler does one thing  
✅ **Testability**: Easy to test handlers in isolation  
✅ **Scalability**: Read and write can be scaled separately  
✅ **Optimization**: Different strategies for reads vs writes  
✅ **Audit Trail**: Easy to log all commands  
✅ **Event Sourcing**: Foundation for event sourcing if needed  

### Negative
❌ **More Classes**: More files than traditional CRUD  
❌ **Boilerplate**: Similar code patterns repeated  
❌ **Learning Curve**: Team needs to understand the pattern  

### Mitigation
- Create code generators for commands/queries
- Provide templates and examples
- Document common patterns
- Use IDE snippets

## CQRS vs Event Sourcing

**Important**: We're doing CQRS, **not** Event Sourcing.

- ✅ CQRS: Separate read/write models
- ❌ Event Sourcing: Store events instead of state

Event Sourcing can be added later if needed.

## Read Model Optimization

For complex queries, we can use:

**1. Database Views**
```sql
CREATE VIEW asset_summary AS
SELECT a.id, a.name, COUNT(wo.id) as work_order_count
FROM assets a
LEFT JOIN work_orders wo ON wo.asset_id = a.id
GROUP BY a.id;
```

**2. Materialized Views**
```sql
CREATE MATERIALIZED VIEW dashboard_metrics AS
SELECT /* complex aggregations */;

-- Refresh periodically
REFRESH MATERIALIZED VIEW dashboard_metrics;
```

**3. Separate Read Database** (Future)
- Write to PostgreSQL
- Replicate to read-optimized store
- Eventual consistency acceptable for reports

## Validation Strategy

Commands are validated at multiple levels:

1. **DTO Validation**: Symfony Validator
2. **Business Rules**: In handler
3. **Domain Rules**: In entity

```php
// In handler
if ($this->repository->existsByCode($command->code)) {
    throw new AssetCodeAlreadyExistsException();
}
```

## Error Handling

Commands can fail:
```php
try {
    $this->commandBus->dispatch($command);
} catch (ValidationException $e) {
    return new JsonResponse(['errors' => $e->getErrors()], 400);
} catch (DomainException $e) {
    return new JsonResponse(['error' => $e->getMessage()], 422);
}
```

Queries rarely fail (return null/empty):
```php
$result = $this->queryBus->dispatch($query);
if (!$result) {
    return new JsonResponse(['error' => 'Not found'], 404);
}
```

## Alternatives Considered

### 1. Traditional CRUD Services
**Rejected** because:
- Mixed read/write logic
- Less testable
- Harder to optimize separately

### 2. Full Event Sourcing
**Rejected** because:
- Too complex for initial version
- Can add later if needed

## Examples in the System

### Commands
- `CreateAssetCommand`
- `UpdateAssetCommand`
- `DeleteAssetCommand`
- `AssignWorkOrderCommand`
- `CompleteWorkOrderCommand`
- `CreateMaintenanceScheduleCommand`

### Queries
- `GetAssetByIdQuery`
- `ListAssetsQuery`
- `GetAssetHistoryQuery`
- `GetDashboardMetricsQuery`
- `GetWorkOrdersByStatusQuery`

## References

- [CQRS by Martin Fowler](https://martinfowler.com/bliki/CQRS.html)
- [Symfony Messenger](https://symfony.com/doc/current/messenger.html)
- [CQRS Pattern](https://docs.microsoft.com/en-us/azure/architecture/patterns/cqrs)
