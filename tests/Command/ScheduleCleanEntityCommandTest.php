<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\ScheduleEntityCleanBundle\Command\ScheduleCleanEntityCommand;

/**
 * @internal
 */
#[CoversClass(ScheduleCleanEntityCommand::class)]
#[RunTestsInSeparateProcesses]
final class ScheduleCleanEntityCommandTest extends AbstractCommandTestCase
{
    private MockObject&MessageBusInterface $messageBus;

    private ScheduleCleanEntityCommand $command;

    private CommandTester $commandTester;

    protected function onSetUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $command = self::getContainer()->get(ScheduleCleanEntityCommand::class);
        self::assertInstanceOf(ScheduleCleanEntityCommand::class, $command);
        $this->command = $command;
        $this->commandTester = new CommandTester($this->command);
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

    public function testExecuteWithNoEntities(): void
    {
        // 测试没有实体的情况 - 使用真实的EntityManager，数据库默认是空的
        $this->messageBus->expects($this->never())
            ->method('dispatch')
        ;

        $this->commandTester->execute([]);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteBasicFunctionality(): void
    {
        // 基本功能测试：确保命令能够正常执行
        $this->messageBus->expects($this->any())
            ->method('dispatch')
        ;

        $this->commandTester->execute([]);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
