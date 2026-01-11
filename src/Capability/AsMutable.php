<?php

namespace AlexandreBulete\DddDoctrineBridge\Capability;

use AlexandreBulete\DddDoctrineBridge\Operation\CanPersist;
use AlexandreBulete\DddDoctrineBridge\Operation\CanRemove;
use AlexandreBulete\DddDoctrineBridge\DispatchesDomainEvents;

trait AsMutable
{
    use CanPersist;
    use CanRemove;
    use DispatchesDomainEvents;
}