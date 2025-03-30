<?php

namespace Tourze\ScheduleEntityCleanBundle\Message;

use Tourze\Symfony\Async\Message\AsyncMessageInterface;

/**
 * 有些表的数据我们不是十分关心的，可以定期清理的
 */
class CleanEntityMessage implements AsyncMessageInterface
{
    /**
     * @var string 实体名
     */
    private string $modelClass;

    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    public function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @var int 保留天数
     */
    private int $keepDay = 7;

    public function getKeepDay(): int
    {
        return $this->keepDay;
    }

    public function setKeepDay(int $keepDay): void
    {
        $this->keepDay = $keepDay;
    }
}
