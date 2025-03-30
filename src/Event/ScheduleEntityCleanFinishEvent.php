<?php

namespace Tourze\ScheduleEntityCleanBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ScheduleEntityCleanFinishEvent extends Event
{
    public function __construct(
        private readonly string $modelClass,
    )
    {
    }

    public function getModelClass(): string
    {
        return $this->modelClass;
    }
}
