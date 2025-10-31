# Symfony Schedule Entity Clean Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-schedule-entity-clean-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/symfony-schedule-entity-clean-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/symfony-schedule-entity-clean-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/symfony-schedule-entity-clean-bundle)
[![License](https://img.shields.io/packagist/l/tourze/symfony-schedule-entity-clean-bundle.svg?style=flat-square)]
(https://packagist.org/packages/tourze/symfony-schedule-entity-clean-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)]
(https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)]
(https://codecov.io/gh/tourze/php-monorepo)

A Symfony bundle for automatically cleaning old entity data based on scheduled cron expressions.

## Features

- Automatically clean old entity data based on cron expressions
- Configurable retention period for each entity
- Custom retention period via environment variables
- Asynchronous processing using Symfony Messenger
- Event dispatching after cleaning operations

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Console Commands](#console-commands)
- [Events](#events)
- [Advanced Usage](#advanced-usage)
- [Requirements](#requirements)
- [License](#license)

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

- `expression`: A cron expression that determines when to clean the entity 
  (default: '0 0 * * *', which runs at midnight every day)
- `defaultKeepDay`: The number of days to keep records (default: 7)
- `keepDayEnv`: An optional environment variable name that can override the defaultKeepDay value

## Console Commands

### schedule-entity-clean:run

This command is responsible for periodically cleaning entity data based on the 
configured cron expressions. It runs every minute by default and checks all 
entities marked with the `AsScheduleClean` attribute.

The command:
- Scans all entities for `AsScheduleClean` attributes
- Checks if the cron expression is due for execution  
- Verifies that entities have a `createTime` field
- Dispatches asynchronous cleanup messages for eligible entities

Usage:
```bash
php bin/console schedule-entity-clean:run
```

**Note:** This command is automatically registered as a cron job and runs every 
minute. You don't need to call it manually unless for testing purposes.

## Advanced Usage

### Environment Variable Configuration

You can override the default keep days using environment variables:

```php
#[AsScheduleClean(
    expression: '0 2 * * *', 
    defaultKeepDay: 30, 
    keepDayEnv: 'LOG_RETENTION_DAYS'
)]
class LogEntry
{
    // ...
}
```

Then set the environment variable:

```bash
LOG_RETENTION_DAYS=60
```

### Multiple Cleaning Schedules

You can apply multiple cleaning schedules to the same entity:

```php
#[AsScheduleClean(expression: '0 0 * * 0', defaultKeepDay: 7)]   // Weekly cleanup
#[AsScheduleClean(expression: '0 0 1 * *', defaultKeepDay: 90)]  // Monthly deep cleanup
class LogEntry
{
    // ...
}
```

## Events

The bundle dispatches a `ScheduleEntityCleanFinishEvent` after successfully 
cleaning entity data. You can listen to this event to perform custom actions 
after the cleaning process.

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
