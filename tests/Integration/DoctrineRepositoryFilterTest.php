<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Tests\Integration;

use AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture\Article;
use AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture\ArticleRepository;
use AlexandreBulete\DddFoundation\Application\Criteria\CriteriaBuilder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The foundation's CriteriaBuilder is the producer of filters; this checks
 * that every comparison it emits runs as SQL — and means what the in-memory
 * repository says it means (same cases as its test in ddd-foundation).
 */
final class DoctrineRepositoryFilterTest extends TestCase
{
    private EntityManagerInterface $em;
    private ArticleRepository $repository;

    protected function setUp(): void
    {
        $this->em = TestEntityManager::create();
        $this->repository = new ArticleRepository($this->em);

        foreach (['alpha', 'beta', 'gamma', 'delta'] as $title) {
            $this->em->persist(new Article($title));
        }
        $this->em->flush();
        $this->em->clear();
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    /**
     * @return iterable<string, array{array<string, mixed>, list<string>}>
     */
    public static function filters(): iterable
    {
        $c = new CriteriaBuilder();

        yield 'plain value = equality' => [['title' => 'beta'], ['beta']];
        yield 'neq' => [$c->neq('title', 'beta'), ['alpha', 'delta', 'gamma']];
        yield 'in' => [$c->in('title', ['alpha', 'delta']), ['alpha', 'delta']];
        yield 'not in (builder spelling)' => [$c->notIn('title', ['alpha', 'delta']), ['beta', 'gamma']];
        yield 'like = contains' => [$c->like('title', 'et'), ['beta']];
        yield 'not like (builder spelling)' => [$c->notLike('title', 'mm'), ['alpha', 'beta', 'delta']];
        yield 'starts with' => [['title' => ['type' => 'starts_with', 'value' => 'ga']], ['gamma']];
        yield 'is null, no value needed' => [['title' => ['type' => 'is_null']], []];
        yield 'blank value = filter skipped' => [['title' => ''], ['alpha', 'beta', 'delta', 'gamma']];
    }

    /**
     * @param array<string, mixed> $filter
     * @param list<string>         $expected
     */
    #[Test]
    #[DataProvider('filters')]
    public function it_filters_with_the_foundation_vocabulary(array $filter, array $expected): void
    {
        $titles = [];
        foreach ($this->repository->filter($filter)->orderBy('title', 'asc') as $article) {
            $titles[] = $article->title;
        }

        self::assertSame($expected, $titles);
    }

    #[Test]
    public function a_subclass_can_constrain_beyond_the_vocabulary(): void
    {
        $titles = [];
        foreach ($this->repository->titledEither('alpha', 'delta')->filter(['title' => ['type' => 'neq', 'value' => 'delta']]) as $article) {
            $titles[] = $article->title;
        }

        self::assertSame(['alpha'], $titles, 'the constraint combines with filters');
        self::assertCount(4, $this->repository, 'the original is untouched');
    }

    #[Test]
    public function an_unknown_comparison_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repository->filter(['title' => ['type' => 'regex', 'value' => '^a']]);
    }
}
