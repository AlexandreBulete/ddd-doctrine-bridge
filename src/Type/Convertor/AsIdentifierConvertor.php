<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Type\Convertor;

use Symfony\Component\Uid\AbstractUid;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;

trait AsIdentifierConvertor
{
    protected string $voClass;

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?IdentifierVO
    {
        if ($value === null) {
            return null;
        }

        if (!isset($this->voClass)) {
            throw new \InvalidArgumentException('Value class not set for ' . static::class);
        }

        if (!is_a($this->voClass, IdentifierVO::class, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid value class "%s": must extend %s.', $this->voClass, IdentifierVO::class));
        }

        if (!is_string($value)) {
            throw new \UnexpectedValueException(sprintf('Cannot hydrate %s from %s.', $this->voClass, get_debug_type($value)));
        }

        return $this->voClass::fromString($value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (is_string($value) || is_null($value)) {
            return $value;
        }

        if ($value instanceof IdentifierVO) {
            $value = $value->value();
        }

        if ($value instanceof AbstractUid) {
            return $value->toRfc4122();
        }

        throw new \InvalidArgumentException('Invalid value type: ' . gettype($value));
    }
}

