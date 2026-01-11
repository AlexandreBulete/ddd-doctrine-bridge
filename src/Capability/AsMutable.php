<?php

namespace AlexandreBulete\DddDoctrineBridge\Capability;

use AlexandreBulete\DddDoctrineBridge\Operation\CanPersist;
use AlexandreBulete\DddDoctrineBridge\Operation\CanRemove;

trait AsMutable
{
    use CanPersist;
    use CanRemove;
}