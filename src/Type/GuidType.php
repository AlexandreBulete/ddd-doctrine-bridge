<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Type;

use AlexandreBulete\DddDoctrineBridge\Type\Convertor\AsIdentifierConvertor;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class GuidType extends BaseType
{
    use AsIdentifierConvertor;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }
}

