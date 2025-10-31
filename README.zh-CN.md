# Symfony实体定时清理Bundle

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

一个用于根据定时表达式自动清理旧实体数据的Symfony bundle。

## 功能特性

- 根据cron表达式自动清理旧实体数据
- 为每个实体配置可调整的保留周期
- 通过环境变量自定义保留周期
- 使用Symfony Messenger进行异步处理
- 清理操作后触发事件

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [快速开始](#快速开始)
- [配置](#配置)
- [控制台命令](#控制台命令)
- [事件](#事件)
- [高级用法](#高级用法)
- [系统要求](#系统要求)
- [许可证](#许可证)

## 安装

```bash
composer require tourze/symfony-schedule-entity-clean-bundle
```

在`bundles.php`中注册Bundle：

```php
return [
    // ...
    Tourze\ScheduleEntityCleanBundle\ScheduleEntityCleanBundle::class => ['all' => true],
    // ...
];
```

## 快速开始

1. 使用`AsScheduleClean`属性标记您的实体类：

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

    // 其他属性和方法...
}
```

2. 确保您的实体有一个`createTime`字段。Bundle使用此字段确定要删除哪些记录。

3. Bundle将自动注册一个每分钟运行的cron作业，以检查是否有任何实体需要清理。

## 配置

`AsScheduleClean`属性接受以下参数：

- `expression`：决定何时清理实体的cron表达式（默认：'0 0 * * *'，每天午夜运行）
- `defaultKeepDay`：保留记录的天数（默认：7）
- `keepDayEnv`：可以覆盖默认保留天数值的可选环境变量名称

## 控制台命令

### schedule-entity-clean:run

此命令负责根据配置的cron表达式定期清理实体数据。它默认每分钟运行一次，检查所有标记了`AsScheduleClean`属性的实体。

命令功能：
- 扫描所有实体的`AsScheduleClean`属性
- 检查cron表达式是否应该执行
- 验证实体是否有`createTime`字段
- 为符合条件的实体分发异步清理消息

使用方法：
```bash
php bin/console schedule-entity-clean:run
```

**注意：** 此命令会自动注册为cron作业，每分钟运行一次。除非用于测试目的，否则您无需手动调用。

## 高级用法

### 环境变量配置

您可以使用环境变量覆盖默认的保留天数：

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

然后设置环境变量：

```bash
LOG_RETENTION_DAYS=60
```

### 多重清理调度

您可以为同一个实体应用多个清理调度：

```php
#[AsScheduleClean(expression: '0 0 * * 0', defaultKeepDay: 7)]   // 每周清理
#[AsScheduleClean(expression: '0 0 1 * *', defaultKeepDay: 90)]  // 每月深度清理
class LogEntry
{
    // ...
}
```

## 事件

Bundle在成功清理实体数据后会分发`ScheduleEntityCleanFinishEvent`事件。您可以监听此事件，以在清理过程后执行自定义操作。

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
        // 使用model类做一些操作
    }
}
```

## 系统要求

- PHP 8.1或更高版本
- Symfony 6.4或更高版本
- Doctrine ORM
- 实体必须有一个`createTime`字段

## 许可证

MIT许可证（MIT）。请查看[License文件](LICENSE)了解更多信息。
