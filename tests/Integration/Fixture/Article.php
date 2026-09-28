<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture;

use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The `tags` JSON column is the point: PostgreSQL has no equality operator for
 * `json`, so any query that compares whole rows (SELECT DISTINCT) fails on it.
 */
#[ORM\Entity]
#[ORM\Table(name: 'bridge_test_article')]
class Article
{
    use RecordsEvents;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    /**
     * @param list<string> $tags
     */
    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 50)]
        public string $title,
        #[ORM\Column(type: Types::JSON)]
        public array $tags = [],
    ) {}

    public function publish(): void
    {
        $this->recordEvent(new ArticlePublished($this->title));
    }
}
