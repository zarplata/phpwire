<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;
use Zp\PHPWire\Tests\Fixtures\Foo;

class InitializedTest extends TestCase
{
    public function testHaveNoDefinition()
    {
        // arrange
        $container = new Container([]);
        // act
        $initialized = $container->initialized(Foo::class);
        // assert
        $this->assertFalse($initialized);
    }

    public function testHaveDefinitionButDidNotGet()
    {
        // arrange
        $container = new Container([
            Foo::class => [],
        ]);
        // act
        $initialized = $container->initialized(Foo::class);
        // assert
        $this->assertFalse($initialized);
    }

    public function testHaveDefinitionAndDidGet()
    {
        // arrange
        $container = new Container([
            Foo::class => [],
        ]);
        // act
        $container->get(Foo::class);
        $initialized = $container->initialized(Foo::class);
        // assert
        $this->assertTrue($initialized);
    }

    public function testHaveNoDefinitionButSet()
    {
        // arrange
        $container = new Container([]);
        // act
        $container->set(Foo::class, new Foo());
        $initialized = $container->initialized(Foo::class);
        // assert
        $this->assertTrue($initialized);
    }
}
