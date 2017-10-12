<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;
use Zp\PHPWire\Tests\Fixtures\ClassDependency;
use Zp\PHPWire\Tests\Fixtures\InterfaceDependency;
use Zp\PHPWire\Tests\Fixtures\ScalarDependency;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\FooInterface;

class ConstructorTest extends TestCase
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
