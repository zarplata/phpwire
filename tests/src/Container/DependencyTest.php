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
        $this->assertNull($entry->getFoo());
    }

    public function testAliasDependency()
    {
        // arrange
        $container = new Container([
            ClassDependency::class => ['args' => ['$foo']],
            'foo' => ['class' => Foo::class],
        ]);
        // act
        $entry = $container->get(ClassDependency::class);
        // assert
        $this->assertInstanceOf(ClassDependency::class, $entry);
        $this->assertInstanceOf(Foo::class, $entry->getFoo());
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
        $this->assertInstanceOf(Foo::class, $entry->getFoo());
    }

    /**
     * @expectedException \Psr\Container\ContainerExceptionInterface
     * @expectedExceptionMessage Unable to create instance of entry `Zp\PHPWire\Tests\Fixtures\ScalarDependency`: Unable to invoke arguments to constructor: Please provide definition for argument `number`
     */
    public function testUnresolvedDependencies()
    {
        // arrange
        $container = new Container([
            ScalarDependency::class => [],
        ]);
        // act
        $container->get(ScalarDependency::class);
    }

    /**
     * @expectedException \Psr\Container\ContainerExceptionInterface
     * @expectedExceptionMessage Unable to create instance of entry `Zp\PHPWire\Tests\Fixtures\ClassDependency`: Unable to invoke arguments to constructor: Requested a non-existent container entry `foo`
     */
    public function testRequestNonExistent()
    {
        // arrange
        $container = new Container([
            ClassDependency::class => ['args' => ['$foo']],
        ]);
        // act
        $container->get(ClassDependency::class);
    }
}
