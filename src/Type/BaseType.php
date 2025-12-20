<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Type;

use Doctrine\DBAL\Types\Type;

abstract class BaseType extends Type
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }
}

