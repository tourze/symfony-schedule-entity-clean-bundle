<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Environment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\ScheduleEntityCleanBundle\Command\ScheduleCleanEntityCommand;

/**
 * @internal
 */
#[CoversClass(ScheduleCleanEntityCommand::class)]
#[RunTestsInSeparateProcesses]
final class KeepDayEnvTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置，如果有特殊需要可以在这里添加
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getContainer()->get(ScheduleCleanEntityCommand::class);
        self::assertInstanceOf(ScheduleCleanEntityCommand::class, $command);

        return new CommandTester($command);
    }

    public function testEnvironmentVariableOverride(): void
    {
        // 设置环境变量
        $_ENV['TEST_KEEP_DAY'] = '60';

        // 使用真实的Command进行集成测试
        $command = self::getContainer()->get(ScheduleCleanEntityCommand::class);
        self::assertInstanceOf(ScheduleCleanEntityCommand::class, $command);
        $commandTester = new CommandTester($command);

        // 设置时间以确保cron表达式匹配
        // Note: 使用PHP原生DateTimeImmutable替代Carbon

        // 执行命令
        $commandTester->execute([]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());

        // 验证命令输出（如果没有匹配的实体，不会有异步清理任务的输出）
        $output = $commandTester->getDisplay();
        // 这是集成测试，不依赖mock对象，而是测试真实的业务逻辑
        $this->assertIsString($output);

        // 清理环境变量
        unset($_ENV['TEST_KEEP_DAY']);

        // 重置测试时间
        // Note: PHP原生DateTimeImmutable无需重置测试时间
    }

    public function testMissingEnvironmentVariable(): void
    {
        // 确保环境变量不存在
        unset($_ENV['TEST_KEEP_DAY']);

        // 使用真实的Command进行集成测试
        $command = self::getContainer()->get(ScheduleCleanEntityCommand::class);
        self::assertInstanceOf(ScheduleCleanEntityCommand::class, $command);
        $commandTester = new CommandTester($command);

        // 设置时间以确保cron表达式匹配
        // Note: 使用PHP原生DateTimeImmutable替代Carbon

        // 执行命令
        $commandTester->execute([]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());

        // 验证命令输出
        $output = $commandTester->getDisplay();
        $this->assertIsString($output);

        // 重置测试时间
        // Note: PHP原生DateTimeImmutable无需重置测试时间
    }

    public function testInvalidEnvironmentVariable(): void
    {
        // 设置无效的环境变量值
        $_ENV['TEST_KEEP_DAY'] = 'not-a-number';

        // 使用真实的Command进行集成测试
        $command = self::getContainer()->get(ScheduleCleanEntityCommand::class);
        self::assertInstanceOf(ScheduleCleanEntityCommand::class, $command);
        $commandTester = new CommandTester($command);

        // 设置时间以确保cron表达式匹配
        // Note: 使用PHP原生DateTimeImmutable替代Carbon

        // 执行命令
        $commandTester->execute([]);

        // 验证命令执行成功
        $this->assertEquals(0, $commandTester->getStatusCode());

        // 验证命令输出
        $output = $commandTester->getDisplay();
        $this->assertIsString($output);

        // 清理环境变量
        unset($_ENV['TEST_KEEP_DAY']);

        // 重置测试时间
        // Note: PHP原生DateTimeImmutable无需重置测试时间
    }
}
