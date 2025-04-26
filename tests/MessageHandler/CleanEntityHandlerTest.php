<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\ScheduleEntityCleanBundle\Event\ScheduleEntityCleanFinishEvent;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;
use Tourze\ScheduleEntityCleanBundle\MessageHandler\CleanEntityHandler;

class CleanEntityHandlerTest extends TestCase
{
    private MockObject&ManagerRegistry $registry;
    private MockObject&LoggerInterface $logger;
    private MockObject&EventDispatcherInterface $eventDispatcher;
    private CleanEntityHandler $handler;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->handler = new CleanEntityHandler(
            $this->registry,
            $this->logger,
            $this->eventDispatcher
        );
    }

    public function testInvokeSuccessful(): void
    {
        $modelClass = 'App\Entity\TestEntity';
        $keepDay = 30;
        $deletedCount = 5;

        $message = new CleanEntityMessage();
        $message->setModelClass($modelClass);
        $message->setKeepDay($keepDay);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        // 设置模拟对象的行为
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($modelClass)
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with($modelClass)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('delete')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.createTime < :time')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('time', $this->anything())
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('execute')
            ->willReturn($deletedCount);

        // 检查是否正确记录日志
        $this->logger->expects($this->once())
            ->method('info')
            ->with('清理表数据成功', $this->callback(function ($params) use ($modelClass, $deletedCount) {
                return $params['modelName'] === $modelClass
                    && $params['count'] === $deletedCount
                    && isset($params['lastTime']);
            }));

        // 检查是否触发事件
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($event) use ($modelClass) {
                return $event instanceof ScheduleEntityCleanFinishEvent
                    && $event->getModelClass() === $modelClass;
            }))
            ->willReturnArgument(0);

        // 执行处理函数
        $this->handler->__invoke($message);
    }

    public function testInvokeWithZeroKeepDay(): void
    {
        $modelClass = 'App\Entity\TestEntity';
        $keepDay = 0;

        $message = new CleanEntityMessage();
        $message->setModelClass($modelClass);
        $message->setKeepDay($keepDay);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($modelClass)
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with($modelClass)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('delete')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.createTime < :time')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('time', $this->callback(function ($time) {
                // 保留天数为0，所以应该使用当前时间
                return $time instanceof \DateTimeInterface;
            }))
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('execute')
            ->willReturn(0);

        $this->handler->__invoke($message);
    }

    public function testInvokeWithNegativeKeepDay(): void
    {
        $modelClass = 'App\Entity\TestEntity';
        $keepDay = -10;

        $message = new CleanEntityMessage();
        $message->setModelClass($modelClass);
        $message->setKeepDay($keepDay);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($modelClass)
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with($modelClass)
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('a')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('delete')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.createTime < :time')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('time', $this->callback(function ($time) {
                // 负数保留天数应该导致在将来的日期(添加而不是减去天数)
                return $time instanceof \DateTimeInterface;
            }))
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('execute')
            ->willReturn(100);

        $this->handler->__invoke($message);
    }
}
