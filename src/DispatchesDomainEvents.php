<?php 

namespace AlexandreBulete\DddDoctrineBridge;

use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * Requires the class to have a property: EventDispatcherInterface $eventDispatcher
 */
trait DispatchesDomainEvents
{
    /**
     * @param object&RecordsEvents $entity
     */
    protected function dispatchEvents(object $entity): void
    {
        if (!method_exists($entity, 'releaseEvents')) {
            throw new \BadMethodCallException('Entity does not implement RecordsEvents methods');
        }

        if (!isset($this->eventDispatcher)) {
            throw new \LogicException('Event dispatcher not set. Inject EventDispatcherInterface in your repository constructor.');
        }

        foreach ($entity->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}