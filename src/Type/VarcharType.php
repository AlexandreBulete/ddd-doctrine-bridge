<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Type;

use AlexandreBulete\DddDoctrineBridge\Type\Convertor\AsStringConvertor;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class VarcharType extends BaseType
{
    use AsStringConvertor;

    protected int $length = 255;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(
            ['length' => $this->length]
        );
    }
}

