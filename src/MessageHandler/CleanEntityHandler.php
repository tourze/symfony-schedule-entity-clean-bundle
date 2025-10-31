<?php

namespace Tourze\ScheduleEntityCleanBundle\MessageHandler;

use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Tourze\ScheduleEntityCleanBundle\Event\ScheduleEntityCleanFinishEvent;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;

#[AsMessageHandler]
#[WithMonologChannel(channel: 'schedule_entity_clean')]
readonly class CleanEntityHandler
{
    public function __construct(
        private ManagerRegistry $registry,
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CleanEntityMessage $message): void
    {
        $modelClass = $message->getModelClass();
        /** @var class-string $modelClass */
        $entityManager = $this->registry->getManagerForClass($modelClass);

        if (null === $entityManager) {
            $this->logger->error('无法找到实体管理器', ['modelClass' => $modelClass]);

            return;
        }

        $repo = $entityManager->getRepository($modelClass);
        /** @var EntityRepository<object> $repo */
        $now = new \DateTimeImmutable();
        $keepDay = $message->getKeepDay();
        $lastTime = $now->modify("-{$keepDay} days");

        $result = $repo->createQueryBuilder('a')
            ->delete()
            ->where('a.createTime < :time')
            ->setParameter('time', $lastTime)
            ->getQuery()
            ->execute()
        ;
        $c = is_int($result) ? $result : 0;
        $this->logger->info('清理表数据成功', [
            'modelName' => $message->getModelClass(),
            'count' => $c,
            'lastTime' => $lastTime->format('Y-m-d H:i:s'),
        ]);

        $event = new ScheduleEntityCleanFinishEvent($message->getModelClass());
        $this->eventDispatcher->dispatch($event);
    }
}
