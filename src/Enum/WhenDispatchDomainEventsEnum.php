<?php 

namespace AlexandreBulete\DddDoctrineBridge\Enum;

enum WhenDispatchDomainEventsEnum: string
{
    case BEFORE = 'before';
    case AFTER = 'after';

    public function isBefore(): bool
    {
        return $this === self::BEFORE;
    }

    public function isAfter(): bool
    {
        return $this === self::AFTER;
    }
}