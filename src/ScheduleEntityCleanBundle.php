<?php

namespace Tourze\ScheduleEntityCleanBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\Symfony\CronJob\CronJobBundle;

class ScheduleEntityCleanBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            CronJobBundle::class => ['all' => true],
        ];
    }
}
