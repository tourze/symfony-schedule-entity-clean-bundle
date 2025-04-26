<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\ScheduleEntityCleanBundle\Command\ScheduleCleanEntityCommand;
use Tourze\ScheduleEntityCleanBundle\DependencyInjection\ScheduleEntityCleanExtension;
use Tourze\ScheduleEntityCleanBundle\MessageHandler\CleanEntityHandler;

class ScheduleEntityCleanExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private ScheduleEntityCleanExtension $extension;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->extension = new ScheduleEntityCleanExtension();
    }

    public function testLoadExtension(): void
    {
        $this->extension->load([], $this->container);

        // 断言关键服务已注册
        $this->assertTrue($this->container->hasDefinition(ScheduleCleanEntityCommand::class) ||
            $this->container->hasAlias(ScheduleCleanEntityCommand::class),
            '必须注册清理命令服务');

        $this->assertTrue($this->container->hasDefinition(CleanEntityHandler::class) ||
            $this->container->hasAlias(CleanEntityHandler::class),
            '必须注册消息处理器服务');
    }

    public function testHasCorrectAlias(): void
    {
        $this->assertEquals('schedule_entity_clean', $this->extension->getAlias());
    }
}
