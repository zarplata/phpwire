<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\StrictDependency;

class ValidatorTest extends TestCase
{
    public function testValidConfiguration()
    {
        // arrange
        $container = new Container([
            Foo::class => [],
        ]);
        // act
        $exceptions = $container->validate();
        // assert
        $this->assertEmpty($exceptions);
    }

    public function testInvalidConfiguration()
    {
        // arrange
        $container = new Container([
            StrictDependency::class => [],
        ]);
        // act
        $exceptions = $container->validate();
        // assert
        $this->assertArrayHasKey(StrictDependency::class, $exceptions);
    }
}
