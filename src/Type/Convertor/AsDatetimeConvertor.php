<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Type\Convertor;

use AlexandreBulete\DddFoundation\Domain\ValueObject\DatetimeVO;
use Doctrine\DBAL\Platforms\AbstractPlatform;

trait AsDatetimeConvertor
{
    protected string $voClass;

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DatetimeVO
    {
        if ($value === null || $value instanceof DatetimeVO) {
            return $value;
        }

        if (!isset($this->voClass)) {
            throw new \InvalidArgumentException('Value class not set for ' . static::class);
        }

        if (!is_subclass_of($this->voClass, DatetimeVO::class)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value class "%s": must extend %s.',
                $this->voClass,
                DatetimeVO::class,
            ));
        }

        if (!$value instanceof \DateTimeImmutable && !is_string($value)) {
            throw new \UnexpectedValueException(sprintf('Cannot hydrate %s from %s.', $this->voClass, get_debug_type($value)));
        }

        $dateTime = $value instanceof \DateTimeImmutable
            ? $value
            : \DateTimeImmutable::createFromFormat('!' . $platform->getDateFormatString(), $value);

        if ($dateTime === false) {
            throw new \UnexpectedValueException(sprintf(
                'Cannot hydrate "%s" as %s (expected format %s).',
                $value,
                $this->voClass,
                $platform->getDateFormatString(),
            ));
        }

        // Use late binding on the configured VO class so subclasses (e.g. BirthDate)
        // are instantiated — and their `validate()` hook runs — instead of the
        // base DatetimeVO. Mirrors AsStringConvertor's `$this->voClass::fromString`.
        return $this->voClass::fromDateTime($dateTime);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DatetimeVO) {
            return $value->format($platform->getDateFormatString());
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value->format($platform->getDateFormatString());
        }

        throw new \UnexpectedValueException(sprintf(
            'Cannot persist value of type "%s" as a DatetimeVO.',
            get_debug_type($value),
        ));
    }
}

