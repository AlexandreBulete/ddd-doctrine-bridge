<?php 

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge;

use Doctrine\ORM\QueryBuilder;

final class ComparisonBuilder
{
    public function __construct(
        private readonly QueryBuilder $queryBuilder
    ) {
        //
    }

    public function build(string $type, string $field, $param, $value): QueryBuilder
    {
        match ($type) {
            'equals', 'equal', 'eq', 'is' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->eq($field, ":$param"))
                ->setParameter($param, $value),

            'not_equals', 'not_equal', 'neq' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->neq($field, ":$param"))
                ->setParameter($param, $value),

            'lt' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->lt($field, ":$param"))
                ->setParameter($param, $value),

            'lte' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->lte($field, ":$param"))
                ->setParameter($param, $value),

            'gt' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->gt($field, ":$param"))
                ->setParameter($param, $value),

            'gte' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->gte($field, ":$param"))
                ->setParameter($param, $value),

            'in' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->in($field, ":$param"))
                ->setParameter($param, $value),

            'nin', 'not_in' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->notIn($field, ":$param"))
                ->setParameter($param, $value),

            'contains', 'like' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->like($field, ":$param"))
                ->setParameter($param, "%{$value}%"),

            'not_contains', 'not_like' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->notLike($field, ":$param"))
                ->setParameter($param, "%{$value}%"),

            'member_of', 'member_in' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->isMemberOf($field, ":$param"))
                ->setParameter($param, $value),

            'starts_with', 'startswith' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->like($field, ":$param"))
                ->setParameter($param, "{$value}%"),

            'ends_with', 'endswith' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->like($field, ":$param"))
                ->setParameter($param, "%{$value}"),

            default => throw new \InvalidArgumentException(sprintf('Unsupported filter type "%s" for "%s"', $type, $field)),
        };

        return $this->queryBuilder;
    }
}