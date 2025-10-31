<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\MessageHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;
use Tourze\ScheduleEntityCleanBundle\MessageHandler\CleanEntityHandler;

/**
 * @internal
 */
#[CoversClass(CleanEntityHandler::class)]
#[RunTestsInSeparateProcesses]
final class CleanEntityHandlerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    public function testHandlerServiceExists(): void
    {
        // 测试服务是否能从容器中正确获取
        $handler = self::getContainer()->get(CleanEntityHandler::class);
        $this->assertInstanceOf(CleanEntityHandler::class, $handler);
    }

    public function testMessageCreationAndBasicProperties(): void
    {
        // 测试消息创建和基本属性设置
        $message = new CleanEntityMessage();
        $modelClass = 'App\Entity\TestEntity';
        $keepDay = 30;

        $message->setModelClass($modelClass);
        $message->setKeepDay($keepDay);

        $this->assertEquals($modelClass, $message->getModelClass());
        $this->assertEquals($keepDay, $message->getKeepDay());
    }
}
