<?php

namespace AlexandreBulete\DddDoctrineBridge\Capability;

use AlexandreBulete\DddDoctrineBridge\Operation\CanFindAll;
use AlexandreBulete\DddDoctrineBridge\Operation\CanFindById;

trait AsReadable
{
    use CanFindById;
    use CanFindAll;
}