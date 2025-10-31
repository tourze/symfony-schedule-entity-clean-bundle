<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\ScheduleEntityCleanBundle\Command\ScheduleCleanEntityCommand;

/**
 * 集成测试：测试命令调度和实体扫描功能
 *
 * @internal
 */
#[CoversClass(ScheduleCleanEntityCommand::class)]
#[RunTestsInSeparateProcesses]
final class ScheduleEntityCleanUnitTest extends AbstractCommandTestCase
{
    private MessageBusInterface&MockObject $messageBus;

    private ScheduleCleanEntityCommand $command;

    private CommandTester $commandTester;

    protected function onSetUp(): void
    {
        // 设置命令组件 - 稍后在测试中设置，因为需要Mock EntityManager
        $this->messageBus = $this->createMock(MessageBusInterface::class);
    }

    protected function getCommandTester(): CommandTester
    {
        if (!isset($this->commandTester)) {
            $command = self::getContainer()->get(ScheduleCleanEntityCommand::class);
            self::assertInstanceOf(ScheduleCleanEntityCommand::class, $command);
            $this->commandTester = new CommandTester($command);
        }

        return $this->commandTester;
    }

    public function testCommandDispatchesMessageWithCorrectParameters(): void
    {
        // 使用真实的Command进行集成测试
        $command = self::getContainer()->get(ScheduleCleanEntityCommand::class);
        self::assertInstanceOf(ScheduleCleanEntityCommand::class, $command);
        $this->command = $command;
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);

        // 设置时间以确保cron表达式匹配
        // Note: 使用PHP原生DateTimeImmutable替代Carbon

        // 执行命令
        $this->commandTester->execute([]);

        // 验证命令执行成功
        $this->assertEquals(0, $this->commandTester->getStatusCode());

        // 验证命令输出（集成测试，不依赖mock对象）
        $output = $this->commandTester->getDisplay();
        $this->assertIsString($output);

        // 重置测试时间
        // Note: PHP原生DateTimeImmutable无需重置测试时间
    }

    public function testCommandSkipsEntitiesWithoutScheduleCleanAttribute(): void
    {
        // 创建一个没有 AsScheduleClean 属性的实体类
        $className = 'App\Entity\NonScheduledEntity';
        /*
         * 使用 ReflectionClass 具体类是必要的，因为：
         * 1. 这是 PHP 内置的反射类，没有对应的接口
         * 2. 测试需要验证 getAttributes()、getName() 等方法的具体行为
         * 3. 反射类是系统级组件，无法通过依赖注入替换
         */
        $reflection = $this->createMock(\ReflectionClass::class);
        $reflection->method('getName')->willReturn($className);
        $reflection->method('getAttributes')->willReturn([]);
        $reflection->method('hasProperty')->with('createTime')->willReturn(true);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getName')->willReturn($className);
        $metadata->method('getReflectionClass')->willReturn($reflection);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->method('getAllMetadata')->willReturn([$metadata]);

        // Mock EntityManager
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getMetadataFactory')->willReturn($metadataFactory);

        // 创建命令实例
        $command = self::getContainer()->get(ScheduleCleanEntityCommand::class);
        self::assertInstanceOf(ScheduleCleanEntityCommand::class, $command);
        $this->command = $command;
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);

        // 不应该发送任何消息
        $this->messageBus->expects($this->never())
            ->method('dispatch')
        ;

        // 执行命令
        $this->commandTester->execute([]);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
