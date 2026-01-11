# DDD Doctrine Bridge

Doctrine ORM bridge for DDD Foundation. This package provides repository and paginator implementations using Doctrine, plus custom DBAL types for Value Objects.

**Pure PHP** - No framework dependency. Use with any PHP framework.

## Installation

```bash
composer require alexandrebulete/ddd-doctrine-bridge
```

For Symfony integration, also install:
```bash
composer require alexandrebulete/ddd-doctrine-bundle
```

## Structure

```
src/
├── DoctrineRepository.php
├── DoctrinePaginator.php
├── DispatchesDomainEvents.php
├── Capability/
│   ├── AsReadable.php          # CanFindById + CanFindAll
│   ├── AsMutable.php           # CanPersist + CanRemove + DispatchesDomainEvents
│   └── AsCrudable.php          # AsReadable + AsMutable
├── Operation/
│   ├── CanPersist.php          # persistAndFlush()
│   ├── CanRemove.php           # removeAndFlush()
│   ├── CanFindById.php         # findEntityById()
│   └── CanFindAll.php          # findAllEntities()
├── Enum/
│   └── WhenDispatchDomainEventsEnum.php
└── Type/
    ├── BaseType.php
    ├── GuidType.php
    ├── TextType.php
    ├── VarcharType.php
    └── Convertor/
        ├── AsIdentifierConvertor.php
        └── AsStringConvertor.php
```

## Repository Traits

Granular traits to compose your repositories with only the capabilities you need.

### Capability Traits (High-level)

| Trait | Includes | Use case |
|-------|----------|----------|
| `AsReadable` | CanFindById, CanFindAll | Read-only repositories |
| `AsMutable` | CanPersist, CanRemove, DispatchesDomainEvents | Write-only repositories |
| `AsCrudable` | AsReadable, AsMutable | Full CRUD repositories |

### Operation Traits (Atomic)

| Trait | Method | Description |
|-------|--------|-------------|
| `CanPersist` | `persistAndFlush()` | Persist and flush entity |
| `CanRemove` | `removeAndFlush()` | Remove and flush entity |
| `CanFindById` | `findEntityById()` | Find entity by ID |
| `CanFindAll` | `findAllEntities()` | Find all entities |

### Usage Examples

#### Full CRUD Repository

```php
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddDoctrineBridge\Capability\AsCrudable;
use AlexandreBulete\DddFoundation\Application\Event\EventDispatcherInterface;

class DoctrinePostRepository extends DoctrineRepository implements PostRepositoryInterface
{
    use AsCrudable;

    public function __construct(
        EntityManagerInterface $em,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($em, Post::class, 'post');
    }

    public function save(Post $post): void
    {
        $this->persistAndFlush(
            entity: $post,
            dispatchEvents: true,
            whenDispatchEvents: WhenDispatchDomainEventsEnum::AFTER,
        );
    }

    public function delete(Post $post): void
    {
        $this->removeAndFlush($post);
    }

    public function findById(IdentifierVO $id): ?Post
    {
        return $this->findEntityById(Post::class, $id->toRfc4122());
    }

    public function findAll(): array
    {
        return $this->findAllEntities();
    }
}
```

#### Read-Only Repository

```php
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddDoctrineBridge\Capability\AsReadable;

class DoctrineCountryRepository extends DoctrineRepository
{
    use AsReadable;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Country::class, 'country');
    }

    public function findById(IdentifierVO $id): ?Country
    {
        return $this->findEntityById(Country::class, $id->value());
    }

    public function findAll(): array
    {
        return $this->findAllEntities();
    }
}
```

#### Custom Mix (Persist without Delete)

```php
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddDoctrineBridge\Capability\AsReadable;
use AlexandreBulete\DddDoctrineBridge\Operation\CanPersist;
use AlexandreBulete\DddDoctrineBridge\DispatchesDomainEvents;

class DoctrineAuditLogRepository extends DoctrineRepository
{
    use AsReadable;
    use CanPersist;
    use DispatchesDomainEvents;

    // Can read and create, but NOT delete
}
```

## Domain Events

Dispatch domain events from your aggregates after persistence.

### When to Dispatch

```php
use AlexandreBulete\DddDoctrineBridge\Enum\WhenDispatchDomainEventsEnum;

// Dispatch AFTER flush (recommended - ensures transaction committed)
$this->persistAndFlush($entity, dispatchEvents: true, whenDispatchEvents: WhenDispatchDomainEventsEnum::AFTER);

// Dispatch BEFORE flush (rare - event dispatched even if flush fails)
$this->persistAndFlush($entity, dispatchEvents: true, whenDispatchEvents: WhenDispatchDomainEventsEnum::BEFORE);
```

### Requirements

Your entity must use the `RecordsEvents` trait from `ddd-foundation`:

```php
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

class Post
{
    use RecordsEvents;

    public function publish(): void
    {
        $this->status = 'published';
        $this->recordEvent(new PostPublished($this->id));
    }
}
```

## Custom Doctrine Types

Create custom Doctrine types for your Value Objects:

```php
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;
use App\Post\Domain\ValueObject\PostId;

class PostIdType extends GuidType
{
    protected string $name = 'post_id';
    protected string $voClass = PostId::class;
}
```

### Available Base Types

- **GuidType**: For UUID/ULID identifier value objects
- **VarcharType**: For string value objects (VARCHAR)
- **TextType**: For string value objects (TEXT/CLOB)

