<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Environment;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\ScheduleEntityCleanBundle\Command\ScheduleCleanEntityCommand;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;
use Tourze\ScheduleEntityCleanBundle\Tests\TestEntity\TestEntityWithEnv;

class KeepDayEnvTest extends TestCase
{
    private MockObject&MessageBusInterface $messageBus;
    private MockObject&EntityManagerInterface $entityManager;
    private ScheduleCleanEntityCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->command = new ScheduleCleanEntityCommand($this->messageBus, $this->entityManager);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testEnvironmentVariableOverride(): void
    {
        // 设置环境变量
        $_ENV['TEST_KEEP_DAY'] = '60';

        // 模拟实体元数据和反射
        $className = TestEntityWithEnv::class;
        $reflection = new \ReflectionClass($className);

        // 准备元数据对象
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getName')->willReturn($className);
        $metadata->method('getReflectionClass')->willReturn($reflection);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->method('getAllMetadata')->willReturn([$metadata]);

        $this->entityManager->method('getMetadataFactory')->willReturn($metadataFactory);

        // 设置时间以确保cron表达式匹配
        Carbon::setTestNow(Carbon::create(2023, 1, 1, 0, 0, 0));

        // 期望消息总线接收消息
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CleanEntityMessage $message) use ($className) {
                $this->assertEquals($className, $message->getModelClass());
                // 验证环境变量覆盖了默认值
                $this->assertEquals(60, $message->getKeepDay());
                return true;
            }));

        // 执行命令
        $this->commandTester->execute([]);

        // 验证命令输出
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('异步进行清理任务', $output);

        // 清理环境变量
        unset($_ENV['TEST_KEEP_DAY']);

        // 重置测试时间
        Carbon::setTestNow();
    }

    public function testMissingEnvironmentVariable(): void
    {
        // 确保环境变量不存在
        unset($_ENV['TEST_KEEP_DAY']);

        // 模拟实体元数据和反射
        $className = TestEntityWithEnv::class;
        $reflection = new \ReflectionClass($className);

        // 准备元数据对象
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getName')->willReturn($className);
        $metadata->method('getReflectionClass')->willReturn($reflection);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->method('getAllMetadata')->willReturn([$metadata]);

        $this->entityManager->method('getMetadataFactory')->willReturn($metadataFactory);

        // 设置时间以确保cron表达式匹配
        Carbon::setTestNow(Carbon::create(2023, 1, 1, 0, 0, 0));

        // 期望消息总线接收消息
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CleanEntityMessage $message) use ($className) {
                $this->assertEquals($className, $message->getModelClass());
                // 验证使用了默认值
                $this->assertEquals(7, $message->getKeepDay());
                return true;
            }));

        // 执行命令
        $this->commandTester->execute([]);

        // 重置测试时间
        Carbon::setTestNow();
    }

    public function testInvalidEnvironmentVariable(): void
    {
        // 设置无效的环境变量值
        $_ENV['TEST_KEEP_DAY'] = 'not-a-number';

        // 模拟实体元数据和反射
        $className = TestEntityWithEnv::class;
        $reflection = new \ReflectionClass($className);

        // 准备元数据对象
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getName')->willReturn($className);
        $metadata->method('getReflectionClass')->willReturn($reflection);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->method('getAllMetadata')->willReturn([$metadata]);

        $this->entityManager->method('getMetadataFactory')->willReturn($metadataFactory);

        // 设置时间以确保cron表达式匹配
        Carbon::setTestNow(Carbon::create(2023, 1, 1, 0, 0, 0));

        // 期望消息总线接收消息
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CleanEntityMessage $message) use ($className) {
                $this->assertEquals($className, $message->getModelClass());
                // 虽然环境变量无效，但intval会将其转为0
                $this->assertEquals(0, $message->getKeepDay());
                return true;
            }));

        // 执行命令
        $this->commandTester->execute([]);

        // 清理环境变量
        unset($_ENV['TEST_KEEP_DAY']);

        // 重置测试时间
        Carbon::setTestNow();
    }
}
