<?php 

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge;

use Doctrine\ORM\QueryBuilder;

final class ComparisonBuilder
{
    /**
     * Comparison types that assert on the column itself and take no operand.
     * Callers must consult this before discarding a criterion for having an
     * empty value — see {@see DoctrineRepository::filter()}.
     */
    private const VALUELESS_TYPES = [
        'is_null', 'isnull', 'null',
        'is_not_null', 'isnotnull', 'not_null',
    ];

    public function __construct(
        private readonly QueryBuilder $queryBuilder
    ) {
        //
    }

    /**
     * Whether `$type` is an assertion on null-ness, i.e. one that must still be
     * applied when no value accompanies it.
     */
    public static function isValueless(string $type): bool
    {
        return in_array($type, self::VALUELESS_TYPES, true);
    }

    public function build(string $type, string $field, $param, $value): QueryBuilder
    {
        match ($type) {
            // No parameter bound on purpose: SQL compares against NULL with a
            // dedicated operator, never with `= :param` (which is never true).
            'is_null', 'isnull', 'null' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->isNull($field)),

            'is_not_null', 'isnotnull', 'not_null' => $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->isNotNull($field)),

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