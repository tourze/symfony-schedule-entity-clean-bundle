<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;

/**
 * 这个测试用例展示了如何在实际应用中使用这个Bundle
 */
class ExampleUsageTest extends TestCase
{
    public function testExampleEntityAttributes(): void
    {
        // 模拟实体类的反射
        $reflection = new \ReflectionClass(ExampleEntity::class);

        // 检查是否设置了AsScheduleClean属性
        $attributes = $reflection->getAttributes(AsScheduleClean::class);
        $this->assertNotEmpty($attributes, '实体应该有AsScheduleClean属性');

        // 检查属性值
        $attribute = $attributes[0]->newInstance();
        $this->assertEquals('0 0 * * *', $attribute->expression, 'cron表达式应该为每天午夜执行');
        $this->assertEquals(90, $attribute->defaultKeepDay, '默认保留期应该为90天');
        $this->assertEquals('LOG_KEEP_DAYS', $attribute->keepDayEnv, '应该使用LOG_KEEP_DAYS环境变量');
    }

    public function testEntityHasRequiredFields(): void
    {
        // 确保实体有createTime字段
        $reflection = new \ReflectionClass(ExampleEntity::class);
        $this->assertTrue($reflection->hasProperty('createTime'), '实体必须有createTime属性');

        // 实例化实体并测试createTime字段
        $entity = new ExampleEntity();
        $this->assertInstanceOf(\DateTimeImmutable::class, $entity->getCreateTime(), 'createTime应该是DateTimeImmutable实例');

        // 测试设置新的createTime
        $newTime = new \DateTimeImmutable('2023-01-01');
        $entity->setCreateTime($newTime);
        $this->assertEquals($newTime, $entity->getCreateTime(), '应该能够设置createTime属性');
    }
}

/**
 * 这是一个示例实体，展示如何使用AsScheduleClean注释
 */
#[AsScheduleClean(expression: '0 0 * * *', defaultKeepDay: 90, keepDayEnv: 'LOG_KEEP_DAYS')]
class ExampleEntity
{
    private ?int $id = null;

    private \DateTimeImmutable $createTime;

    private string $data = '';

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

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;
        return $this;
    }
}
