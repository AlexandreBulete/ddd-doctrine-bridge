<?php 

namespace AlexandreBulete\DddDoctrineBridge;

use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;
use AlexandreBulete\DddFoundation\Application\Event\EventDispatcherInterface;

trait DispatchesDomainEvents
{
    protected readonly EventDispatcherInterface $eventDispatcher;
    
    /**
     * @param object&RecordsEvents $entity
     */
    protected function dispatchEvents(object $entity): void
    {
        if (!method_exists($entity, 'releaseEvents')) {
            throw new \BadMethodCallException('Entity does not implement RecordsEvents methods');
        }

        if (!$this->eventDispatcher) {
            throw new \LogicException('Event dispatcher not set');
        }

        foreach ($entity->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}