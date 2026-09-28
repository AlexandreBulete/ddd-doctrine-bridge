<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge;

/**
 * Dispatches the events an aggregate recorded (foundation's RecordsEvents).
 *
 * Contract since 1.0: the using class declares an `$eventDispatcher` property
 * exposing `dispatch(object)` — typically Symfony's EventDispatcherInterface,
 * injected through the repository constructor. A trait cannot require a
 * property in PHP's type system, hence the runtime check below.
 */
trait DispatchesDomainEvents
{
    protected function dispatchEvents(object $entity): void
    {
        if (!method_exists($entity, 'releaseEvents')) {
            throw new \BadMethodCallException(sprintf('%s does not record events (use RecordsEvents).', $entity::class));
        }

        if (!isset($this->eventDispatcher)) {
            throw new \LogicException('Event dispatcher not set. Inject EventDispatcherInterface in your repository constructor.');
        }

        $events = $entity->releaseEvents();
        if (!is_iterable($events)) {
            throw new \UnexpectedValueException(sprintf('%s::releaseEvents() must return an iterable.', $entity::class));
        }

        foreach ($events as $event) {
            if (!is_object($event)) {
                throw new \UnexpectedValueException(sprintf('%s released a %s, not an event object.', $entity::class, get_debug_type($event)));
            }

            $this->eventDispatcher->dispatch($event);
        }
    }
}
