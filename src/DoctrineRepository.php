<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge;

use AlexandreBulete\DddFoundation\Domain\Repository\PaginatorInterface;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

/**
 * @template T of object
 *
 * @implements RepositoryInterface<T>
 */
abstract class DoctrineRepository implements RepositoryInterface
{
    private ?int $page = null;
    private ?int $itemsPerPage = null;

    private QueryBuilder $queryBuilder;

    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(
        protected EntityManagerInterface $em,
        string $entityClass,
        string $alias,
    ) {
        $this->queryBuilder = $this->em->createQueryBuilder()
            ->select($alias)
            ->from($entityClass, $alias);
    }

    /**
     * @return \Iterator<array-key, T>
     */
    public function getIterator(): \Iterator
    {
        if (null !== $paginator = $this->paginator()) {
            yield from $paginator;

            return;
        }

        yield from $this->results($this->queryBuilder);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return $this->countTotal();
    }

    /**
     * @return PaginatorInterface<T>|null
     */
    public function paginator(): ?PaginatorInterface
    {
        if (null === $this->page || null === $this->itemsPerPage) {
            return null;
        }
        // No DISTINCT: the query selects one root entity and never joins, so a
        // row cannot come back twice. DISTINCT compared every column instead —
        // which PostgreSQL rejects outright for a `json` column. Should joins
        // ever be allowed, a to-many join needs Doctrine's Paginator (ids
        // first, then entities), not a DISTINCT over the whole row.
        $qb = clone $this->queryBuilder;
        $qb->setFirstResult(($this->page - 1) * $this->itemsPerPage);
        $qb->setMaxResults($this->itemsPerPage);

        return new DoctrinePaginator($this->results($qb), $this->countTotal(), $this->page, $this->itemsPerPage);
    }

    /**
     * The query selects the root entity only, so every row is a T — which
     * Doctrine's untyped getResult() cannot tell PHPStan.
     *
     * @return list<T>
     */
    private function results(QueryBuilder $qb): array
    {
        /** @var list<T> */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return int<0, max>
     */
    private function countTotal(): int
    {
        $alias = $this->getAlias();

        $countQb = clone $this->queryBuilder;
        $countQb->select("COUNT({$alias})");

        $countQb->resetDQLPart('orderBy');

        $total = $countQb->getQuery()->getSingleScalarResult();
        if (!is_numeric($total) || (int) $total < 0) {
            throw new \UnexpectedValueException(sprintf('COUNT() returned %s.', get_debug_type($total)));
        }

        return (int) $total;
    }

    /**
     * @return static
     */
    public function withoutPagination(): static
    {
        $cloned = clone $this;
        $cloned->page = null;
        $cloned->itemsPerPage = null;

        return $cloned;
    }

    /**
     * @return static
     */
    public function withPagination(int $page, int $itemsPerPage): static
    {
        Assert::positiveInteger($page);
        Assert::positiveInteger($itemsPerPage);

        $cloned = clone $this;
        $cloned->page = $page;
        $cloned->itemsPerPage = $itemsPerPage;

        return $cloned;
    }

    /**
     * @param array<string, mixed> $filter field => value, or field => {type, value}
     *
     * @return static
     */
    public function filter(array $filter): static
    {
        $cloned = clone $this;

        foreach ($filter as $key => $criterion) {
            $type  = is_array($criterion) ? ($criterion['type'] ?? 'equals') : 'equals';
            $value = is_array($criterion) ? ($criterion['value'] ?? null) : $criterion;

            if (!is_string($type)) {
                throw new \InvalidArgumentException(sprintf('Filter type for "%s" must be a string, %s given.', $key, get_debug_type($type)));
            }

            // An empty value means "filter left blank" and is skipped — except
            // for null-ness assertions, whose whole point is to carry no operand.
            if (!ComparisonBuilder::isValueless($type) && ($value === null || $value === '')) {
                continue;
            }

            $field = sprintf('%s.%s', $cloned->getAlias(), $key);
            $param = $key;

            $cloned->queryBuilder = (new ComparisonBuilder($cloned->queryBuilder))
                ->build($type, $field, $param, $value);
        }

        return $cloned;
    }

    /**
     * @return static
     */
    public function orderBy(string $field, string $direction): static
    {
        Assert::notEmpty($field);
        Assert::notEmpty($direction);
        Assert::oneOf($direction, ['asc', 'desc']);

        $cloned = clone $this;

        if (!str_contains($field, '.')) {
            $alias = $cloned->getAlias();
            $field = "{$alias}.{$field}";
        }

        $cloned->queryBuilder->addOrderBy($field, $direction);

        return $cloned;
    }

    private function getAlias(): string
    {
        return $this->queryBuilder->getRootAliases()[0];
    }

    protected function query(): QueryBuilder
    {
        return clone $this->queryBuilder;
    }

    protected function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }
}

