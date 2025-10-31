<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;

/**
 * @internal
 */
#[CoversClass(AsScheduleClean::class)]
final class AsScheduleCleanTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $attribute = new AsScheduleClean();

        $this->assertEquals('0 0 * * *', $attribute->expression);
        $this->assertEquals(7, $attribute->defaultKeepDay);
        $this->assertNull($attribute->keepDayEnv);
    }

    public function testCustomValues(): void
    {
        $attribute = new AsScheduleClean(
            expression: '*/5 * * * *',
            defaultKeepDay: 30,
            keepDayEnv: 'KEEP_DAY'
        );

        $this->assertEquals('*/5 * * * *', $attribute->expression);
        $this->assertEquals(30, $attribute->defaultKeepDay);
        $this->assertEquals('KEEP_DAY', $attribute->keepDayEnv);
    }
}
