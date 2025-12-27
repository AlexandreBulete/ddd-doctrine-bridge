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

    public function __construct(
        protected EntityManagerInterface $em,
        string $entityClass,
        string $alias,
    ) {
        $this->queryBuilder = $this->em->createQueryBuilder()
            ->select($alias)
            ->from($entityClass, $alias);
    }

    public function getIterator(): \Iterator
    {
        if (null !== $paginator = $this->paginator()) {
            yield from $paginator;

            return;
        }

        yield from $this->queryBuilder->getQuery()->getResult();
    }

    public function count(): int
    {
        if (null !== $paginator = $this->paginator()) {
            return $paginator->getTotalItems();
        }

        return $this->countTotal();
    }

    public function paginator(): ?PaginatorInterface
    {
        if (null === $this->page || null === $this->itemsPerPage) {
            return null;
        }

        $qb = clone $this->queryBuilder;
        $qb->distinct();
        $qb->setFirstResult(($this->page - 1) * $this->itemsPerPage);
        $qb->setMaxResults($this->itemsPerPage);

        $items = $qb->getQuery()->getResult();
        $total = $this->countTotal();

        return new DoctrinePaginator($items, $total, $this->page, $this->itemsPerPage);
    }

    private function countTotal(): int
    {
        $alias = $this->getAlias();

        $countQb = clone $this->queryBuilder;
        $countQb->select("COUNT({$alias})");

        $countQb->resetDQLPart('orderBy');

        return (int) $countQb->getQuery()->getSingleScalarResult();
    }

    public function withoutPagination(): static
    {
        $cloned = clone $this;
        $cloned->page = null;
        $cloned->itemsPerPage = null;

        return $cloned;
    }

    public function withPagination(int $page, int $itemsPerPage): static
    {
        Assert::positiveInteger($page);
        Assert::positiveInteger($itemsPerPage);

        $cloned = clone $this;
        $cloned->page = $page;
        $cloned->itemsPerPage = $itemsPerPage;

        return $cloned;
    }

    public function filter(array $filter): static
    {
        $cloned = clone $this;

        foreach ($filter as $key => $criterion) {
            $type  = is_array($criterion) ? ($criterion['type'] ?? 'equals') : 'equals';
            $value = is_array($criterion) ? ($criterion['value'] ?? null) : $criterion;
    
            if ($value === null || $value === '') {
                continue;
            }
    
            $field = sprintf('%s.%s', $cloned->getAlias(), $key);
            $param = $key;
    
            $cloned->queryBuilder = (new ComparisonBuilder($cloned->queryBuilder))
                ->build($type, $field, $param, $value);
        }
    
        return $cloned;
    }    

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

