<?php

namespace Tourze\ScheduleEntityCleanBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\ScheduleEntityCleanBundle\ScheduleEntityCleanBundle;

class ScheduleEntityCleanBundleTest extends TestCase
{
    public function testInstanceOfBundle(): void
    {
        $bundle = new ScheduleEntityCleanBundle();
        
        $this->assertInstanceOf(Bundle::class, $bundle);
    }
    
    public function testGetPath(): void
    {
        $bundle = new ScheduleEntityCleanBundle();
        $path = $bundle->getPath();
        
        $this->assertDirectoryExists($path);
        $this->assertDirectoryExists($path . '/Resources');
        $this->assertFileExists($path . '/Resources/config/services.yaml');
    }
} 