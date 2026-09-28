<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge;

use AlexandreBulete\DddFoundation\Domain\Repository\Comparison;
use Doctrine\ORM\QueryBuilder;

/**
 * Translates one criterion of the {@see Comparison} vocabulary into DQL.
 *
 * The vocabulary — canonical types and their aliases — lives in the
 * foundation, shared with the in-memory repository: a filter means the same
 * thing in production and in a handler test.
 */
final class ComparisonBuilder
{
    public function __construct(
        private readonly QueryBuilder $queryBuilder
    ) {
    }

    /**
     * Whether `$type` is an assertion on null-ness, i.e. one that must still be
     * applied when no value accompanies it.
     */
    public static function isValueless(string $type): bool
    {
        return Comparison::isValueless($type);
    }

    public function build(string $type, string $field, string $param, mixed $value): QueryBuilder
    {
        $expr = $this->queryBuilder->expr();

        match (Comparison::canonical($type)) {
            // No parameter bound on purpose: SQL compares against NULL with a
            // dedicated operator, never with `= :param` (which is never true).
            Comparison::IS_NULL => $this->queryBuilder->andWhere($expr->isNull($field)),
            Comparison::IS_NOT_NULL => $this->queryBuilder->andWhere($expr->isNotNull($field)),

            Comparison::EQ => $this->bind($expr->eq($field, ":$param"), $param, $value),
            Comparison::NEQ => $this->bind($expr->neq($field, ":$param"), $param, $value),
            Comparison::LT => $this->bind($expr->lt($field, ":$param"), $param, $value),
            Comparison::LTE => $this->bind($expr->lte($field, ":$param"), $param, $value),
            Comparison::GT => $this->bind($expr->gt($field, ":$param"), $param, $value),
            Comparison::GTE => $this->bind($expr->gte($field, ":$param"), $param, $value),
            Comparison::IN => $this->bind($expr->in($field, ":$param"), $param, $value),
            Comparison::NOT_IN => $this->bind($expr->notIn($field, ":$param"), $param, $value),
            Comparison::MEMBER_OF => $this->bind($expr->isMemberOf(":$param", $field), $param, $value),

            Comparison::CONTAINS => $this->bind($expr->like($field, ":$param"), $param, '%' . self::likeOperand($value) . '%'),
            Comparison::NOT_CONTAINS => $this->bind($expr->notLike($field, ":$param"), $param, '%' . self::likeOperand($value) . '%'),
            Comparison::STARTS_WITH => $this->bind($expr->like($field, ":$param"), $param, self::likeOperand($value) . '%'),
            Comparison::ENDS_WITH => $this->bind($expr->like($field, ":$param"), $param, '%' . self::likeOperand($value)),

            default => throw new \InvalidArgumentException(sprintf('Unsupported filter type "%s" for "%s"', $type, $field)),
        };

        return $this->queryBuilder;
    }

    private function bind(object $condition, string $param, mixed $value): QueryBuilder
    {
        return $this->queryBuilder->andWhere($condition)->setParameter($param, $value);
    }

    /**
     * A LIKE pattern is built by concatenation, so the operand has to be text:
     * an array or an object would otherwise turn into "Array" or a fatal error.
     */
    private static function likeOperand(mixed $value): string
    {
        if (is_string($value) || is_int($value) || is_float($value) || $value instanceof \Stringable) {
            return (string) $value;
        }

        throw new \InvalidArgumentException(sprintf('A LIKE filter needs a string operand, %s given.', get_debug_type($value)));
    }
}
