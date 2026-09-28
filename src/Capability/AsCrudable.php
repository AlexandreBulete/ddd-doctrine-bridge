<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Capability;

trait AsCrudable
{
    use AsReadable;
    use AsMutable;
}