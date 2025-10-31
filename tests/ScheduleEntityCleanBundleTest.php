<?php

declare(strict_types=1);

namespace Tourze\ScheduleEntityCleanBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\ScheduleEntityCleanBundle\ScheduleEntityCleanBundle;

/**
 * @internal
 */
#[CoversClass(ScheduleEntityCleanBundle::class)]
#[RunTestsInSeparateProcesses]
final class ScheduleEntityCleanBundleTest extends AbstractBundleTestCase
{
}
