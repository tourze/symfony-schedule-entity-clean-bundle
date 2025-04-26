<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Integration;

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\ScheduleEntityCleanBundle\Command\ScheduleCleanEntityCommand;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;
use Tourze\ScheduleEntityCleanBundle\MessageHandler\CleanEntityHandler;
use Tourze\ScheduleEntityCleanBundle\Tests\TestEntity\TestEntity;

class ScheduleEntityCleanIntegrationTest extends TestCase
{
    private MockObject&MessageBusInterface $messageBus;
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&ManagerRegistry $registry;
    private MockObject&LoggerInterface $logger;
    private MockObject&EventDispatcherInterface $eventDispatcher;
    private ScheduleCleanEntityCommand $command;
    private CleanEntityHandler $handler;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        // 设置命令组件
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->command = new ScheduleCleanEntityCommand($this->messageBus, $this->entityManager);

        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);

        // 设置消息处理程序
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->handler = new CleanEntityHandler(
            $this->registry,
            $this->logger,
            $this->eventDispatcher
        );
    }

    public function testFullCleanupFlow(): void
    {
        // 模拟实体元数据和反射
        $className = TestEntity::class;
        $reflection = new \ReflectionClass($className);

        // 准备元数据对象
        $metadata = $this->createMock(\Doctrine\ORM\Mapping\ClassMetadata::class);
        $metadata->method('getName')->willReturn($className);
        $metadata->method('getReflectionClass')->willReturn($reflection);

        $metadataFactory = $this->createMock(\Doctrine\ORM\Mapping\ClassMetadataFactory::class);
        $metadataFactory->method('getAllMetadata')->willReturn([$metadata]);

        $this->entityManager->method('getMetadataFactory')->willReturn($metadataFactory);

        // 设置时间以确保cron表达式匹配
        Carbon::setTestNow(Carbon::create(2023, 1, 1, 0, 0, 0));

        // 期望消息总线接收消息
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($message) use ($className) {
                $this->assertInstanceOf(CleanEntityMessage::class, $message);
                $this->assertEquals($className, $message->getModelClass());
                $this->assertEquals(30, $message->getKeepDay());
                return true;
            }))
            ->willReturn(new Envelope(new CleanEntityMessage()));

        // 执行命令
        $this->commandTester->execute([]);

        // 验证命令输出
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('异步进行清理任务', $output);

        // 准备模拟消息处理
        $message = new CleanEntityMessage();
        $message->setModelClass($className);
        $message->setKeepDay(30);

        // 模拟存储库和查询构建器
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($className)
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with($className)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())->method('delete')->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('where')->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('setParameter')->willReturn($queryBuilder);
        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);

        $query->expects($this->once())
            ->method('execute')
            ->willReturn(15); // 模拟删除了15条记录

        // 期望事件调度
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($className) {
                return $event->getModelClass() === $className;
            }));

        // 执行处理器
        $this->handler->__invoke($message);

        // 重置测试时间
        Carbon::setTestNow();
    }
}
