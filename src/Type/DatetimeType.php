<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Type;

use AlexandreBulete\DddDoctrineBridge\Type\Convertor\AsDatetimeConvertor;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class DatetimeType extends BaseType
{
    use AsDatetimeConvertor;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDateTypeDeclarationSQL($column);
    }
}

