<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;
use Tourze\ScheduleEntityCleanBundle\Event\ScheduleEntityCleanFinishEvent;

/**
 * @internal
 */
#[CoversClass(ScheduleEntityCleanFinishEvent::class)]
final class ScheduleEntityCleanFinishEventTest extends AbstractEventTestCase
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
