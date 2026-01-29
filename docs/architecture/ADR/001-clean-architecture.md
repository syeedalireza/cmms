# ADR 001: Clean Architecture Implementation

**Date**: 2026-01-28  
**Status**: Accepted  
**Decision Makers**: Architecture Team

## Context

We need to choose an architectural pattern for the Zagros CMMS application that ensures:
- Long-term maintainability
- Testability
- Framework independence
- Ability to swap infrastructure components
- Clear separation of concerns

## Decision

We will implement **Clean Architecture** (also known as Hexagonal Architecture or Ports & Adapters pattern) with the following layers:

### 1. Domain Layer (Core)
- Contains pure business logic
- Framework-agnostic
- No external dependencies
- Entities, Value Objects, Repository Interfaces, Domain Events

### 2. Application Layer
- Use Cases and business workflows
- CQRS pattern (Commands and Queries)
- Data Transfer Objects (DTOs)
- Orchestrates domain objects

### 3. Infrastructure Layer
- Framework-specific implementations
- Database access (Doctrine)
- External services (Email, Cache, Queue)
- Implements interfaces from Domain layer

### 4. UI Layer
- API Controllers
- API Platform Resources
- Input/Output adapters

## Consequences

### Positive
✅ **Testability**: Domain logic can be tested without database or framework  
✅ **Maintainability**: Clear boundaries between layers  
✅ **Flexibility**: Easy to swap databases or frameworks  
✅ **Independence**: Business rules don't depend on UI or database  
✅ **Scalability**: Each layer can be scaled independently  

### Negative
❌ **Complexity**: More files and abstractions  
❌ **Learning Curve**: Team needs to understand the pattern  
❌ **Boilerplate**: More code than traditional layered architecture  

### Mitigation
- Provide thorough documentation
- Code examples and templates
- Training sessions for the team
- Use generators for boilerplate code

## Alternatives Considered

### 1. Traditional Layered Architecture (MVC)
**Rejected** because:
- Tight coupling between layers
- Difficult to test business logic
- Framework-dependent

### 2. Domain-Driven Design (DDD) without Clean Architecture
**Rejected** because:
- DDD is more about modeling, not structure
- We want explicit architectural boundaries

## Implementation Notes

- Use Symfony Messenger for CQRS Commands/Queries
- Repository interfaces in Domain, implementations in Infrastructure
- API Platform for auto-generated REST endpoints
- Domain Events handled by Symfony Event Dispatcher

## References

- [Clean Architecture by Robert C. Martin](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Hexagonal Architecture](https://alistair.cockburn.us/hexagonal-architecture/)
- [Symfony CQRS](https://symfony.com/doc/current/messenger.html)
