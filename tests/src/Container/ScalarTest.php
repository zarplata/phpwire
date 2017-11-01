<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;
use Zp\PHPWire\Tests\Fixtures\ClassDependency;
use Zp\PHPWire\Tests\Fixtures\InterfaceDependency;
use Zp\PHPWire\Tests\Fixtures\ScalarDependency;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\FooInterface;
use Zp\PHPWire\Tests\Fixtures\StringDependency;

class ScalarTest extends TestCase
{
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
        $this->assertEquals(1, $entry->getValue());
    }

    public function testMixedNoService()
    {
        $container = new Container([
            ScalarDependency::class => [
                'args' => [Foo::class]
            ],
        ]);
        // act
        /** @var ScalarDependency $entry */
        $entry = $container->get(ScalarDependency::class);
        // assert
        $this->assertEquals(Foo::class, $entry->getValue());
    }

    public function testMixedWithService()
    {
        $container = new Container([
            Foo::class => [],
            ScalarDependency::class => [
                'args' => [Foo::class]
            ],
        ]);
        // act
        /** @var ScalarDependency $entry */
        $entry = $container->get(ScalarDependency::class);
        // assert
        $this->assertInstanceOf(Foo::class, $entry->getValue());
    }

    public function testMixedWithAlias()
    {
        $container = new Container([
            'foo' => [
                'class' => Foo::class,
            ],
            ScalarDependency::class => [
                'args' => ['$foo']
            ],
        ]);
        // act
        /** @var ScalarDependency $entry */
        $entry = $container->get(ScalarDependency::class);
        // assert
        $this->assertInstanceOf(Foo::class, $entry->getValue());
    }

    public function testStringNoService()
    {
        $container = new Container([
            StringDependency::class => [
                'args' => [Foo::class]
            ],
        ]);
        // act
        /** @var ScalarDependency $entry */
        $entry = $container->get(StringDependency::class);
        // assert
        $this->assertEquals(Foo::class, $entry->getValue());
    }

    public function testStringWithService()
    {
        $container = new Container([
            Foo::class => [],
            StringDependency::class => [
                'args' => [Foo::class]
            ],
        ]);
        // act
        /** @var ScalarDependency $entry */
        $entry = $container->get(StringDependency::class);
        // assert
        $this->assertEquals(Foo::class, $entry->getValue());
    }

    public function testStringWithAlias()
    {
        $container = new Container([
            'foo' => [
                'class' => Foo::class,
            ],
            StringDependency::class => [
                'args' => ['$foo']
            ],
        ]);
        // act
        /** @var ScalarDependency $entry */
        $entry = $container->get(StringDependency::class);
        // assert
        $this->assertEquals('$foo', $entry->getValue());
    }
}
