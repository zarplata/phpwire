<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Throwable;
use Zp\PHPWire\Container;
use Zp\PHPWire\ContainerException;
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

    public function testNamedDependency()
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

    public function testUnresolvedDependencies()
    {
        // arrange
        $definitions = [
            ScalarDependency::class => [],
        ];
        $container = new Container($definitions);

        // act
        try {
            $container->get(ScalarDependency::class);
        } catch (ContainerExceptionInterface $e) {
            $this->assertEquals(
                'Unable to create instance of definition `Zp\PHPWire\Tests\Fixtures\ScalarDependency`',
                $e->getMessage()
            );
            $this->assertInstanceOf(
                ContainerExceptionInterface::class,
                $e->getPrevious()
            );
            $this->assertEquals(
                'Unable to invoke arguments to constructor of Zp\PHPWire\Tests\Fixtures\ScalarDependency',
                $e->getPrevious()->getMessage()
            );
            $this->assertInstanceOf(
                ContainerExceptionInterface::class,
                $e->getPrevious()->getPrevious()
            );
            $this->assertEquals(
                'Please provide definition for argument `value`',
                $e->getPrevious()->getPrevious()->getMessage()
            );
            $this->assertNull($e->getPrevious()->getPrevious()->getPrevious());
            return;
        }
        $this->fail('Failed asserting that exception is thrown');
    }

    public function testRequestedNonExistentDependency()
    {
        // arrange
        $definitions = [
            ClassDependency::class => ['args' => ['$foo']],
        ];
        $container = new Container($definitions);
        // act
        try {
            $container->get(ClassDependency::class);
        } catch (ContainerExceptionInterface $e) {
            $this->assertEquals(
                'Unable to create instance of definition `Zp\PHPWire\Tests\Fixtures\ClassDependency`',
                $e->getMessage()
            );
            $this->assertInstanceOf(
                ContainerExceptionInterface::class,
                $e->getPrevious()
            );
            $this->assertEquals(
                'Unable to invoke arguments to constructor of Zp\PHPWire\Tests\Fixtures\ClassDependency',
                $e->getPrevious()->getMessage()
            );
            $this->assertInstanceOf(
                ContainerExceptionInterface::class,
                $e->getPrevious()->getPrevious()
            );
            $this->assertEquals(
                'Unable to resolve value of argument #0',
                $e->getPrevious()->getPrevious()->getMessage()
            );
            $this->assertInstanceOf(
                ContainerExceptionInterface::class,
                $e->getPrevious()->getPrevious()->getPrevious()
            );
            $this->assertEquals(
                'Requested a non-existent container entry `foo`',
                $e->getPrevious()->getPrevious()->getPrevious()->getMessage()
            );
            $this->assertNull($e->getPrevious()->getPrevious()->getPrevious()->getPrevious());
            return;
        }
        $this->fail('Failed asserting that exception is thrown');
    }
}
