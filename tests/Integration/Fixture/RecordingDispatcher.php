<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Tests\Integration\Fixture;

/**
 * Stands in for the event dispatcher DispatchesDomainEvents expects on the
 * repository: it only has to expose dispatch(object).
 */
final class RecordingDispatcher
{
    /** @var list<object> */
    public array $dispatched = [];

    public function dispatch(object $event): object
    {
        $this->dispatched[] = $event;

        return $event;
    }
}
