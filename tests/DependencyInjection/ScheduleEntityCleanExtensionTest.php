<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\ScheduleEntityCleanBundle\Command\ScheduleCleanEntityCommand;
use Tourze\ScheduleEntityCleanBundle\DependencyInjection\ScheduleEntityCleanExtension;
use Tourze\ScheduleEntityCleanBundle\MessageHandler\CleanEntityHandler;

/**
 * @internal
 */
#[CoversClass(ScheduleEntityCleanExtension::class)]
final class ScheduleEntityCleanExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Extension 测试不需要特殊的设置
    }

    public function testLoadExtension(): void
    {
        $extension = new ScheduleEntityCleanExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        $extension->load([], $container);

        // 断言关键服务已注册
        $this->assertTrue($container->hasDefinition(ScheduleCleanEntityCommand::class)
            || $container->hasAlias(ScheduleCleanEntityCommand::class),
            '必须注册清理命令服务');

        $this->assertTrue($container->hasDefinition(CleanEntityHandler::class)
            || $container->hasAlias(CleanEntityHandler::class),
            '必须注册消息处理器服务');
    }

    public function testHasCorrectAlias(): void
    {
        $extension = new ScheduleEntityCleanExtension();
        $this->assertEquals('schedule_entity_clean', $extension->getAlias());
    }
}
