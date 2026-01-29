# ADR 003: API Platform for REST API

**Date**: 2026-01-28  
**Status**: Accepted  
**Decision Makers**: Architecture Team

## Context

We need to expose a RESTful API for the frontend application and potential third-party integrations. The API should:
- Follow REST best practices
- Auto-generate OpenAPI documentation
- Support filtering, sorting, pagination
- Handle authentication and authorization
- Be maintainable and extensible

## Decision

We will use **API Platform 3.x** built on Symfony for our REST API implementation.

## Rationale

### Advantages

**1. Auto-Generation**
- REST endpoints from entity/resource definitions
- OpenAPI/Swagger documentation
- JSON-LD support for semantic APIs
- GraphQL API (optional future addition)

**2. Built-in Features**
- Pagination (configurable)
- Filtering and sorting
- Validation integration
- Serialization groups
- Versioning support

**3. Developer Experience**
- Less boilerplate code
- Type-safe with PHP 8+ attributes
- Integration with Symfony ecosystem
- Active community and documentation

**4. Standards Compliance**
- REST best practices
- JSON-LD for linked data
- Hydra for hypermedia
- OpenAPI 3.1 specification

## Example Implementation

### Resource Definition
```php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(security: "is_granted('ROLE_ADMIN')")
    ],
    normalizationContext: ['groups' => ['asset:read']],
    denormalizationContext: ['groups' => ['asset:write']],
    paginationItemsPerPage: 30
)]
class AssetResource
{
    // Properties and methods
}
```

### Custom Operations
```php
use ApiPlatform\Metadata\Post;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/assets/{id}/generate-qrcode',
            controller: GenerateQRCodeController::class
        )
    ]
)]
class AssetResource { }
```

## Consequences

### Positive
✅ **Rapid Development**: Auto-generated CRUD endpoints  
✅ **Documentation**: OpenAPI docs auto-generated  
✅ **Standards**: REST best practices enforced  
✅ **Extensibility**: Easy to add custom operations  
✅ **Testing**: Built-in test utilities  
✅ **Symfony Integration**: Works seamlessly with security, validation  

### Negative
❌ **Learning Curve**: Requires understanding API Platform concepts  
❌ **Magic**: Auto-generation can obscure what's happening  
❌ **Overhead**: Might be overkill for simple APIs  
❌ **Customization**: Complex customizations can be tricky  

### Mitigation
- Use custom controllers for complex operations
- Document all customizations
- Training on API Platform for team
- Use DTOs instead of direct entity exposure

## Integration with Clean Architecture

API Platform sits in the **UI Layer**:

```
UI Layer (API Platform Resources)
         ↓
Application Layer (Commands/Queries)
         ↓
Domain Layer (Business Logic)
```

We'll use:
- **Resources**: API Platform resources (UI layer)
- **Data Providers**: Query handlers (Application layer)
- **Data Persisters**: Command handlers (Application layer)

## Custom Endpoints

For operations not fitting CRUD pattern:

```php
// Custom controller for complex operations
class GetDashboardMetricsController
{
    public function __construct(
        private readonly GetDashboardMetricsHandler $handler
    ) {}
    
    public function __invoke(Request $request): JsonResponse
    {
        $query = new GetDashboardMetricsQuery(
            startDate: $request->query->get('start_date'),
            endDate: $request->query->get('end_date')
        );
        
        $metrics = $this->handler->handle($query);
        
        return new JsonResponse($metrics);
    }
}
```

## API Documentation

API Platform generates:
- **Swagger UI**: Interactive API documentation at `/api/docs`
- **OpenAPI JSON**: Machine-readable spec at `/api/docs.json`
- **JSON-LD Context**: Semantic context at `/api/contexts/*`

## Security

Integration with Symfony Security:

```php
#[ApiResource(
    operations: [
        new Get(security: "is_granted('VIEW', object)"),
        new Put(security: "is_granted('EDIT', object)"),
        new Delete(security: "is_granted('DELETE', object)")
    ]
)]
```

## Alternatives Considered

### 1. Manual REST Controllers
**Rejected** because:
- More boilerplate code
- Manual documentation needed
- Inconsistent API design
- More maintenance burden

### 2. GraphQL Only (API Platform GraphQL)
**Rejected** because:
- REST is more universally understood
- Simpler for most use cases
- Can add GraphQL later if needed

### 3. FOSRestBundle
**Rejected** because:
- Less modern approach
- More manual configuration
- API Platform is the successor

## Migration Strategy

If we need to migrate away from API Platform:
- Business logic is in Application/Domain layers
- Only UI layer affected
- Resources can be converted to controllers
- OpenAPI spec preserved for compatibility

## References

- [API Platform Documentation](https://api-platform.com/docs/)
- [Symfony Integration](https://symfony.com/bundles/ApiPlatformBundle/current/index.html)
- [OpenAPI Specification](https://spec.openapis.org/oas/v3.1.0)
