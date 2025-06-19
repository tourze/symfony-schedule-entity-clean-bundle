<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\TestEntity;

use Doctrine\ORM\Mapping as ORM;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;

#[ORM\Entity]
#[ORM\Table(name: 'test_entity_with_env', options: ['comment' => '带环境变量的测试实体'])]
#[AsScheduleClean(expression: '0 0 * * *', defaultKeepDay: 7, keepDayEnv: 'TEST_KEEP_DAY')]
class TestEntityWithEnv implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\Column(options: ['comment' => '创建时间'])]
    private \DateTimeImmutable $createTime;

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreateTime(): \DateTimeImmutable
    {
        return $this->createTime;
    }

    public function setCreateTime(\DateTimeImmutable $createTime): self
    {
        $this->createTime = $createTime;
        return $this;
    }

    public function __toString(): string
    {
        return 'TestEntityWithEnv#' . ($this->id ?? 'null');
    }
}
