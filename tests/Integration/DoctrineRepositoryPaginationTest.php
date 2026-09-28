<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Tests\Integration;

use AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture\Article;
use AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DoctrineRepositoryPaginationTest extends TestCase
{
    private EntityManagerInterface $em;
    private ArticleRepository $repository;

    protected function setUp(): void
    {
        $this->em = TestEntityManager::create();
        $this->repository = new ArticleRepository($this->em);

        foreach (['a', 'b', 'c', 'd', 'e'] as $title) {
            $this->em->persist(new Article($title, ['tag-' . $title]));
        }
        $this->em->flush();
        $this->em->clear();
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    #[Test]
    public function a_page_holds_its_slice_and_knows_the_total(): void
    {
        $paginator = $this->repository
            ->orderBy('title', 'asc')
            ->withPagination(2, 2)
            ->paginator();

        self::assertNotNull($paginator);
        self::assertSame(['c', 'd'], $this->titles($paginator));
        self::assertSame(5, $paginator->getTotalItems());
        self::assertSame(2, $paginator->getCurrentPage());
        self::assertSame(3, $paginator->getLastPage());
    }

    /**
     * Regression: the paginated query used to be a SELECT DISTINCT over every
     * column, which PostgreSQL rejects as soon as one of them is `json`
     * ("could not identify an equality operator for type json").
     */
    #[Test]
    public function it_paginates_an_entity_holding_a_json_column(): void
    {
        $page = $this->repository->withPagination(1, 10);

        self::assertCount(5, $page);
        self::assertSame(['tag-a'], $this->articles($page)[0]->tags);
    }

    #[Test]
    public function the_last_page_holds_the_remainder(): void
    {
        $paginator = $this->repository
            ->orderBy('title', 'asc')
            ->withPagination(3, 2)
            ->paginator();

        self::assertNotNull($paginator);
        self::assertSame(['e'], $this->titles($paginator));
    }

    #[Test]
    public function without_pagination_it_counts_and_yields_everything(): void
    {
        self::assertNull($this->repository->paginator());
        self::assertCount(5, $this->repository);
        self::assertCount(5, $this->articles($this->repository));
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return list<string>
     */
    private function titles(iterable $items): array
    {
        return array_map(static fn (Article $a): string => $a->title, $this->articles($items));
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return list<Article>
     */
    private function articles(iterable $items): array
    {
        $articles = [];
        foreach ($items as $item) {
            self::assertInstanceOf(Article::class, $item);
            $articles[] = $item;
        }

        return $articles;
    }
}
