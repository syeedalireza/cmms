# API Design Documentation

## Base URL

```
Development: http://localhost/api
Production: https://your-domain.com/api
```

## Authentication

All API endpoints (except `/auth/login` and `/auth/register`) require JWT authentication.

### Headers

```http
Authorization: Bearer <jwt_token>
Content-Type: application/json
```

### Token Expiration

- Access Token: 1 hour
- Refresh Token: 7 days (planned)

---

## API Endpoints

### Authentication

#### POST /api/auth/login

Authenticate user and receive JWT token.

**Request:**
```json
{
  "email": "admin@zagros.test",
  "password": "admin123"
}
```

**Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": "uuid",
    "email": "admin@zagros.test",
    "firstName": "Admin",
    "lastName": "User",
    "fullName": "Admin User",
    "roles": ["ROLE_ADMIN", "ROLE_USER"]
  }
}
```

#### POST /api/auth/register

Register new user (requires ROLE_ADMIN).

**Request:**
```json
{
  "email": "new.user@example.com",
  "password": "securepassword",
  "firstName": "John",
  "lastName": "Doe",
  "phone": "+98-912-1234567"
}
```

**Response:**
```json
{
  "message": "User registered successfully",
  "userId": "uuid"
}
```

#### GET /api/auth/me

Get current authenticated user info.

**Response:**
```json
{
  "id": "uuid",
  "email": "admin@zagros.test",
  "firstName": "Admin",
  "lastName": "User",
  "fullName": "Admin User",
  "roles": ["ROLE_ADMIN", "ROLE_USER"]
}
```

---

### Assets

#### GET /api/assets

List all assets with pagination.

**Query Parameters:**
- `page` (default: 1)
- `itemsPerPage` (default: 30, max: 100)

**Response:**
```json
{
  "@context": "/api/contexts/Asset",
  "@id": "/api/assets",
  "@type": "hydra:Collection",
  "hydra:member": [
    {
      "@id": "/api/assets/uuid",
      "@type": "Asset",
      "id": "uuid",
      "code": "AST-HVAC-001",
      "name": "Central HVAC Unit",
      "status": "operational",
      "criticalityLevel": 3,
      "createdAt": "2026-01-28T10:00:00+00:00",
      "updatedAt": "2026-01-28T10:00:00+00:00"
    }
  ],
  "hydra:totalItems": 50,
  "hydra:view": {
    "@id": "/api/assets?page=1",
    "@type": "hydra:PartialCollectionView",
    "hydra:first": "/api/assets?page=1",
    "hydra:last": "/api/assets?page=2",
    "hydra:next": "/api/assets?page=2"
  }
}
```

#### POST /api/assets

Create new asset (requires ROLE_MANAGER).

**Request:**
```json
{
  "code": "AST-NEW-001",
  "name": "New Equipment",
  "categoryId": "uuid",
  "locationId": "uuid",
  "serialNumber": "SN123456",
  "manufacturer": "Siemens",
  "model": "Model-X",
  "purchaseDate": "2026-01-15",
  "purchaseCost": 50000.00
}
```

**Response:**
```json
{
  "@id": "/api/assets/uuid",
  "id": "uuid",
  "code": "AST-NEW-001",
  "name": "New Equipment",
  "status": "operational",
  "criticalityLevel": 3
}
```

#### GET /api/assets/{id}

Get single asset by ID.

**Response:**
```json
{
  "@id": "/api/assets/uuid",
  "@type": "Asset",
  "id": "uuid",
  "code": "AST-HVAC-001",
  "name": "Central HVAC Unit",
  "status": "operational",
  "criticalityLevel": 5,
  "createdAt": "2026-01-28T10:00:00+00:00",
  "updatedAt": "2026-01-28T10:00:00+00:00"
}
```

#### PUT /api/assets/{id}

Update asset (requires ROLE_MANAGER).

**Request:**
```json
{
  "name": "Updated Name",
  "status": "maintenance"
}
```

#### DELETE /api/assets/{id}

Delete asset (requires ROLE_ADMIN).

---

### Work Orders (Planned)

#### GET /api/work-orders
#### POST /api/work-orders
#### GET /api/work-orders/{id}
#### PATCH /api/work-orders/{id}/assign
#### PATCH /api/work-orders/{id}/complete

---

### Maintenance Schedules (Planned)

#### GET /api/maintenance/schedules
#### POST /api/maintenance/schedules
#### POST /api/maintenance/schedules/{id}/generate-work-order

---

### Inventory (Planned)

#### GET /api/inventory/parts
#### POST /api/inventory/parts/{id}/transaction

---

### Dashboard (Planned)

#### GET /api/dashboard/metrics

**Response:**
```json
{
  "totalAssets": 150,
  "activeWorkOrders": 25,
  "overdueTasks": 5,
  "inventoryAlerts": 3,
  "mtbf": 720.5,
  "mttr": 4.2,
  "assetUtilization": 87.5
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Missing required fields"
}
```

### 401 Unauthorized
```json
{
  "message": "Invalid credentials"
}
```

### 403 Forbidden
```json
{
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Access Denied"
}
```

### 404 Not Found
```json
{
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Asset not found"
}
```

### 422 Unprocessable Entity
```json
{
  "@type": "ConstraintViolationList",
  "hydra:title": "An error occurred",
  "violations": [
    {
      "propertyPath": "email",
      "message": "This value is already used."
    }
  ]
}
```

### 500 Internal Server Error
```json
{
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Internal server error"
}
```

---

## Rate Limiting

- API Endpoints: 10 requests/second
- Login Endpoint: 5 requests/minute

Exceeding rate limits returns `429 Too Many Requests`.

---

## Pagination

All collection endpoints support pagination:

**Query Parameters:**
- `page`: Page number (default: 1)
- `itemsPerPage`: Items per page (default: 30, max: 100)

**Response Headers:**
- `X-Total-Count`: Total number of items
- `Link`: Links to first, last, next, previous pages

---

## Filtering (Planned)

Collection endpoints will support filtering:

```
GET /api/assets?status=operational
GET /api/assets?criticalityLevel[gte]=4
GET /api/work-orders?priority=5&status=pending
```

---

## Sorting (Planned)

```
GET /api/assets?order[createdAt]=desc
GET /api/work-orders?order[priority]=desc&order[dueDate]=asc
```

---

## API Versioning

Currently: v1 (implicit)

Future versions will use:
```
Accept: application/ld+json; version=2
```

---

## Interactive API Documentation

Swagger UI available at:
- http://localhost/api/docs
- https://your-domain.com/api/docs

OpenAPI specification:
- http://localhost/api/docs.json

---

## Client Libraries

Recommended:
- JavaScript/TypeScript: `axios` or `fetch`
- PHP: `symfony/http-client` or `guzzlehttp/guzzle`
- Python: `requests` or `httpx`

Example (JavaScript):
```javascript
const response = await fetch('http://localhost/api/assets', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
const data = await response.json();
```

---

## Webhooks (Planned)

Future feature for real-time notifications:
- Asset status changes
- Work order completions
- Inventory stock alerts

---

## Best Practices

1. **Always validate input** on client side before sending
2. **Handle errors gracefully** with user-friendly messages
3. **Cache responses** when appropriate (use ETags)
4. **Use HTTPS** in production
5. **Implement retry logic** for failed requests
6. **Log API errors** for debugging

---

For implementation examples, see [frontend/src/features/*/api/](../../frontend/src/features/)
