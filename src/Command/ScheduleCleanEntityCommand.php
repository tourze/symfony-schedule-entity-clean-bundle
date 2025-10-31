<?php

namespace Tourze\ScheduleEntityCleanBundle\Command;

use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use Tourze\ScheduleEntityCleanBundle\Message\CleanEntityMessage;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask(expression: '* * * * *')]
#[AsCommand(name: self::NAME, description: '定期清理实体数据')]
#[Autoconfigure(public: true)]
class ScheduleCleanEntityCommand extends Command
{
    public const NAME = 'schedule-entity-clean:run';

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $metas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metas as $meta) {
            $this->processEntityMeta($meta, $now, $output);
        }

        return Command::SUCCESS;
    }

    /**
     * @param ClassMetadata<object> $meta
     */
    private function processEntityMeta(ClassMetadata $meta, \DateTimeImmutable $now, OutputInterface $output): void
    {
        $className = $meta->getName();
        $reflection = $meta->getReflectionClass();

        if (!$this->hasCreateTimeProperty($reflection, $className, $output)) {
            return;
        }

        $attributes = $reflection->getAttributes(AsScheduleClean::class);
        if (0 === count($attributes)) {
            $output->writeln("{$className}不需要自动清理");

            return;
        }

        $this->processCleanAttributes($attributes, $className, $now, $output);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private function hasCreateTimeProperty(\ReflectionClass $reflection, string $className, OutputInterface $output): bool
    {
        if (!$reflection->hasProperty('createTime')) {
            $output->writeln("{$className}缺少 createTime 字段，不需要清除");

            return false;
        }

        return true;
    }

    /**
     * @param array<\ReflectionAttribute<AsScheduleClean>> $attributes
     */
    private function processCleanAttributes(array $attributes, string $className, \DateTimeImmutable $now, OutputInterface $output): void
    {
        foreach ($attributes as $attribute) {
            $cleanAttribute = $attribute->newInstance();
            /** @var AsScheduleClean $cleanAttribute */
            if (!$this->shouldCleanNow($cleanAttribute, $now)) {
                continue;
            }

            $keepDay = $this->getKeepDay($cleanAttribute);
            $this->dispatchCleanMessage($className, $keepDay, $output);
        }
    }

    private function shouldCleanNow(AsScheduleClean $attribute, \DateTimeImmutable $now): bool
    {
        $cron = new CronExpression($attribute->expression);

        return $cron->isDue($now);
    }

    private function getKeepDay(AsScheduleClean $attribute): int
    {
        $keepDay = $attribute->defaultKeepDay;
        if (null !== $attribute->keepDayEnv && '' !== $attribute->keepDayEnv && isset($_ENV[$attribute->keepDayEnv])) {
            $envValue = $_ENV[$attribute->keepDayEnv];
            if (is_string($envValue) || is_numeric($envValue)) {
                $keepDay = intval($envValue);
            }
        }

        return $keepDay;
    }

    private function dispatchCleanMessage(string $className, int $keepDay, OutputInterface $output): void
    {
        $message = new CleanEntityMessage();
        $message->setModelClass($className);
        $message->setKeepDay($keepDay);
        $this->messageBus->dispatch($message);
        $output->writeln("{$className}异步进行清理任务");
    }
}
