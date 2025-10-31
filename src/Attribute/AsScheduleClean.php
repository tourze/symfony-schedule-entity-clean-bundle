<?php

namespace Tourze\ScheduleEntityCleanBundle\Attribute;

/**
 * 在实体上使用这个注解，意思是按照声明那样定期去清空数据
 */
#[\Attribute(flags: \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsScheduleClean
{
    public function __construct(
        public string $expression = '0 0 * * *',
        public int $defaultKeepDay = 7,
        public ?string $keepDayEnv = null,
    ) {
    }
}
