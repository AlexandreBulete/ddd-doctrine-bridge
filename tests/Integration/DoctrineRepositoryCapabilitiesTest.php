<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Tests\Integration;

use AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture\Article;
use AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture\ArticlePublished;
use AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DoctrineRepositoryCapabilitiesTest extends TestCase
{
    private EntityManagerInterface $em;
    private ArticleRepository $repository;

    protected function setUp(): void
    {
        $this->em = TestEntityManager::create();
        $this->repository = new ArticleRepository($this->em);
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    #[Test]
    public function saving_persists_and_dispatches_what_the_entity_recorded(): void
    {
        $article = new Article('a');
        $article->publish();

        $this->repository->save($article);

        self::assertNotNull($article->id);
        self::assertEquals([new ArticlePublished('a')], $this->repository->dispatcher()->dispatched);
        self::assertSame([], $article->releaseEvents(), 'events are released once');
    }

    #[Test]
    public function it_finds_by_id_and_lists_everything(): void
    {
        $this->repository->save(new Article('a'));
        $this->repository->save($b = new Article('b'));
        $this->em->clear();

        self::assertNotNull($b->id);
        self::assertSame('b', $this->repository->byId($b->id)?->title);
        self::assertCount(2, $this->repository->everything());
    }

    #[Test]
    public function deleting_removes_the_row(): void
    {
        $this->repository->save($article = new Article('a'));

        $this->repository->delete($article);

        self::assertCount(0, $this->repository);
    }
}
