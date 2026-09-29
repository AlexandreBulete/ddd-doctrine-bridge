<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture;

use AlexandreBulete\DddDoctrineBridge\Capability\AsCrudable;
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Uses AsCrudable on purpose: it is how the capability traits get exercised —
 * and analysed, PHPStan skips a trait no class uses.
 *
 * @extends DoctrineRepository<Article>
 */
final class ArticleRepository extends DoctrineRepository
{
    use AsCrudable;

    public function __construct(
        EntityManagerInterface $em,
        private readonly RecordingDispatcher $eventDispatcher = new RecordingDispatcher(),
    ) {
        parent::__construct($em, Article::class, 'article');
    }

    public function findById(IdentifierVO $id): ?Article
    {
        return $this->em->find(Article::class, $id->value());
    }

    public function save(Article $article): void
    {
        $this->persistAndFlush($article, dispatchEvents: true);
    }

    public function delete(Article $article): void
    {
        $this->removeAndFlush($article);
    }

    public function byId(int $id): ?Article
    {
        return $this->findEntityById(Article::class, (string) $id);
    }

    /**
     * @return list<object>
     */
    public function everything(): array
    {
        return $this->findAllEntities();
    }

    /**
     * An OR the filter vocabulary cannot express.
     */
    public function titledEither(string $one, string $other): self
    {
        return $this->constrained(static function (QueryBuilder $qb, string $alias) use ($one, $other): void {
            $qb->andWhere($qb->expr()->orX("{$alias}.title = :one", "{$alias}.title = :other"))
                ->setParameter('one', $one)
                ->setParameter('other', $other);
        });
    }

    public function dispatcher(): RecordingDispatcher
    {
        return $this->eventDispatcher;
    }
}
