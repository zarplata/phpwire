<?php

namespace Zp\PHPWire\Tests\Container;

use PhpParser\Node\Stmt\Continue_;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zp\PHPWire\Container;

class SelfTest extends TestCase
{
    public function testSelf()
    {
        // arrange
        $container = new class([]) extends Container {};
        // act
        $has = $container->has(Container::class);
        $instance = $container->get(Container::class);
        // assert
        $this->assertTrue($has);
        $this->assertSame($container, $instance);
    }

    public function testStatic()
    {
        // arrange
        $container = new class([]) extends Container {};
        // act
        $has = $container->has(\get_class($container));
        $instance = $container->get(\get_class($container));
        // assert
        $this->assertTrue($has);
        $this->assertSame($container, $instance);
    }

    public function testInterface()
    {
        // arrange
        $container = new class([]) extends Container {};
        // act
        $has = $container->has(ContainerInterface::class);
        $instance = $container->get(ContainerInterface::class);
        // assert
        $this->assertTrue($has);
        $this->assertSame($container, $instance);
    }
}
