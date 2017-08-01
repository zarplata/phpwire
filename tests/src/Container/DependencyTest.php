<?php

namespace Zp\Container\Tests\Container;

use Zp\Container\Container;
use Zp\Container\Tests\Fixtures\ClassDependency;
use Zp\Container\Tests\Fixtures\InterfaceDependency;
use Zp\Container\Tests\Fixtures\ScalarDependency;
use Zp\Container\Tests\Fixtures\Foo;
use Zp\Container\Tests\Fixtures\FooInterface;

class DependencyTest extends \PHPUnit_Framework_TestCase
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
     * @expectedExceptionMessage Unable to resolve parameter `number` of method `__construct`
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
