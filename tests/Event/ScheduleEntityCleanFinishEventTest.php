<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\ScheduleEntityCleanBundle\Event\ScheduleEntityCleanFinishEvent;

class ScheduleEntityCleanFinishEventTest extends TestCase
{
    public function testGetModelClass(): void
    {
        $modelClass = 'App\Entity\TestEntity';
        $event = new ScheduleEntityCleanFinishEvent($modelClass);

        $this->assertEquals($modelClass, $event->getModelClass());
    }

    public function testConstructorWithEmptyString(): void
    {
        $event = new ScheduleEntityCleanFinishEvent('');

        $this->assertEquals('', $event->getModelClass());
    }
}
