<?php 

namespace AlexandreBulete\DddDoctrineBridge\Operation;

use Doctrine\ORM\EntityManagerInterface;
use AlexandreBulete\DddDoctrineBridge\Enum\WhenDispatchDomainEventsEnum;

trait CanRemove
{
    protected EntityManagerInterface $em;
    
    protected function removeAndFlush(
        object $entity,
        bool $dispatchEvents = false,
        WhenDispatchDomainEventsEnum $whenDispatchEvents = WhenDispatchDomainEventsEnum::AFTER,
    ): void
    {
        if ($dispatchEvents && $whenDispatchEvents->isBefore()) {
            $this->dispatchEvents($entity);
        }

        $this->em->remove($entity);
        $this->em->flush();

        if ($dispatchEvents && $whenDispatchEvents->isAfter()) {
            $this->dispatchEvents($entity);
        }
    }
}