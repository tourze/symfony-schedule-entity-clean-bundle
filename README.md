# Symfony Schedule Entity Clean Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-schedule-entity-clean-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-schedule-entity-clean-bundle)

A Symfony bundle for automatically cleaning old entity data based on scheduled cron expressions.

## Features

- Automatically clean old entity data based on cron expressions
- Configurable retention period for each entity
- Custom retention period via environment variables
- Asynchronous processing using Symfony Messenger
- Event dispatching after cleaning operations

## Installation

```bash
composer require tourze/symfony-schedule-entity-clean-bundle
```

Register the bundle in your `bundles.php`:

```php
return [
    // ...
    Tourze\ScheduleEntityCleanBundle\ScheduleEntityCleanBundle::class => ['all' => true],
    // ...
];
```

## Quick Start

1. Mark your entity class with the `AsScheduleClean` attribute:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tourze\ScheduleEntityCleanBundle\Attribute\AsScheduleClean;

#[ORM\Entity]
#[AsScheduleClean(expression: '0 0 * * *', defaultKeepDay: 30)]
class LogEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private \DateTimeImmutable $createTime;

    // Other properties and methods...
}
```

2. Ensure your entity has a `createTime` field. The bundle uses this field to determine which records to delete.

3. The bundle will automatically register a cron job that runs every minute to check if any entities need cleaning.

## Configuration

The `AsScheduleClean` attribute accepts the following parameters:

- `expression`: A cron expression that determines when to clean the entity (default: '0 0 ** *', which runs at midnight every day)
- `defaultKeepDay`: The number of days to keep records (default: 7)
- `keepDayEnv`: An optional environment variable name that can override the defaultKeepDay value

## Events

The bundle dispatches a `ScheduleEntityCleanFinishEvent` after successfully cleaning entity data. You can listen to this event to perform custom actions after the cleaning process.

```php
<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\ScheduleEntityCleanBundle\Event\ScheduleEntityCleanFinishEvent;

class EntityCleanSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ScheduleEntityCleanFinishEvent::class => 'onEntityCleaned',
        ];
    }

    public function onEntityCleaned(ScheduleEntityCleanFinishEvent $event): void
    {
        $modelClass = $event->getModelClass();
        // Do something with the model class
    }
}
```

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM
- Entity must have a `createTime` field

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
