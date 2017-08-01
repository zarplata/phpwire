<?php

namespace Zp\Container\Tests\Container;

use Zp\Container\Container;
use Zp\Container\Tests\Fixtures\ClassDependency;
use Zp\Container\Tests\Fixtures\InterfaceDependency;
use Zp\Container\Tests\Fixtures\ScalarDependency;
use Zp\Container\Tests\Fixtures\Foo;
use Zp\Container\Tests\Fixtures\FooInterface;

class ConstructorTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        // arrange
        $container = new Container([
            Foo::class => [],
        ]);
        // act
        $entry = $container->get(Foo::class);
        // assert
        $this->assertInstanceOf(Foo::class, $entry);
    }

    public function testClass()
    {
        // arrange
        $container = new Container([
            Foo::class => [],
            ClassDependency::class => [
                'args' => [Foo::class]
            ],
        ]);
        // act
        /** @var ClassDependency $entry */
        $entry = $container->get(ClassDependency::class);
        // assert
        $this->assertInstanceOf(ClassDependency::class, $entry);
        $this->assertInstanceOf(Foo::class, $entry->getFoo());
    }

    public function testInterface()
    {
        // arrange
        $container = new Container([
            FooInterface::class => [
                'class' => Foo::class,
            ],
            InterfaceDependency::class => [
                'args' => [FooInterface::class]
            ],
        ]);
        // act
        /** @var ClassDependency $entry */
        $entry = $container->get(InterfaceDependency::class);
        // assert
        $this->assertInstanceOf(InterfaceDependency::class, $entry);
        $this->assertInstanceOf(Foo::class, $entry->getFoo());
    }

    public function testScalar()
    {
        $container = new Container([
            ScalarDependency::class => [
                'args' => [1]
            ],
        ]);
        // act
        /** @var ScalarDependency $entry */
        $entry = $container->get(ScalarDependency::class);
        // assert
        $this->assertInternalType('int', $entry->getNumber());
    }
}
