## [Unreleased]

### 🚀 Features

- Add `is_null` / `is_not_null` comparison types, so a criteria array can assert
  on null-ness — previously impossible, as `filter()` discarded any criterion
  without a value. Derived states spanning a nullable column and a counter
  (e.g. an outbox row's pending/parked/published) are now expressible in SQL
  instead of being filtered in PHP after pagination.
- `ComparisonBuilder::isValueless()` exposes which types carry no operand.

## [1.1.3] - 2026-02-05

### 💼 Other

- Symfony 8 compatibility

### 📚 Documentation

- Update CHANGELOG.md for 1.1.2
## [1.1.2] - 2026-01-11

### 🐛 Bug Fixes

- Remove property declaration from trait to avoid conflict

### 🚜 Refactor

- Rm unused class

### 📚 Documentation

- Update CHANGELOG.md for 1.1.1
## [1.1.1] - 2026-01-11

### 🐛 Bug Fixes

- Twice use eventDispatcher

### 📚 Documentation

- Init changelog
- Add granular traits et event dispatcher
## [1.1.0] - 2026-01-11

### 🚀 Features

- Add granular repository traits
## [1.0.0] - 2025-12-27

### 🚀 Features

- Support all doctrine standard comparisons

### 🐛 Bug Fixes

- Namespace
- Prevent guid convertion
- Convert if is AbstractUid
- Rm dd garbage
