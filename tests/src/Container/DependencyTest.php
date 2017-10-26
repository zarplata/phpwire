<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;
use Zp\PHPWire\Tests\Fixtures\ClassDependency;
use Zp\PHPWire\Tests\Fixtures\InterfaceDependency;
use Zp\PHPWire\Tests\Fixtures\ScalarDependency;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\FooInterface;

class DependencyTest extends TestCase
{
    public function testClassDependency()
    {
        // arrange
        $container = new Container([
            ClassDependency::class => ['args' => []],
            FooInterface::class => ['class' => Foo::class],
        ]);
        // act
        $entry = $container->get(ClassDependency::class);
        // assert
        $this->assertInstanceOf(ClassDependency::class, $entry);
    }

    public function testInterfaceDependency()
    {
        // arrange
        $container = new Container([
            InterfaceDependency::class => [],
            FooInterface::class => ['class' => Foo::class],
        ]);
        // act
        $entry = $container->get(InterfaceDependency::class);
        // assert
        $this->assertInstanceOf(InterfaceDependency::class, $entry);
    }

    /**
     * @expectedException \Psr\Container\ContainerExceptionInterface
     * @expectedExceptionMessage Unable to create instance of entry `Zp\PHPWire\Tests\Fixtures\ScalarDependency`: Unable to invoke arguments to constructor: Please provide definition for argument `value`
     */
    public function testUnresolvedDependencies()
    {
        // arrange
        $container = new Container([
            ScalarDependency::class => [],
        ]);
        // act
        $entry = $container->get(ScalarDependency::class);
        // assert
        $this->assertInstanceOf(ScalarDependency::class, $entry);
    }
}
