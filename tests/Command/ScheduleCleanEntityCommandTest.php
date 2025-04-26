<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\MessageBusInterface;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;
use Tourze\ScheduleEntityCleanBundle\Command\ScheduleCleanEntityCommand;

class ScheduleCleanEntityCommandTest extends TestCase
{
    private MockObject&MessageBusInterface $messageBus;
    private MockObject&EntityManagerInterface $entityManager;
    private ScheduleCleanEntityCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->command = new ScheduleCleanEntityCommand($this->messageBus, $this->entityManager);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithNoEntities(): void
    {
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('getAllMetadata')
            ->willReturn([]);

        $this->entityManager->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $this->messageBus->expects($this->never())
            ->method('dispatch');

        $this->commandTester->execute([]);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithEntityMissingCreateTime(): void
    {
        $className = 'App\Entity\TestEntityNoCreateTime';

        $reflection = $this->createMock(ReflectionClass::class);
        $reflection->expects($this->once())
            ->method('hasProperty')
            ->with('createTime')
            ->willReturn(false);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())
            ->method('getName')
            ->willReturn($className);
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn($reflection);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('getAllMetadata')
            ->willReturn([$metadata]);

        $this->entityManager->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $this->messageBus->expects($this->never())
            ->method('dispatch');

        $this->commandTester->execute([]);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('缺少 createTime 字段', $this->commandTester->getDisplay());
    }

    public function testExecuteWithEntityNoScheduleCleanAttribute(): void
    {
        $className = 'App\Entity\TestEntityNoAttribute';

        $reflection = $this->createMock(ReflectionClass::class);
        $reflection->expects($this->once())
            ->method('hasProperty')
            ->with('createTime')
            ->willReturn(true);
        $reflection->expects($this->once())
            ->method('getAttributes')
            ->with(AsScheduleClean::class)
            ->willReturn([]);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())
            ->method('getName')
            ->willReturn($className);
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn($reflection);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('getAllMetadata')
            ->willReturn([$metadata]);

        $this->entityManager->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $this->messageBus->expects($this->never())
            ->method('dispatch');

        $this->commandTester->execute([]);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('不需要自动清理', $this->commandTester->getDisplay());
    }

    public function testExecuteWithCronNotDue(): void
    {
        $className = 'App\Entity\TestEntityWithAttribute';
        $attributeMock = $this->getMockBuilder(AsScheduleClean::class)
            ->setConstructorArgs(['0 0 * * *', 7, null])
            ->getMock();

        $attribute = new \ReflectionAttribute(AsScheduleClean::class, 0);

        $reflection = $this->createMock(ReflectionClass::class);
        $reflection->expects($this->once())
            ->method('hasProperty')
            ->with('createTime')
            ->willReturn(true);
        $reflection->expects($this->once())
            ->method('getAttributes')
            ->with(AsScheduleClean::class)
            ->willReturn([$attribute]);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())
            ->method('getName')
            ->willReturn($className);
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn($reflection);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('getAllMetadata')
            ->willReturn([$metadata]);

        $this->entityManager->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $this->messageBus->expects($this->never())
            ->method('dispatch');

        $this->commandTester->execute([]);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
