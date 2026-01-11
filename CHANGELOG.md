# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-01-11

### Added

- **Operation Traits** - Granular traits for atomic repository operations:
  - `CanPersist` - `persistAndFlush()` method with optional domain events dispatch
  - `CanRemove` - `removeAndFlush()` method with optional domain events dispatch
  - `CanFindById` - `findEntityById()` method
  - `CanFindAll` - `findAllEntities()` method

- **Capability Traits** - High-level traits combining operations:
  - `AsReadable` - Combines `CanFindById` + `CanFindAll`
  - `AsMutable` - Combines `CanPersist` + `CanRemove` + `DispatchesDomainEvents`
  - `AsCrudable` - Combines `AsReadable` + `AsMutable`

- **Domain Events Support**:
  - `DispatchesDomainEvents` trait - Dispatches events from entities using `RecordsEvents`
  - `WhenDispatchDomainEventsEnum` - Control when events are dispatched (`BEFORE` / `AFTER`)

### Changed

- Requires `alexandrebulete/ddd-foundation` ^1.1 for `EventDispatcherInterface` and `RecordsEvents`

## [1.0.0] - 2025-01-01

### Added

- `DoctrineRepository` - Abstract repository with pagination, filtering, and ordering
- `DoctrinePaginator` - Paginator implementation for Doctrine queries
- `ComparisonBuilder` - Query builder helper for filter comparisons

- **Custom Doctrine Types**:
  - `GuidType` - For UUID/ULID identifier value objects
  - `VarcharType` - For string value objects (VARCHAR)
  - `TextType` - For string value objects (TEXT/CLOB)
  - `AsIdentifierConvertor` - Trait for identifier type conversion
  - `AsStringConvertor` - Trait for string type conversion

