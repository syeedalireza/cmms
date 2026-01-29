# ADR 002: PostgreSQL as Primary Database

**Date**: 2026-01-28  
**Status**: Accepted  
**Decision Makers**: Architecture Team

## Context

We need to choose a relational database for Zagros CMMS that can handle:
- Complex asset hierarchies
- Advanced search functionality
- JSON metadata storage
- Analytical queries (MTBF, MTTR calculations)
- Scalability for enterprise deployments

## Decision

We will use **PostgreSQL 16** as the primary database management system.

## Rationale

### Technical Advantages

**1. Advanced Data Types**
- **JSONB**: Efficient storage and querying of asset metadata
- **Arrays**: Store multiple values without separate tables
- **UUID**: Built-in UUID generation for distributed systems
- **Full-text Search**: Native search without external tools

**2. Performance for Complex Queries**
- **Window Functions**: Essential for MTBF/MTTR calculations
- **CTEs (Common Table Expressions)**: Hierarchical asset queries
- **Better Query Optimizer**: Handles complex joins efficiently
- **Partial Indexes**: Index only specific rows (e.g., active assets)

**3. Data Integrity**
- **Transactional DDL**: Schema changes are transactional
- **Better ACID Compliance**: Stricter consistency guarantees
- **CHECK Constraints**: More expressive than MySQL
- **Exclusion Constraints**: Prevent overlapping ranges

**4. Advanced Features**
- **Materialized Views**: Pre-compute expensive reports
- **Table Partitioning**: Scale large tables (work_orders, logs)
- **Pub/Sub**: Real-time notifications via LISTEN/NOTIFY
- **Better Unicode Support**: Full UTF-8 by default

## Comparison with MySQL

| Feature | PostgreSQL | MySQL |
|---------|-----------|-------|
| JSON Support | JSONB (indexed, fast) | JSON (slower) |
| Window Functions | Full support | Limited |
| Full-text Search | Built-in | Limited |
| Hierarchical Queries | WITH RECURSIVE | Limited |
| Data Integrity | Strict | Permissive |
| License | PostgreSQL License | GPL/Commercial |

## Consequences

### Positive
✅ **Better for CMMS domain**: Complex queries, analytics, hierarchies  
✅ **JSONB for metadata**: Flexible schema for different asset types  
✅ **Advanced indexing**: GiST, GIN, BRIN for different use cases  
✅ **Better for reporting**: Window functions, CTEs, materialized views  
✅ **Open source**: No licensing concerns for commercial use  

### Negative
❌ **Less familiar**: Some developers may know MySQL better  
❌ **Slightly more complex**: More features = more to learn  
❌ **Fewer managed hosting options**: Though AWS RDS, Azure support it  

### Mitigation
- Provide PostgreSQL training materials
- Use Doctrine ORM to abstract database specifics
- Document PostgreSQL-specific features used

## Example Use Cases

### 1. Asset Metadata (JSONB)
```sql
SELECT * FROM assets 
WHERE metadata @> '{"manufacturer": "Siemens"}';
```

### 2. Asset Hierarchy (Recursive CTE)
```sql
WITH RECURSIVE asset_tree AS (
  SELECT id, parent_id, name, 1 as level
  FROM assets WHERE id = 'root-id'
  UNION ALL
  SELECT a.id, a.parent_id, a.name, level + 1
  FROM assets a
  INNER JOIN asset_tree t ON a.parent_id = t.id
)
SELECT * FROM asset_tree;
```

### 3. MTBF Calculation (Window Functions)
```sql
SELECT asset_id,
  AVG(time_between_failures) OVER (
    PARTITION BY asset_id 
    ORDER BY failure_date
    ROWS BETWEEN 10 PRECEDING AND CURRENT ROW
  ) as rolling_mtbf
FROM failures;
```

## Alternatives Considered

### 1. MySQL/MariaDB
**Rejected** because:
- Inferior JSON support
- Limited window functions
- Less suitable for complex analytics

### 2. MongoDB (NoSQL)
**Rejected** because:
- CMMS requires strong relationships
- ACID transactions essential for work orders
- Complex joins needed

### 3. SQL Server
**Rejected** because:
- Licensing costs
- Less cross-platform friendly
- Overkill for our needs

## Migration Path

If we ever need to switch databases:
- Use Doctrine ORM for database abstraction
- Avoid PostgreSQL-specific features in application code
- Keep complex queries in repository layer
- Document all PostgreSQL-specific SQL

## References

- [PostgreSQL Documentation](https://www.postgresql.org/docs/16/)
- [PostgreSQL vs MySQL](https://www.postgresql.org/about/)
- [Why Postgres for JSONB](https://www.postgresql.org/docs/current/datatype-json.html)
