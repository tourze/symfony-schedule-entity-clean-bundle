<?php

namespace Tourze\ScheduleEntityCleanBundle\MessageHandler;

use Carbon\CarbonImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Tourze\ScheduleEntityCleanBundle\Event\ScheduleEntityCleanFinishEvent;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;

#[AsMessageHandler]
class CleanEntityHandler
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CleanEntityMessage $message): void
    {
        $entityManager = $this->registry->getManagerForClass($message->getModelClass());
        $repo = $entityManager->getRepository($message->getModelClass());
        /** @var \Doctrine\ORM\EntityRepository $repo */
        $now = CarbonImmutable::now();
        $keepDay = $message->getKeepDay();
        $lastTime = $now->subDays($keepDay);

        $c = (int) $repo->createQueryBuilder('a')
            ->delete()
            ->where('a.createTime < :time')
            ->setParameter('time', $lastTime)
            ->getQuery()
            ->execute();
        $this->logger->info('清理表数据成功', [
            'modelName' => $message->getModelClass(),
            'count' => $c,
            'lastTime' => $lastTime->toDateTimeString(),
        ]);

        $event = new ScheduleEntityCleanFinishEvent($message->getModelClass());
        $this->eventDispatcher->dispatch($event);
    }
}
