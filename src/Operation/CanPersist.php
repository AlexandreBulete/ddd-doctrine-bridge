<?php

namespace AlexandreBulete\DddDoctrineBridge\Operation;

use Doctrine\ORM\EntityManagerInterface;
use AlexandreBulete\DddDoctrineBridge\DispatchesDomainEvents;
use AlexandreBulete\DddDoctrineBridge\Enum\WhenDispatchDomainEventsEnum;

trait CanPersist
{
    use DispatchesDomainEvents;

    protected EntityManagerInterface $em;
    
    protected function persistAndFlush(
        object $entity,
        bool $dispatchEvents = false,
        WhenDispatchDomainEventsEnum $whenDispatchEvents = WhenDispatchDomainEventsEnum::AFTER,
    ): void
    {
        if ($dispatchEvents && $whenDispatchEvents->isBefore()) {
            $this->dispatchEvents($entity);
        }

        $this->em->persist($entity);
        $this->em->flush();

        if ($dispatchEvents && $whenDispatchEvents->isAfter()) {
            $this->dispatchEvents($entity);
        }
    }
}