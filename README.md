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
└── Type/
    ├── BaseType.php
    ├── GuidType.php
    ├── TextType.php
    ├── VarcharType.php
    └── Convertor/
        ├── AsIdentifierConvertor.php
        └── AsStringConvertor.php
```

## Usage

### DoctrineRepository

```php
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use App\Post\Domain\Entity\Post;

class DoctrinePostRepository extends DoctrineRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Post::class, 'p');
    }

    public function findById(IdentifierVO $id): ?Post
    {
        return $this->query()
            ->andWhere('p.id = :id')
            ->setParameter('id', $id->toString())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
```

### Custom Doctrine Types

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

