# Contributing to Zagros CMMS

Thank you for your interest in contributing to Zagros CMMS! This document provides guidelines for contributing to the project.

## Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Focus on what is best for the community

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/yourusername/zagros-cmms.git`
3. Create a feature branch: `git checkout -b feature/your-feature-name`
4. Make your changes
5. Test your changes thoroughly
6. Commit using conventional commits (see below)
7. Push to your fork
8. Open a Pull Request

## Development Setup

See [README.md](README.md#quick-start) for local development setup instructions.

## Coding Standards

### Backend (PHP/Symfony)

- Follow PSR-12 coding standards
- Use PHPStan level 8
- Write unit tests for business logic
- Document complex methods
- Use type hints everywhere

```php
// Good
public function createAsset(CreateAssetCommand $command): Asset
{
    // Implementation
}

// Bad
public function createAsset($command)
{
    // Implementation
}
```

### Frontend (TypeScript/React)

- Use TypeScript strictly (no `any` types)
- Follow React best practices
- Use functional components and hooks
- Write component tests
- Use ESLint and Prettier

```typescript
// Good
interface AssetProps {
  id: string;
  name: string;
}

export const Asset: React.FC<AssetProps> = ({ id, name }) => {
  return <div>{name}</div>;
};

// Bad
export const Asset = (props: any) => {
  return <div>{props.name}</div>;
};
```

## Commit Messages

We use [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```
feat(assets): add QR code generation for equipment

Implements QR code generation using endroid/qr-code library.
QR codes include asset ID and direct link to asset details.

Closes #123
```

```
fix(workorder): resolve timezone issue in scheduling

Converts all dates to UTC before storing in database.
Displays dates in user's local timezone in frontend.

Fixes #456
```

## Pull Request Process

1. **Update documentation** if you're changing functionality
2. **Add/update tests** for your changes
3. **Ensure all tests pass**: Run `composer test` and `npm test`
4. **Update CHANGELOG.md** with your changes
5. **Request review** from maintainers
6. **Address feedback** promptly

### PR Title Format

Use the same format as commit messages:

```
feat(module): brief description of changes
```

### PR Description Template

```markdown
## Description
Brief description of what this PR does

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Integration tests added/updated
- [ ] Manually tested

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No new warnings generated
- [ ] Tests pass locally

## Screenshots (if applicable)
Add screenshots here
```

## Testing Requirements

- **Backend**: Minimum 80% code coverage
- **Frontend**: Test critical user flows
- **E2E**: Test complete features

```bash
# Backend tests
docker-compose exec backend composer test

# Frontend tests
cd frontend && npm run test

# E2E tests
cd frontend && npm run test:e2e
```

## Architecture Guidelines

### Backend

- Follow Clean Architecture layers
- Use CQRS pattern (Commands for writes, Queries for reads)
- Keep Domain layer framework-agnostic
- Use Value Objects for domain concepts
- Emit Domain Events for important actions

### Frontend

- Use feature-based structure
- Keep components small and focused
- Use custom hooks for logic reuse
- Prefer composition over inheritance
- Use TypeScript strictly

## Documentation

- Update README.md for user-facing changes
- Add ADR (Architectural Decision Record) for significant architectural changes
- Document API changes in OpenAPI spec
- Add inline comments for complex logic

## Branch Naming

```
feature/asset-qr-code-generation
fix/workorder-timezone-bug
docs/update-api-documentation
refactor/clean-architecture-implementation
```

## Questions?

- Open a [Discussion](https://github.com/yourusername/zagros-cmms/discussions)
- Check existing [Issues](https://github.com/yourusername/zagros-cmms/issues)
- Review [Documentation](docs/)

Thank you for contributing! 🎉
