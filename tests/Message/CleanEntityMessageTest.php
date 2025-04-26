<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Message;

use PHPUnit\Framework\TestCase;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;

class CleanEntityMessageTest extends TestCase
{
    public function testGetSetModelClass(): void
    {
        $message = new CleanEntityMessage();
        $modelClass = 'App\Entity\TestEntity';

        $message->setModelClass($modelClass);
        $this->assertEquals($modelClass, $message->getModelClass());
    }

    public function testGetSetKeepDay(): void
    {
        $message = new CleanEntityMessage();

        // 默认值应为7
        $this->assertEquals(7, $message->getKeepDay());

        // 设置新值
        $message->setKeepDay(30);
        $this->assertEquals(30, $message->getKeepDay());
    }

    public function testZeroKeepDay(): void
    {
        $message = new CleanEntityMessage();
        $message->setKeepDay(0);

        $this->assertEquals(0, $message->getKeepDay());
    }

    public function testNegativeKeepDay(): void
    {
        $message = new CleanEntityMessage();
        $message->setKeepDay(-1);

        $this->assertEquals(-1, $message->getKeepDay());
    }
}
