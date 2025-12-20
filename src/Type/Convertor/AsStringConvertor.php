<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Type\Convertor;

use AlexandreBulete\DddFoundation\Domain\ValueObject\StringVO;
use Doctrine\DBAL\Platforms\AbstractPlatform;

trait AsStringConvertor
{
    protected string $voClass;

    public function convertToPHPValue($value, AbstractPlatform $platform): ?StringVO
    {
        if ($value === null) {
            return null;
        }

        if (!isset($this->voClass)) {
            throw new \InvalidArgumentException('Value class not set for ' . static::class);
        }

        if (!class_exists($this->voClass)) {
            throw new \InvalidArgumentException('Invalid value class: ' . $this->voClass);
        }

        return $this->voClass::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value?->value();
    }
}

