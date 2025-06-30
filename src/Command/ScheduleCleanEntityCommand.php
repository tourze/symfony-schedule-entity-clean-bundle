<?php

namespace Tourze\ScheduleEntityCleanBundle\Command;

use Carbon\CarbonImmutable;
use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask(expression: '* * * * *')]
#[AsCommand(name: self::NAME, description: '定期清理实体数据')]
class ScheduleCleanEntityCommand extends Command
{
    const NAME = 'schedule-entity-clean:run';

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = CarbonImmutable::now();

        $metas = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $className = $meta->getName();
            $reflection = $meta->getReflectionClass();

            if (!$reflection->hasProperty('createTime')) {
                $output->writeln("{$className}缺少 createTime 字段，不需要清除");
                continue;
            }

            $attributes = $reflection->getAttributes(AsScheduleClean::class);
            if (empty($attributes)) {
                $output->writeln("{$className}不需要自动清理");
                continue;
            }

            foreach ($attributes as $attribute) {
                $attribute = $attribute->newInstance();
                /** @var AsScheduleClean $attribute */
                $cron = new CronExpression($attribute->expression);
                if (!$cron->isDue($now)) {
                    continue;
                }

                $keepDay = $attribute->defaultKeepDay;
                if ($attribute->keepDayEnv !== null && $attribute->keepDayEnv !== '' && isset($_ENV[$attribute->keepDayEnv])) {
                    $keepDay = intval($_ENV[$attribute->keepDayEnv]);
                }

                $message = new CleanEntityMessage();
                $message->setModelClass($className);
                $message->setKeepDay($keepDay);
                $this->messageBus->dispatch($message);
                $output->writeln("{$className}异步进行清理任务");
            }
        }

        return Command::SUCCESS;
    }
}
