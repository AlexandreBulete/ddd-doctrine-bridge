<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Type;

use AlexandreBulete\DddDoctrineBridge\Type\Convertor\AsStringConvertor;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class TextType extends BaseType
{
    use AsStringConvertor;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }
}

